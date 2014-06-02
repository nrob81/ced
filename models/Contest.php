<?php
/**
 * @property integer $activeId
 * @property string $recommendedCollect
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

    private $_activeId;
    private $_recommendedCollect;
    private $_recommendedPrize;
    private $_collect;
    private $_collectParam;
    private $_collectTypes = [
        'xp','xp_duel','xp_mission',
        'dollar','dollar_duel','dollar_mission'
        ];

    public function attributeNames() {
        return [];
    }

    public function getCollect() {
        if (!$this->_collect) {
            $this->_collect = Yii::app()->redis->getClient()->get(self::ID_LIST . $this->_activeId . ':collect');
        }
        return $this->_collect; 
    }

    public function getActiveId() {
        if (!$this->_activeId) {
            $this->_activeId = Yii::app()->redis->getClient()->get(self::ID_ACTIVE);
        }
        return $this->_activeId;
    }
    public function getRecommendedCollect() {
        if (!$this->_recommendedCollect) {
            //echo 'rec.from.cache, ';
            $this->_recommendedCollect = Yii::app()->redis->getClient()->get(self::ID_RECOMMENDED_COLLECT);
            Yii::app()->redis->getClient()->del(self::ID_RECOMMENDED_COLLECT);
        }

        if (!in_array($this->_recommendedCollect, $this->_collectTypes)) {
            //echo 'rec.not.found, ';
            $randomId = array_rand($this->_collectTypes);
            $this->_recommendedCollect = $this->_collectTypes[$randomId];
        }

        //echo 'rec:'.$this->_recommendedCollect;
        return $this->_recommendedCollect; 
    }
    public function getRecommendedPrize() {
        if (!$this->_recommendedPrize) {
            $this->_recommendedPrize = Yii::app()->redis->getClient()->get(self::ID_RECOMMENDED_PRIZE);
            Yii::app()->redis->getClient()->del(self::ID_RECOMMENDED_PRIZE);
        }

        if (!$this->_recommendedPrize) {
            $this->_recommendedPrize = self::PRIZE;
        }

        return (int)$this->_recommendedPrize; 
    }

    public function create() {
        if ($this->activeId) return false;

        $redis = Yii::app()->redis->getClient();

        $this->_activeId = time();

        $redis->set('contest:active', $this->_activeId);
        $redis->lPush('contest:log', $this->_activeId); //log of contests

        $redis->set(self::ID_LIST . $this->_activeId.':collect', $this->recommendedCollect);
        $redis->set(self::ID_LIST . $this->_activeId.':prize', $this->recommendedPrize);
        $redis->set(self::ID_LIST . $this->_activeId.':created', date('Y.m.d. H:i:s', $this->_activeId));
        return true;        
    }

    public function addPoints($uid, $activity, $energy, $xp, $dollar) {
        //todo: log common collecting

        if (!$this->activeId) return false; //no active contest

        if (!$this->validate($activity, $xp, $dollar)) {
            return false;
        }

        //collect
        $points = 0;
        if ($this->_collectParam == 'xp') $points = $xp;
        if ($this->_collectParam == 'dollar') $points = $dollar;

        Yii::app()->redis->getClient()->zIncrBy(self::ID_LIST . $this->activeId . ':points', $points, $uid);
        return true;
    }
    
    public function complete() {
        $redis = Yii::app()->redis->getClient();

        $completed = $redis->del(self::ID_ACTIVE);
        if ($completed) {
            $this->logWinners();
            $this->_activeId = 0;
        }

        return $completed;
    }

    private function logWinners() {
        $winners = $this->fetchWinners();
        if (!$winners) return false;

        $redis = Yii::app()->redis->getClient();
        foreach ($winners as $winner) {
            //add badges
            Yii::app()->badge->model->triggerHer($winner, 'win_contest', []);

            //add winner to the log
            $redis->sadd(self::ID_LIST . $this->activeId . ':winners', $winner);
        }
    }

    private function fetchWinners() {
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

    public function validate($activity, $xp, $dollar) {
        $toCollect = $this->collect;

        //check the lifetime
        if (time() > $this->activeId + self::LIFETIME) {
            //echo 'quit, reason: lifetime, ';
            return false;
        }

        $valid = true;
        //check activity+toCollect
        switch ($toCollect) {
        case 'xp': 
            if (!$xp) $valid = false; 
            $this->_collectParam = 'xp';
            break;
        case 'xp_duel': 
            if (!$xp || $activity != self::ACT_DUEL) $valid = false;
            $this->_collectParam = 'xp';
            break;
        case 'xp_mission': 
            if (!$xp || $activity != self::ACT_MISSION) $valid = false;
            $this->_collectParam = 'xp';
            break;
        case 'dollar': 
            if (!$dollar) $valid = false;
            $this->_collectParam = 'dollar';
            break;
        case 'dollar_duel': 
            if (!$dollar || $activity != self::ACT_DUEL) $valid = false;
            $this->_collectParam = 'dollar';
            break;
        case 'dollar_mission': 
            if (!$dollar || $activity != self::ACT_MISSION) $valid = false;
            $this->_collectParam = 'dollar';
            break;
        }

        if (!$valid) {
            //echo 'quit, reason: , '.$toCollect;
            return false;
        }

        return true;
    }
}
