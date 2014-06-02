<?php
/**
 * @property integer $uid
 * @property integer $inClub
 * @property array $items
 * @property string $boardType
 * @property string $range
 * @property string $title
 * @property string $playerRankDescription
 * @property string $clubRankDescription
 */
class Leaderboard extends CModel
{
    const BOARD_RANGE = 5;
    const TYPE_PLAYER = 'board_p';
    const TYPE_CLUB = 'board_c';

    const RANGE_ACTUAL = 'actual';
    const RANGE_PREVIOUS = 'prev';
    const RANGE_LAST_SIX = 'last';

    private $_uid;
    private $_inClub;
    private $_items = [];
    private $_key = '';
    private $_boardType;
    private $_range;

    public function attributeNames() {
        return [];
    }

    public function getUid() { return $this->_uid; }
    public function getInClub() { return $this->_inClub; }
    public function getItems() { return $this->_items; }
    public function getBoardType() { return $this->_boardType; }
    public function getRange() { return $this->_range; }
    public function getTitle() {
        $titles = [
            self::TYPE_PLAYER => [
            self::RANGE_ACTUAL => 'Aktuális hónap legjobb játékosai',
            self::RANGE_PREVIOUS => 'Előző hónap legjobb játékosai',
            self::RANGE_LAST_SIX => 'Utolsó 6 hónap legjobb játékosai',
            ],
            self::TYPE_CLUB => [
            self::RANGE_ACTUAL => 'Aktuális hónap legjobb klubjai',
            self::RANGE_PREVIOUS => 'Előző hónap legjobb klubjai',
            self::RANGE_LAST_SIX => 'Utolsó 6 hónap legjobb klubjai',
            ]
            ];
        return $titles[$this->boardType][$this->range];
    }

    public function getPlayerRankDescription() {
        $redis = Yii::app()->redis->getClient();

        $total = $redis->zCard($this->_key);
        $rank  = $redis->zRevRank($this->_key, $this->_uid) + 1;

        if ($rank == 1) {
            return 'Te vagy a király!';
        } else {
            $percent = round((1 - ($rank / $total)) * 100, 1);
            if (!$percent) return 'Több párbajgyőzelemre van szükséged.';

            return 'Jobb vagy, mint a játékosok ' . $percent . '%-a!';
        }
    }

    public function getClubRankDescription() {
        $redis = Yii::app()->redis->getClient();

        $total = $redis->zCard($this->_key);
        $rank  = $redis->zRevRank($this->_key, $this->_inClub) + 1;

        if ($rank == 1) {
            return 'A te klubod a király!';
        } else {
            $percent = round((1 - ($rank / $total)) * 100, 1);
            if (!$percent) return 'Több versenygyőzelemre van szükségetek.';

            return 'Jobb a klubod, mint a többi klub ' . $percent . '%-a!';
        }
    }

    public function setBoardType ($type) {
        $this->_boardType = $type;
    }
    public function setRange ($range) {
        $d = new DateTime();

        //create key from date/intersect
        switch ($range) {
        case self::RANGE_LAST_SIX:
            $this->_key = $this->_boardType . ':6month';
            //create intersect

            $history = [];
            $history[] = $this->_boardType . ':' . $d->format('Ym');            

            for ($i=0; $i<6; $i++) {
                $d->modify( 'first day of previous month' );
                $history[] = $this->_boardType . ':' . $d->format('Ym');            
            }

            $redis = Yii::app()->redis->getClient();
            $redis->zUnionStore($this->_key, $history);

            break;
        case self::RANGE_PREVIOUS:
            $d->modify( 'first day of previous month' );
            $this->_key = $this->_boardType . ':' . $d->format('Ym');
            break;
        default:
            $range = self::RANGE_ACTUAL;
            $this->_key = $this->_boardType . ':' . $d->format('Ym');
        }
        $this->_range = $range;
    }

    public function setUid($uid) {
        $this->_uid = (int)$uid;
    }
    public function setInClub($id) {
        $this->_inClub = (int)$id;
    }

    public function fetch() {
        $redis = Yii::app()->redis->getClient();

        $myRank = $redis->zRevRank($this->_key, $this->_uid);

        $range = self::BOARD_RANGE;
        $min = $myRank - $range > 0 ? $myRank - $range : 0;
        $max = $myRank + $range + ($range-$myRank+$min);
        //echo "search:$this->_inClub, mr:$myRank, min:$min, max:$max, \n";

        $item = new Player;
        $i = $min+1;
        foreach($redis->zRevRange($this->_key, $min, $max, true) as $id => $score) {
            $item->uid = $id;
            $item->fetchUser();

            $this->_items[$i] = [
                'id'=>$id,
                'name'=>$item->user,
                'score'=>$score,
                ];
            $i++;
        }
    }

    public function fetchClubs() {
        $redis = Yii::app()->redis->getClient();

        $myRank = $redis->zRevRank($this->_key, $this->_inClub);

        $range = self::BOARD_RANGE;
        $min = $myRank - $range > 0 ? $myRank - $range : 0;
        $max = $myRank + $range + ($range-$myRank+$min);
        //echo "search:$this->_inClub, mr:$myRank, min:$min, max:$max, \n";

        $item = new Club;
        $i = $min+1;
        foreach($redis->zRevRange($this->_key, $min, $max, true) as $id => $score) {
            $item->id = $id;
            $item->fetchName();

            $this->_items[$i] = [
                'id'=>$id,
                'name'=>$item->name,
                'score'=>$score,
                ];
            $i++;
        }
    }



    public function getRankDescription() {
        if ($this->_boardType == self::TYPE_CLUB) {
            $ret = $this->getClubRankDescription();
        } else {
            $ret = $this->getPlayerRankDescription();
        }
        return $ret;
    }
}
