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

    private $uid;
    private $inClub;
    private $items = [];
    private $key = '';
    private $boardType;
    private $range;

    public function attributeNames()
    {
        return [];
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getInClub()
    {
        return $this->inClub;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getBoardType()
    {
        return $this->boardType;
    }

    public function getRange()
    {
        return $this->range;
    }

    public function getTitle()
    {
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

    public function getPlayerRankDescription()
    {
        $redis = Yii::app()->redis->getClient();

        $total = $redis->zCard($this->key);
        $rank  = $redis->zRevRank($this->key, $this->uid) + 1;

        if ($rank == 1) {
            return 'Te vagy a király!';
        } else {
            $percent = round((1 - ($rank / $total)) * 100, 1);
            if (!$percent) {
                return 'Több párbajgyőzelemre van szükséged.';
            }

            return 'Jobb vagy, mint a játékosok ' . $percent . '%-a!';
        }
    }

    public function getClubRankDescription()
    {
        $redis = Yii::app()->redis->getClient();

        $total = $redis->zCard($this->key);
        $rank  = $redis->zRevRank($this->key, $this->inClub) + 1;

        if ($rank == 1) {
            return 'A te klubod a király!';
        } else {
            $percent = round((1 - ($rank / $total)) * 100, 1);
            if (!$percent) {
                return 'Több versenygyőzelemre van szükségetek.';
            }

            return 'Jobb a klubod, mint a többi klub ' . $percent . '%-a!';
        }
    }

    public function setBoardType ($type)
    {
        $this->boardType = $type;
    }

    public function setRange ($range)
    {
        $d = new DateTime();

        //create key from date/intersect
        switch ($range) {
            case self::RANGE_LAST_SIX:
                $this->key = $this->boardType . ':6month';
                //create intersect

                $history = [];
                $history[] = $this->boardType . ':' . $d->format('Ym');

                for ($i=0; $i<6; $i++) {
                    $d->modify('first day of previous month');
                    $history[] = $this->boardType . ':' . $d->format('Ym');            
                }

                $redis = Yii::app()->redis->getClient();
                $redis->zUnionStore($this->key, $history);

                break;
            case self::RANGE_PREVIOUS:
                $d->modify('first day of previous month');
                $this->key = $this->boardType . ':' . $d->format('Ym');
                break;
            default:
                $range = self::RANGE_ACTUAL;
                $this->key = $this->boardType . ':' . $d->format('Ym');
        }
        $this->range = $range;
    }

    public function setUid($uid)
    {
        $this->uid = (int)$uid;
    }

    public function setInClub($id)
    {
        $this->inClub = (int)$id;
    }

    public function fetch()
    {
        $redis = Yii::app()->redis->getClient();

        $myRank = $redis->zRevRank($this->key, $this->uid);

        $range = self::BOARD_RANGE;
        $min = $myRank - $range > 0 ? $myRank - $range : 0;
        $max = $myRank + $range + ($range-$myRank+$min);
        //echo "search:$this->inClub, mr:$myRank, min:$min, max:$max, \n";

        $item = new Player;
        $i = $min+1;
        foreach ($redis->zRevRange($this->key, $min, $max, true) as $id => $score) {
            $item->uid = $id;
            $item->fetchUser();

            $this->items[$i] = [
                'id'=>$id,
                'name'=>$item->user,
                'score'=>$score,
                ];
            $i++;
        }
    }

    public function fetchClubs()
    {
        $redis = Yii::app()->redis->getClient();

        $myRank = $redis->zRevRank($this->key, $this->inClub);

        $range = self::BOARD_RANGE;
        $min = $myRank - $range > 0 ? $myRank - $range : 0;
        $max = $myRank + $range + ($range-$myRank+$min);
        //echo "search:$this->inClub, mr:$myRank, min:$min, max:$max, \n";

        $item = new Club;
        $i = $min+1;
        foreach ($redis->zRevRange($this->key, $min, $max, true) as $id => $score) {
            $item->id = $id;
            $item->fetchName();

            $this->items[$i] = [
                'id'=>$id,
                'name'=>$item->name,
                'score'=>$score,
                ];
            $i++;
        }
    }

    public function getRankDescription()
    {
        if ($this->boardType == self::TYPE_CLUB) {
            $ret = $this->getClubRankDescription();
        } else {
            $ret = $this->getPlayerRankDescription();
        }
        return $ret;
    }
}
