<?php
/**
 * @property integer $activeId
 * @property string $recommendedCollect
 * @property integer $recommendedPrize
 * @property string $collect
 */
class Contest extends CModel
{
    const LIFETIME = 172800; //2 days
    const PRIZE = 500;

    const ID_ACTIVE = 'contest:active';
    const ID_LIST = 'contest:list:';
    const ID_RECOMMENDED_COLLECT = 'contest:r_collect';
    const ID_RECOMMENDED_PRIZE = 'contest:r_prize';

    const ACT_MISSION = 1;
    const ACT_DUEL = 2;
    const ACT_CLUB = 3;

    private $activeId;
    private $recommendedCollect;
    private $recommendedPrize;
    private $collect;
    private $collectParam;
    private $collectTypes = [
        'xp','xp_duel','xp_mission',
        'dollar','dollar_duel','dollar_mission'
        ];

    public function attributeNames()
    {
        return [];
    }

    public function getCollect()
    {
        if (!$this->collect) {
            $this->collect = Yii::app()->redis->getClient()->get(self::ID_LIST . $this->activeId . ':collect');
        }
        return $this->collect;
    }

    public function getActiveId()
    {
        if (!$this->activeId) {
            $this->activeId = Yii::app()->redis->getClient()->get(self::ID_ACTIVE);
        }
        return $this->activeId;
    }

    public function getRecommendedCollect()
    {
        if (!$this->recommendedCollect) {
            $this->recommendedCollect = Yii::app()->redis->getClient()->get(self::ID_RECOMMENDED_COLLECT);
            Yii::app()->redis->getClient()->del(self::ID_RECOMMENDED_COLLECT);
        }

        if (!in_array($this->recommendedCollect, $this->collectTypes)) {
            $randomId = array_rand($this->collectTypes);
            $this->recommendedCollect = $this->collectTypes[$randomId];
        }

        return $this->recommendedCollect;
    }

    public function getRecommendedPrize()
    {
        if (!$this->recommendedPrize) {
            $this->recommendedPrize = Yii::app()->redis->getClient()->get(self::ID_RECOMMENDED_PRIZE);
            Yii::app()->redis->getClient()->del(self::ID_RECOMMENDED_PRIZE);
        }

        if (!$this->recommendedPrize) {
            $this->recommendedPrize = self::PRIZE;
        }

        return (int)$this->recommendedPrize;
    }

    public function create()
    {
        if ($this->activeId) {
            return false;
        }

        $redis = Yii::app()->redis->getClient();

        $this->activeId = time();

        $redis->set('contest:active', $this->activeId);
        $redis->lPush('contest:log', $this->activeId); //log of contests

        $redis->set(self::ID_LIST . $this->activeId.':collect', $this->getRecommendedCollect());
        $redis->set(self::ID_LIST . $this->activeId.':prize', $this->getRecommendedPrize());
        $redis->set(self::ID_LIST . $this->activeId.':created', date('Y.m.d. H:i:s', $this->getActiveId()));
        return true;
    }

    /**
     * @param integer $energy
     */
    public function addPoints($uid, $activity, $energy, $xp, $dollar)
    {
        //todo: log common collecting

        if (!$this->getActiveId()) {
            return false; //no active contest
        }
        if (!$this->validate($activity, $xp, $dollar)) {
            return false;
        }

        //collect
        $points = 0;
        if ($this->collectParam == 'xp') {
            $points = $xp;
        }

        if ($this->collectParam == 'dollar') {
            $points = $dollar;
        }

        Yii::app()->redis->getClient()->zIncrBy(self::ID_LIST . $this->getActiveId() . ':points', $points, $uid);
        return true;
    }
    
    public function complete()
    {
        $redis = Yii::app()->redis->getClient();

        $completed = $redis->del(self::ID_ACTIVE);
        if ($completed) {
            $this->logWinners();
            $this->activeId = 0;
        }

        return $completed;
    }

    private function logWinners()
    {
        $winners = $this->fetchWinners();
        if (!$winners) {
            return false;
        }

        $redis = Yii::app()->redis->getClient();
        $b = Yii::app()->badge->model;
        foreach ($winners as $winner) {
            //add badges
            $b->uid = $winner;
            $b->triggerSimple('win_contest');

            //add winner to the log
            $redis->sadd(self::ID_LIST . $this->activeId . ':winners', $winner);
        }
    }

    private function fetchWinners()
    {
        $redis = Yii::app()->redis->getClient();
        //get max point
        $key = self::ID_LIST . $this->activeId . ':points';
        $max = $redis->zRevRange($key, 0, 0, true);
        
        if (count($max)) {
            $maxScore = array_values($max)[0];
            $winners = $redis->zRevRangeByScore($key, $maxScore, $maxScore);
            return $winners;
        }
        return false;
    }

    public function validate($activity, $xp, $dollar)
    {
        $toCollect = $this->getCollect();

        //check the lifetime
        if (time() > $this->activeId + self::LIFETIME) {
            return false;
        }

        $valid = true;
        //check activity+toCollect
        switch ($toCollect) {
            case 'xp':
                if (!$xp) {
                    $valid = false;
                }
                $this->collectParam = 'xp';
                break;
            case 'xp_duel':
                if (!$xp || $activity != self::ACT_DUEL) {
                    $valid = false;
                }
                $this->collectParam = 'xp';
                break;
            case 'xp_mission':
                if (!$xp || $activity != self::ACT_MISSION) {
                    $valid = false;
                }
                $this->collectParam = 'xp';
                break;
            case 'dollar':
                if (!$dollar) {
                    $valid = false;
                }
                $this->collectParam = 'dollar';
                break;
            case 'dollar_duel':
                if (!$dollar || $activity != self::ACT_DUEL) {
                    $valid = false;
                }
                $this->collectParam = 'dollar';
                break;
            case 'dollar_mission':
                if (!$dollar || $activity != self::ACT_MISSION) {
                    $valid = false;
                }
                $this->collectParam = 'dollar';
                break;
        }

        if (!$valid) {
            return false;
        }

        return true;
    }
}
