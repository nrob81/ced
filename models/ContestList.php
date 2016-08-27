<?php
/**
 * @property integer $id
 * @property integer $uid
 * @property string $collect
 * @property integer $prize
 * @property string $desciptionId
 * @property boolean $isValid
 * @property boolean $isActive
 * @property integer $secUntilEnd
 * @property array $winners
 * @property array $list
 * @property array $history
 * @property string $rankDescription
 * @property integer $maxScore
 * @property integer $lastId
 * @property integer $prizePerWinner
 */
class ContestList extends CModel
{
    const LIFETIME = 172800; //2 days
    const BOARD_RANGE = 7;

    private $id;
    private $uid;
    private $isValid;
    private $collect;
    private $prize;
    private $maxScore;
    private $list;
    private $winners;
    private $history;
    private $collectTypes = [
        'xp','xp_duel','xp_mission',
        'dollar','dollar_duel','dollar_mission'
        ];

    public function attributeNames()
    {
        return [];
    }

    public function getId()
    {
        return (int)$this->id;
    }

    public function getCollect()
    {
        return $this->collect;
    }

    public function getPrize()
    {
        return (int)$this->prize;
    }

    public function getDescriptionId()
    {
        if (!in_array($this->collect, $this->collectTypes)) {
            return 'xp';
        }
        return $this->collect;
    }

    public function getIsValid()
    {
        if (is_null($this->isValid)) {
            $this->isValid = Yii::app()->redis->getClient()->exists('contest:list:'.$this->getId().':created');
        }

        return (bool)$this->isValid;
    }

    public function getIsActive()
    {
        if (!$this->id) {
            return false;
        }

        $active = (int)Yii::app()->redis->getClient()->get('contest:active');
        return $active == $this->id;
    }

    public function getSecUntilEnd()
    {
        $sue = ($this->id + self::LIFETIME - time());
        return $sue;
    }

    public function hasWinner()
    {
        return Yii::app()->redis->getClient()->exists('contest:list:'.$this->id.':winners');
    }

    public function getWinners()
    {
        return $this->winners;
    }

    public function getList()
    {
        return $this->list;
    }

    public function getHistory()
    {
        if (!$this->history) {
            $this->history = Yii::app()->redis->getClient()->lRange('contest:log', 0, 5);
        }
        return $this->history;
    }

    public function getRankDescription()
    {
        if (!$this->getIsActive()) {
            return '';
        }

        $redis = Yii::app()->redis->getClient();

        $key = 'contest:list:'.$this->id.':points';
        $total = $redis->zCard($key);
        $rank  = $redis->zRevRank($key, $this->uid) + 1;

        if ($rank == 1) {
            return 'Te vagy a király!';
        } else {
            $percent = round((1 - ($rank / $total)) * 100, 1);
            if (!$percent) {
                return 'Húzz bele, ha nyerni szeretnél!';
            }

            return 'Jobb vagy, mint a versenytársaid ' . $percent . '%-a!';
        }
    }

    public function getMaxScore()
    {
        if (!$this->maxScore) {
            $redis = Yii::app()->redis->getClient();
            $key = 'contest:list:'.$this->id.':points';
            $max = $redis->zRevRange($key, 0, 0, true);

            if (count($max)) {
                $this->maxScore = array_values($max)[0];
            }
        }

        return (int)$this->maxScore;
    }

    public function getLastId()
    {
        $id = Yii::app()->redis->getClient()->lINDEX('contest:log', 0);
        return (int)$id;
    }

    public function getPrizePerWinner()
    {
        if (!count($this->winners)) {
            return Contest::PRIZE;
        }

        return ceil(Contest::PRIZE / count($this->winners));
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function setUid($uid)
    {
        $this->uid = (int)$uid;
    }

    public function fetchDetails()
    {
        $redis = Yii::app()->redis->getClient();
        $this->collect = $redis->get('contest:list:'.$this->id.':collect');
        $this->prize = $redis->get('contest:list:'.$this->id.':prize');
        $this->getMaxScore();
    }

    public function fetchList()
    {
        $redis = Yii::app()->redis->getClient();

        $myRank = $redis->zRevRank('contest:list:'.$this->getId().':points', $this->uid);

        $range = self::BOARD_RANGE;
        $min = $myRank - $range > 0 ? $myRank - $range : 0;
        $max = $myRank + $range + ($range-$myRank+$min);

        $item = new Player;
        $i = $min+1;
        foreach ($redis->zRevRange('contest:list:'.$this->id.':points', $min, $max, true) as $id => $score) {
            $item->subjectId = $id;

            $this->list[$i] = [
                'id'=>$id,
                'name'=>$item->getSubjectName(),
                'score'=>$score,
                ];
            $i++;
        }
    }

    public function listBestPlayers()
    {
        $redis = Yii::app()->redis->getClient();
        if ($this->getIsActive()) {
            $res = $redis->zRevRangeByScore('contest:list:'.$this->id.':points', $this->maxScore, $this->maxScore);
        } else {
            $res = $redis->smembers('contest:list:'.$this->id.':winners');
        }

        $list = [];
        $item = new Player;
        foreach ($res as $id) {
            $item->subjectId = $id;

            $list[$id] = [
                'name'=>$item->getSubjectName(),
                'score'=>$this->maxScore,
                ];
        }
        $this->winners = $list;
    }

    public function canClaimPrize()
    {
        //is active?
        if (!$this->hasWinner()) {
            return false;
        }

        //she is winner?
        if (!array_key_exists($this->uid, $this->winners)) {
            return false;
        }

        //have we the prize/winner?
        if ($this->prizePerWinner < 1) {
            return false;
        }

        //she has claimed the prize already?
        return !Yii::app()->redis->getClient()->exists('contest:list:'.$this->id.':claimed-'.$this->uid);
    }

    public function claimPrize()
    {
        if (!$this->canClaimPrize()) {
            return false;
        }

        Yii::app()->player->model->updateAttributes(['dollar'=>$this->prizePerWinner], []);
        Yii::app()->redis->getClient()->set('contest:list:'.$this->id.':claimed-'.$this->uid, date('Y.m.d. H:i:s'));
        return true;
    }

    public function seeContest()
    {
        if (Yii::app()->player->newContest) {
            Yii::app()->redis->getClient()->set('contest:lastcheck:'.$this->uid, time());
            echo 'checked';
        }
    }
}
