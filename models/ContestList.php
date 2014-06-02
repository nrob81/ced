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
 * @property array $leaders
 * @property array $winner
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

    private $_id;
    private $_uid;
    private $_isValid;
    private $_collect;
    private $_prize;
    private $_maxScore;
    private $_list;
    private $_leaders;
    private $_winners;
    private $_history;
    private $_collectTypes = [
        'xp','xp_duel','xp_mission',
        'dollar','dollar_duel','dollar_mission'
        ];
    
    public function attributeNames() {
        return [];
    }

    public function getId() {
        return (int)$this->_id;
    }
    public function getCollect() {
        return $this->_collect;
    }
    public function getPrize() {
        return (int)$this->_prize;
    }
    public function getDescriptionId() {
        if (!in_array($this->collect, $this->_collectTypes)) {
            return 'xp';
        }
        return $this->collect;
    }

    
    public function getIsValid() {
        if (is_null($this->_isValid)) {
            $this->_isValid = Yii::app()->redis->getClient()->exists('contest:list:'.$this->_id.':created');
        }

        return (bool)$this->_isValid;
    }
    public function getIsActive() {
        if (!$this->_id) return false;

        $active = (int)Yii::app()->redis->getClient()->get('contest:active');
        return $active == $this->_id;
    }
    
    public function getSecUntilEnd() {
        $sue = ($this->_id + self::LIFETIME - time());
        return $sue;
    }

    public function getLeaders() {
        return $this->_leaders;
    }
    public function hasWinner() { 
        //if ($this->secUntilEnd > 0) return false; //contest is active
        return Yii::app()->redis->getClient()->exists('contest:list:'.$this->_id.':winners'); 
    }
    public function getWinners() {
        return $this->_winners;
    }
    public function getList() {
        return $this->_list;
    }
    public function getHistory() {
        if (!$this->_history) {
            $this->_history = Yii::app()->redis->getClient()->lRange('contest:log', 0, 5);
        }
        return $this->_history;
    }
    
    public function getRankDescription() {
        if (!$this->isActive) return '';

        $redis = Yii::app()->redis->getClient();

        $key = 'contest:list:'.$this->_id.':points';
        $total = $redis->zCard($key);
        $rank  = $redis->zRevRank($key, $this->_uid) + 1;

        if ($rank == 1) {
            return 'Te vagy a király!';
        } else {
            $percent = round((1 - ($rank / $total)) * 100, 1);
            if (!$percent) return 'Húzz bele, ha nyerni szeretnél!';

            return 'Jobb vagy, mint a versenytársaid ' . $percent . '%-a!';
        }
    }
    
    public function getMaxScore() {
        if (!$this->_maxScore) {
            $redis = Yii::app()->redis->getClient();
            $key = 'contest:list:'.$this->_id.':points';
            $max = $redis->zRevRange($key, 0, 0, true);
            
            if (count($max)) {
                $this->_maxScore = array_values($max)[0];
            }
        }

        return (int)$this->_maxScore;
    }
    public function getLastId() {
        $id = Yii::app()->redis->getClient()->lINDEX('contest:log', 0);
        return (int)$id;
    }
    public function getPrizePerWinner() {
        if (!count($this->_winners)) return Contest::PRIZE;

        return ceil(Contest::PRIZE / count($this->_winners));
    }

    public function setId($id) {
        $this->_id = (int)$id;
    }
    public function setUid($uid) {
        $this->_uid = (int)$uid;
    }

    public function fetchDetails() {
        $redis = Yii::app()->redis->getClient();
        $this->_collect = $redis->get('contest:list:'.$this->_id.':collect'); 
        $this->_prize = $redis->get('contest:list:'.$this->_id.':prize'); 
    }

    public function fetchList() {
        $redis = Yii::app()->redis->getClient();

        $myRank = $redis->zRevRank('contest:list:'.$this->_id.':points', $this->_uid);

        $range = self::BOARD_RANGE;
        $min = $myRank - $range > 0 ? $myRank - $range : 0;
        $max = $myRank + $range + ($range-$myRank+$min);

        $item = new Player;
        $i = $min+1;
        foreach($redis->zRevRange('contest:list:'.$this->_id.':points', $min, $max, true) as $id => $score) {
            $item->uid = (int)$id;
            $item->fetchUser();

            $this->_list[$i] = [
                'id'=>$id,
                'name'=>$item->user,
                'score'=>$score,
                ];
            $i++;
        }
    }
    
    public function fetchLeaders() {
        if (!$this->maxScore) return false;
        
        $redis = Yii::app()->redis->getClient();
        $res = $redis->zRevRangeByScore('contest:list:'.$this->_id.':points', $this->_maxScore, $this->_maxScore);

        $leaders = [];
        $item = new Player;
        foreach($res as $id) {
            $item->uid = $id;
            $item->fetchUser();

            $leaders[$id] = [
                'name'=>$item->user,
                'score'=>$this->_maxScore,
                ];
        }
        $this->_leaders = $leaders;
    }
    
    public function fetchWinners() {
        if (!$this->maxScore) return false;
        $res = Yii::app()->redis->getClient()->smembers('contest:list:'.$this->_id.':winners');

        $winners = [];
        $item = new Player;
        foreach($res as $id) {
            $item->uid = $id;
            $item->fetchUser();

            $winners[$id] = [
                'name'=>$item->user,
                'score'=>$this->_maxScore,
                ];
        }
        $this->_winners = $winners;
    }
    
    
    public function canClaimPrize() {
        //is active?
        if (!$this->hasWinner()) return false;

        //she is winner?
        if (!array_key_exists($this->_uid, $this->winners)) return false;

        //have we the prize/winner?
        if ($this->prizePerWinner < 1) return false;

        //she has claimed the prize already?
        if (Yii::app()->redis->getClient()->exists('contest:list:'.$this->_id.':claimed-'.$this->_uid)) return false;

        return true;
    }
    public function claimPrize() {
        if (!$this->canClaimPrize()) return false;

        Yii::app()->player->model->updateAttributes(['dollar'=>$this->prizePerWinner], []);
        Yii::app()->redis->getClient()->set('contest:list:'.$this->_id.':claimed-'.$this->_uid, date('Y.m.d. H:i:s'));
        return true;
    }

    public function seeContest() {
        if (Yii::app()->player->newContest) {
            Yii::app()->redis->getClient()->set('contest:lastcheck:'.$this->_uid, time());            
            echo 'checked';
        }
    }
}
