<?php
class Player extends CModel
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

    private $usedItems = [];
    private $usedBaits = [];

    private $justAdvanced;

    private $stats = [];

    public function attributeNames() {
        return [];
    }

    // getters
    public function getUid() { return (int)$this->uid; }
    public function getUser() { return $this->user; }
    public function getRegistered() { return $this->registered; }
    public function getLevel() { return (int)$this->level; }
    public function getStatus_points() { return $this->itsMe() ? (int)$this->status_points : 0; }
    public function getEnergy() { return (int)$this->energy; }
    public function getEnergy_max() { return (int)$this->energy_max; }
    public function getEnergy_missing() { return $this->energy_max - $this->energy; }
    public function getEnergyRequiredForDuel() { return round($this->energy_max / 10); }
    public function getSkill() { return (int)$this->skill; }
    public function getStrength() { return (int)$this->strength; }
    public function getDollar() { return (int)$this->dollar; }
    public function getGold() { return (int)$this->gold; }
    public function getXp_all() { return (int)$this->xp_all; }
    public function getXp_delta() { return (int)$this->xp_delta; }
    public function getXp_remaining() { return (int)$this->xp_recommended - (int)$this->xp_delta; }
    public function getLast_location() { return (int)$this->last_location; }
    public function getOwned_items() { return (int)$this->owned_items; }
    public function getOwned_baits() { return (int)$this->owned_baits; }
    public function getFound_setitem_time() { return $this->found_setitem_time > $this->registered ? $this->found_setitem_time : $this->registered; }
    public function getFound_setitem_xp() { return (int)$this->found_setitem_xp; }
    public function getTutorial_mission() { return (int)$this->tutorial_mission; }
    public function getIn_club() { return (int)$this->in_club; }
    public function getLevel_percent() { 
        if (!$this->xp_recommended) return 0;
        $percent = (int)$this->xp_delta / ((int)$this->xp_recommended / 100);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;
        return $percent;
    }
    public function getRefillPerInterval() {
        return round($this->energy_max / 10);
    }
    public function getEnergyRefillInterval() {
        return self::ENERGY_REFILL_INTERVAL;
    }
    public function getRemainingTimeToRefill() {
        $last = strtotime($this->energy_incr_at);
        if ($last < 0) $last = 0;
        $remaining = self::ENERGY_REFILL_INTERVAL - (time() - $last);
        if ($remaining < 0) $remaining = 0;
        return $remaining;
    }
    public function getDollarImprovement() {
        return 30 + (5 * $this->level);
    }
    public function getSkillImprovement() {
        $di = $this->dollarImprovement;

        //strongest bait
        $bait = Yii::app()->db->createCommand()
            ->select('id, skill, price')
            ->from('baits')
            ->where('level<=:level', [':level'=>(int)$this->level])
            ->order('level DESC')
            ->limit(1)
            ->queryRow();
        $skill = round($di / $bait['price'] * $bait['skill'] / 2 * 0.8);
        return (int)$skill;
    }
    public function getJustAdvanced() { return (int)$this->justAdvanced; }
    public function getStats() { return $this->stats; }
    public function getFreeSlots() { return $this->strength - ($this->owned_items + $this->owned_baits); }
    public function getBlack_market() { 
        return (bool)(strtotime($this->black_market) >= time());
    }

    public function itsMe() {
        return $this->uid == $_SESSION['uid'];
    }
    public function getSkill_extended() { return $this->skill_extended>0 ? $this->skill_extended : 1; }

    public function setSkill_extended() {
        //echo __FUNCTION__ . "\n";

        //calculate
        $limitItems = $this->minOwnedCount();
        //echo "limitItems: {$limitItems}\n";
        $sumSkill = $this->skill;
        //echo "skill: {$sumSkill}\n";

        //get items limited
        $sumSkill += $this->sumSkill($limitItems, false);
        //echo "sum+itemsSkill: {$sumSkill}\n";

        //get baits limited
        $sumSkill += $this->sumSkill($limitItems, true);
        //echo "sum+baitSkill: {$sumSkill}\n";
        if ($sumSkill < 1) $sumSkill = 1; //lowest value for player skill.
        //echo "sumSkill: {$sumSkill}\n";

        $this->skill_extended = (int)$sumSkill;

        //print_r($this->usedItems);
        //print_r($this->usedBaits);
        $this->rewriteAttributes(['skill_extended'=>$this->skill_extended]);
    }


    public function setUid($uid) {
        $this->uid = (int)$uid;
    }
    public function setOwned_baits($baits) {
        $this->owned_baits = (int)$baits;
    }
    public function setOwned_items($items) {
        $this->owned_items = (int)$items;
    }

    public function fetchUser() {
        $user = Yii::app()->db->cache(86400)->createCommand()
            ->select('user')
            ->from('main')
            ->where('uid=:uid', [':uid'=>$this->uid])
            ->queryScalar();
        if (!$user) $user = '???';
        $this->user = $user;
    }

    public function setAllAttributes($uid = 0) {
        $this->setUid($uid);
        if (!$this->uid) $this->uid = @$_SESSION['uid'];
        if (!$this->uid) return false;

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

    public function rest() {
        $refillable = $this->energy_max - $this->energy;
        if (!$refillable) return false; //don't need to rest

        $now = time();
        $last = strtotime($this->energy_incr_at);
        if ($last<0) $last = 0;
        //echo 'REF_INT: ' . self::ENERGY_REFILL_INTERVAL . "\n";
        //echo 'last: '. date(" Y.m.d. H:i:s", $last) . "\n";
        //echo 'now: '. date("Y-m-d. H:i:s", $now) . "\n";

        $interval = $now - $last;
        //echo 'interval: ' . $interval . "\n";
        if ($interval < self::ENERGY_REFILL_INTERVAL) return false;

        $refillMultiplier = floor($interval / self::ENERGY_REFILL_INTERVAL); //incement energy every 5 minutes
        //echo 'refillMultiplier: ' . $refillMultiplier . "\n";

        $refillSum = $refillMultiplier * $this->refillPerInterval;
        if ($refillSum > $refillable) $refillSum = $refillable;
        //echo 'refillSum: ' . $refillSum . "\n";

        if ($refillSum < 1) return false; //don't need to rest

        $this->energy += $refillSum;

        $remain = 0;
        if ($interval <= self::ENERGY_REFILL_INTERVAL * 5) {
            $remain = $interval - ($refillMultiplier * self::ENERGY_REFILL_INTERVAL);
        }

        /*
        echo 'remain: ' . $remain . "\n";
        echo date("now> Y.m.d. H:i:s", $now) . "\n";
        echo date("rem> Y.m.d. H:i:s", $now-$remain) . "\n";
         */

        $this->energy_incr_at = date("Y-m-d H:i:s", $now-$remain);

        $cmd = Yii::app()->db->createCommand()
            ->update('main', ['energy'=>$this->energy, 'energy_incr_at'=>$this->energy_incr_at], 'uid=:uid', [':uid'=>(int)$this->uid]);
    }

    public function incrementForStatuspoint($id) {
        if (!$this->itsMe()) return false;
        if ($this->status_points < 1) return false;

        $mapIdAttribute = [1=>['energy_max'=>1, 'energy'=>1], ['skill'=>2, 'skill_extended'=>2], ['strength'=>2], ['dollar'=>$this->dollarImprovement]];
        $mapIdAttribute[2]['skill'] = $mapIdAttribute[2]['skill_extended'] = $this->skillImprovement;

        $increment = isset($mapIdAttribute[$id]) ? $mapIdAttribute[$id] : false;

        if ($increment) {
            $this->updateAttributes($increment, ['status_points'=>1]);
            //badge
            $b = Yii::app()->badge->model;
            if ($id==1) {
                $b->trigger('max_nrg_35', ['energy_max'=>$this->energy_max]);
                $b->trigger('max_nrg_100', ['energy_max'=>$this->energy_max]);
            }
            if ($id==2) {
                $b->trigger('skill_35', ['skill'=>$this->skill]);
                $b->trigger('skill_100', ['skill'=>$this->skill]);
            }
            if ($id==3) {
                $b->trigger('strength_35', ['strength'=>$this->strength]);
                $b->trigger('strength_100', ['strength'=>$this->strength]);
            }
        }
        return true;
    }

    public function updateAttributes($toIncrement, $toDecrement) {
        $this->logEnergyUsage($toDecrement);
        $attributes = [];

        $toIncrement = $this->incrementLevel($toIncrement);
        //print_r($toIncrement);

        foreach ($toIncrement as $k => $v) {
            $newValue = $this->$k + $v;
            $attributes[$k] = $newValue;
            $this->$k = $newValue;

            if ($k=='dollar') {
                Yii::app()->badge->model->triggerHer($this->uid, 'dollar_50', ['dollar'=>$newValue]);
                Yii::app()->badge->model->triggerHer($this->uid, 'dollar_5000', ['dollar'=>$newValue]);
            }
        }

        //level advance, reset energy
        if (isset($attributes['level'])) {
            unset($toDecrement['energy']);
            Yii::app()->gameLogger->log(['type'=>'level_up']);
        }

        foreach ($toDecrement as $k => $v) {
            if ($v > $this->$k) $v = $this->$k;

            $newValue = $this->$k - $v;
            $attributes[$k] = $newValue;
            $this->$k = $newValue;
        }


        if (!empty($attributes)) {
            $cmd = Yii::app()->db->createCommand()
            ->update('main', $attributes, 'uid=:uid', [':uid'=>(int)$this->uid]);
        }
    }
    protected function logEnergyUsage($attributes)
    {
        return false; //todo: implement redis log

        if (!@$attributes['energy']) {
            return false;
        }

        $used = $attributes['energy'];
        $percent = round($used / ($this->energy_max / 100), 2);
    }
    public function rewriteAttributes($attributes) {
        foreach ($attributes as $k => $v) {
            $this->$k = $v;
        }

        $cmd = Yii::app()->db->createCommand()
            ->update('main', $attributes, 'uid=:uid', [':uid'=>(int)$this->uid]);
    }
    private function incrementLevel($incr) {
        if (!isset($incr['xp_delta']) or $incr['xp_delta'] < 1) return $incr; //do not touch level

        $remaining = $this->getXp_remaining();
        if ($incr['xp_delta'] < $remaining) return $incr; //don't advance to next level

        //advance
        $incr['level'] = 1;
        $this->xp_delta = $incr['xp_delta'] - $remaining; //set delta
        $incr['xp_delta'] = 0; //do not increase further
        $incr['xp_recommended'] = $this->nextXpRecommended();
        $incr['status_points'] = 4;

        $this->energy = $this->energy_max;
        $incr['energy'] = 0;

        $this->justAdvanced = true;

        Yii::app()->badge->model->triggerHer($this->uid, 'level_10', ['level'=>$this->level+1]);
        Yii::app()->badge->model->triggerHer($this->uid, 'level_100', ['level'=>$this->level+1]);
        return $incr;
    }

    private function nextXpRecommended() {
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


    public function areOwnedItemsMore() {
        return $this->owned_items > $this->owned_baits;
    }
    public function minOwnedCount() {
        $smaller = $this->owned_items < $this->owned_baits ? $this->owned_items : $this->owned_baits;
        return $smaller;
    }

    /**
     * Reads the max number of items from the database that can be used and sums their skill points.
     */
    private function sumSkill($limitItems, $isBait = false) {
        $table = $isBait ? 'users_baits' : 'users_items';
        $skill = 0;
        $countItem = 0;
        $loop = 0;
        $limit = 10;
        do {
            $doLoop = false;
            $offset = $loop * $limit;
            //echo "LIMIT {$offset}, {$limit}\n";
            $res = Yii::app()->db->createCommand()
                ->select('item_id, item_count, skill')
                ->from($table)
                ->where('uid=:uid', [':uid'=>$this->uid])
                ->order('skill DESC')
                ->offset($offset)
                ->limit($limit)
                ->queryAll();
            foreach ($res as $item) {
                //echo "item_id: {$item['item_id']}, ";
                //echo "item_count: {$item['item_count']}, ";
                //echo "skill: {$item['skill']} | ";

                $toAdd = $limitItems - $countItem;
                if ($toAdd > $item['item_count']) $toAdd = $item['item_count'];
                //echo "toAdd: {$toAdd}, ";
                $countItem += $toAdd;
                //echo "countItem: {$countItem}, ";

                if ($toAdd) {
                    $skill += $toAdd * $item['skill'];
                    //echo "skill: {$skill}, ";

                    //add items to inventory
                    if ($isBait) {
                        $this->usedBaits[$item['item_id']] = $toAdd;
                    } else {
                        $this->usedItems[$item['item_id']] = $toAdd;
                    }
                }

                $doLoop = $countItem < $limitItems;
                //echo 'doLoop:' . $doLoop . "\n";
                if (!$doLoop) break;
            }
            $loop++;
        } while ($doLoop); 

        return $skill;
    }

    public function countOwnedBaitsOf($id) {
        $res = Yii::app()->db->createCommand()
            ->select('item_count')
            ->from('users_baits')
            ->where('uid=:uid AND item_id=:item_id', [':uid'=>$this->uid, ':item_id'=>(int)$id])
            ->queryScalar();
        return (int)$res;
    }

    public function chanceAgainstMission($skillMission) {
        $skillPlayer = $this->getSkill_extended();
        //echo "$skillPlayer vs. $skillMission\n";

        $all = $skillMission + $skillPlayer;

        $percentPlayer = round($skillPlayer / ($all / 100), 1);
        $percentMission = round($skillMission / ($all / 100), 1);
        //echo "$percentPlayer% vs. $percentMission% \n"; 

        if ($percentPlayer >= 90) {
            $percentPlayer = 100;
            //echo "new $percentPlayer% \n"; 
        }
        return $percentPlayer;      
    }

    public function fetchStats() {
        $s = [
            'visited_waters'=>0,
            'visited_counties'=>0,
            'owned_setitems'=>0,
            'setitems'=>[],
            'items'=>[],
            'baits'=>[],
            'sets'=>[],
            'counties'=>[],
            ];       

        //mission counter
        $res = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('users_missions')
            ->where('uid=:uid', [':uid'=>$this->uid])
            ->queryScalar();
        $s['completed_missions'] = $res;

        //water,county counter
        $res = Yii::app()->db->createCommand()
            ->select('v.*, w.county_id, w.title')
            ->from('visited v')
            ->join('waters w', 'v.water_id=w.id')
            ->where('v.uid=:uid', [':uid'=>$this->uid])
            ->order('v.water_id')
            ->queryAll();
        foreach ($res as $dat) {
            $s['routine'][$dat['water_id']] = $dat['routine'];
            $s['visited_waters']++;
            if (!array_key_exists($dat['county_id'], $s['counties'])) $s['visited_counties']++;
            $s['counties'][$dat['county_id']] = 1;
        }

        $logger = new Logger; 
        $logger->uid = $this->uid;
        $stat = $logger->getCounters();

        //duels
        /*$res = Yii::app()->db->createCommand()
            ->select('SUM(duel_success) AS ds, SUM(duel_fail) AS df')
            ->from('log_counters')
            ->where('uid=:uid', [':uid'=>$this->uid])
        ->queryRow();*/
        $s['duel_success'] = @(int)$stat['duel_success'];
        $s['duel_fail'] = @(int)$stat['duel_fail'];
        $s['duel_rate'] = '?';
        if ($s['duel_success'] or $s['duel_fail']) {
            $s['duel_rate'] = round( $s['duel_success'] / (($s['duel_success'] + $s['duel_fail'])/100) ,1);
        }

        //sets
        $res = Yii::app()->db->createCommand()
            ->select('item_id, item_count')
            ->from('users_items')
            ->where('uid=:uid AND item_id>999 AND item_count>0', [':uid'=>$this->uid])
            ->order('skill DESC, item_id DESC')
            ->queryAll();
        $best = 0;
        foreach ($res as $dat) {
            if ($best < 3 and $dat['item_count']) {
                $s['setitems'][$dat['item_id']] = $dat['item_count'];
                $best++;
            }
            $s['owned_setitems'] += $dat['item_count'];
        }

        //best items/baits/sets
        $res = Yii::app()->db->createCommand()
            ->select('item_id')
            ->from('users_items')
            ->where('uid=:uid AND item_id < 1000', [':uid'=>$this->uid])
            ->order('skill DESC')
            ->limit(3)
            ->queryAll();
        foreach ($res as $dat) {
            $i = new Item;
            $i->item_type = Item::TYPE_ITEM;
            $i->id = $dat['item_id'];
            $i->fetch();
            $s['items'][] = $i->title;
        }

        $res = Yii::app()->db->createCommand()
            ->select('item_id')
            ->from('users_baits')
            ->where('uid=:uid', [':uid'=>$this->uid])
            ->order('skill DESC')
            ->limit(3)
            ->queryAll();
        foreach ($res as $dat) {
            $i = new Item;
            $i->item_type = Item::TYPE_BAIT;
            $i->id = $dat['item_id'];
            $i->fetch();
            $s['baits'][] = $i->title;
        }

        foreach ($s['setitems'] as $dat => $cnt) {
            $i = new Item;
            $i->item_type = Item::TYPE_ITEMSET;
            $i->id = $dat;
            $i->fetchSet();
            $s['sets'][] = $i->title;
        }

        $redis = Yii::app()->redis->getClient();

        $rank  = $redis->zRevRank('board_p:'.date('Ym'), $this->uid);
        //var_dump($rank);
        $s['rankActual'] = $rank === false ? false : ++$rank;

        $rank  = $redis->zRevRank('board_p:6month', $this->uid);
        $s['rank'] = $rank === false ? false : ++$rank;

        $this->stats = $s;
    }

    public function getClubName() {
        if (!$this->in_club) return false;

        $club = new Club;
        $club->id = $this->in_club;
        $club->fetchName();
        return $club->name;
    } 
}
