<?php
/**
 * @property integer $uid
 * @property string $user
 * @property string $registered
 * @property integer $level
 * @property integer $status_points
 * @property integer $energy
 * @property integer $energy_max
 * @property integer $energy_missing
 * @property integer $energyRequiredForDuel
 * @property integer $skill
 * @property integer $strength
 * @property integer $dollar
 * @property integer $gold
 * @property integer $xp_all
 * @property integer $xp_delta
 * @property integer $xp_remaining
 * @property integer $last_location
 * @property integer $owned_items
 * @property integer $owned_baits
 * @property string $found_setitem_time
 * @property integer $found_setitem_xp
 * @property integer $tutorial_mission
 * @property integer $in_club
 * @property integer $level_percent
 * @property integer $refillPerInterval
 * @property integer $energyRefillPerInterval
 * @property integer $remainingTimeToRefill
 * @property integer $justAdvanced
 * @property integer $freeSlots
 * @property boolean $black_market
 * @property integer $skill_extended
 * @property string $clubName
 */
class Player extends CModel implements ISubject
{
    const ENERGY_REFILL_INTERVAL = 300; //5min

    private $uid;
    private $user;
    private $registered;
    private $xp_all;
    private $xp_delta;
    private $xp_recommended;
    private $level;
    private $status_points;
    private $energy_max;
    private $energy_incr_at;
    private $energy;
    private $skill;
    private $skill_extended;
    private $strength;
    private $dollar;
    private $gold;
    private $last_location;
    private $owned_items;
    private $owned_baits;
    private $found_setitem_time;
    private $found_setitem_xp;
    private $duel_points;
    private $tutorial_mission;
    private $in_club;
    private $black_market;

    private $justAdvanced;

    public function attributeNames()
    {
        return [];
    }

    public function getUid()
    {
        return (int)$this->uid;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getRegistered()
    {
        return $this->registered;
    }

    public function getLevel()
    {
        return (int)$this->level;
    }

    public function getStatus_points()
    {
        return $this->itsMe() ? (int)$this->status_points : 0;
    }

    public function getEnergy()
    {
        return (int)$this->energy;
    }

    public function getEnergy_max()
    {
        return (int)$this->energy_max;
    }

    public function getEnergy_missing()
    {
        return $this->energy_max - $this->energy;
    }

    public function getEnergyRequiredForDuel()
    {
        return round($this->energy_max / 10);
    }

    public function getSkill()
    {
        return (int)$this->skill;
    }

    public function getStrength()
    {
        return (int)$this->strength;
    }

    public function getDollar()
    {
        return (int)$this->dollar;
    }

    public function getGold()
    {
        return (int)$this->gold;
    }

    public function getXp_all()
    {
        return (int)$this->xp_all;
    }

    public function getXp_delta()
    {
        return (int)$this->xp_delta;
    }

    public function getXp_remaining()
    {
        return (int)$this->xp_recommended - (int)$this->xp_delta;
    }

    public function getLast_location()
    {
        return (int)$this->last_location;
    }

    public function getOwned_items()
    {
        return (int)$this->owned_items;
    }

    public function getOwned_baits()
    {
        return (int)$this->owned_baits;
    }

    public function getFound_setitem_time()
    {
        return $this->found_setitem_time > $this->registered ? $this->found_setitem_time : $this->registered;
    }

    public function getFound_setitem_xp()
    {
        return (int)$this->found_setitem_xp;
    }

    public function getTutorial_mission()
    {
        return (int)$this->tutorial_mission;
    }

    public function getIn_club()
    {
        return (int)$this->in_club;
    }
    
    public function getLevel_percent()
    {
        if (!$this->xp_recommended) {
            return 0;
        }
        $percent = (int)$this->xp_delta / ((int)$this->xp_recommended / 100);
        
        if ($percent < 0) {
            $percent = 0;
        }
        if ($percent > 100) {
            $percent = 100;
        }
        return $percent;
    }

    public function getRefillPerInterval()
    {
        return round($this->energy_max / 10);
    }

    public function getEnergyRefillInterval()
    {
        return self::ENERGY_REFILL_INTERVAL;
    }

    public function getRemainingTimeToRefill()
    {
        $last = strtotime($this->energy_incr_at);
        if ($last < 0) {
            $last = 0;
        }

        $remaining = self::ENERGY_REFILL_INTERVAL - (time() - $last);
        if ($remaining < 0) {
            $remaining = 0;
        }

        return $remaining;
    }
    
    public function getJustAdvanced()
    {
        return (int)$this->justAdvanced;
    }

    public function getFreeSlots()
    {
        return $this->strength - ($this->owned_items + $this->owned_baits);
    }

    public function getBlack_market()
    {
        return (bool)(strtotime($this->black_market) >= time());
    }

    public function itsMe()
    {
        return $this->uid == @$_SESSION['uid'];
    }

    public function getSkill_extended()
    {
        return $this->skill_extended>0 ? $this->skill_extended : 1;
    }
    
    public function getClubName()
    {
        if (!$this->in_club) {
            return false;
        }

        $club = new Club;
        $club->id = $this->in_club;
        $club->fetchName();
        return $club->name;
    }

    public function setSubjectId($id)
    {
        $this->uid = (int)$id;
    }

    public function setOwned_baits($baits)
    {
        $this->owned_baits = (int)$baits;
    }

    public function setOwned_items($items)
    {
        $this->owned_items = (int)$items;
    }

    public function fetchUser()
    {
        $user = Yii::app()->db->cache(86400)->createCommand()
            ->select('user')
            ->from('main')
            ->where('uid=:uid', [':uid'=>$this->uid])
            ->queryScalar();
        if (!$user) {
            $user = '???';
        }
        $this->user = $user;
    }
    
    public function getSubjectName()
    {
        $name = Yii::app()->db->cache(86400)->createCommand()
            ->select('user')
            ->from('main')
            ->where('uid=:uid', [':uid'=>$this->uid])
            ->queryScalar();
        if (!$name) {
            $name = '???';
        }
        return $name;
    }

    public function setAllAttributes($uid = 0)
    {
        $this->subjectId = $uid ? $uid : @$_SESSION['uid'];

        if (!$this->uid) {
            return false;
        }

        //read all from db
        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('main')
            ->where('uid=:uid', [':uid'=>$this->uid])
            ->queryRow();

        if (!is_array($res)) {
            $this->uid = 0;
            return false;
        }

        foreach ($res as $k => $v) {
            $this->$k = $v;
        }
    }

    public function rest()
    {
        $refillable = $this->energy_max - $this->energy;
        if (!$refillable) {
            return false; //don't need to rest
        }

        $now = time();
        $last = strtotime($this->energy_incr_at);
        if ($last<0) {
            $last = 0;
        }

        $interval = $now - $last;
        if ($interval < self::ENERGY_REFILL_INTERVAL) {
            return false;
        }

        $refillMultiplier = floor($interval / self::ENERGY_REFILL_INTERVAL); //incement energy every 5 minutes

        $refillSum = $refillMultiplier * $this->refillPerInterval;
        if ($refillSum > $refillable) {
            $refillSum = $refillable;
        }

        if ($refillSum < 1) {
            return false; //don't need to rest
        }

        $this->energy += $refillSum;

        $remain = 0;
        if ($interval <= self::ENERGY_REFILL_INTERVAL * 5) {
            $remain = $interval - ($refillMultiplier * self::ENERGY_REFILL_INTERVAL);
        }

        $this->energy_incr_at = date("Y-m-d H:i:s", $now-$remain);

        Yii::app()->db->createCommand()
            ->update('main', ['energy'=>$this->energy, 'energy_incr_at'=>$this->energy_incr_at], 'uid=:uid', [':uid'=>(int)$this->uid]);
    }

    public function updateAttributes($toIncrement, $toDecrement)
    {
        $this->logEnergyUsage($toDecrement);
        $attributes = [];

        $toIncrement = $this->incrementLevel($toIncrement);

        foreach ($toIncrement as $k => $v) {
            $newValue = $this->$k + $v;
            $attributes[$k] = $newValue;
            $this->$k = $newValue;

            if ($k=='dollar') {
                (new ProfileBadgeActivator())->triggerDollar($this->uid, $newValue);
            }
        }

        //level advance, reset energy
        if (isset($attributes['level'])) {
            unset($toDecrement['energy']);
            Yii::app()->gameLogger->log(['type'=>'level_up']);
        }

        foreach ($toDecrement as $k => $v) {
            if ($v > $this->$k) {
                $v = $this->$k;
            }

            $newValue = $this->$k - $v;
            $attributes[$k] = $newValue;
            $this->$k = $newValue;
        }


        if (!empty($attributes)) {
            Yii::app()->db->createCommand()
            ->update('main', $attributes, 'uid=:uid', [':uid'=>(int)$this->uid]);
        }
    }

    protected function logEnergyUsage($attributes)
    {
        //todo: implement redis log
        return false;
    }

    public function rewriteAttributes($attributes)
    {
        foreach ($attributes as $k => $v) {
            $this->$k = $v;
        }

        Yii::app()->db->createCommand()
            ->update('main', $attributes, 'uid=:uid', [':uid'=>(int)$this->uid]);
    }

    private function incrementLevel($incr)
    {
        if (!isset($incr['xp_delta']) || $incr['xp_delta'] < 1) {
            return $incr; //do not touch level
        }

        $remaining = $this->getXp_remaining();
        if ($incr['xp_delta'] < $remaining) {
            return $incr; //don't advance to next level
        }

        //advance
        $incr['level'] = 1;
        $this->xp_delta = $incr['xp_delta'] - $remaining; //set delta
        $incr['xp_delta'] = 0; //do not increase further
        $incr['xp_recommended'] = $this->nextXpRecommended();
        $incr['status_points'] = 4;

        $this->energy = $this->energy_max;
        $incr['energy'] = 0;

        $this->justAdvanced = true;

        (new ProfileBadgeActivator())->triggerLevel($this->uid, $this->level+1);
        return $incr;
    }

    private function nextXpRecommended()
    {
        $recommendations = [
            //fromLevel => recommended xp gain to the NEXT level
            1 => 3,
            5 => 5,
            10 => 10,
            20 => 15,
            30 => 20,
            40 => 30,
            50 => 10,
            80 => 15,
            90 => 20,
            100 => 20,
            120 => 35,
            140 => 40,
            160 => 30,
            180 => 45,
            200 => 50,
            ];
        $search = $this->level+1;
        for ($i=$search; $i>0; $i--) {
            if (isset($recommendations[$i])) {
                return $recommendations[$i];
            }
        }
        return 0;
    }
}
