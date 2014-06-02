<?php
/**
 * @property integer $id
 * @property integer $water_id
 * @property integer $gate
 * @property integer $req_energy
 * @property integer $req_bait_1
 * @property integer $req_bait_1_count
 * @property integer $req_bait_2
 * @property integer $req_bait_2_count
 * @property array $req_baits
 * @property integer $award_xp
 * @property integer $award_dollar_min
 * @property integer $award_dollar_max
 * @property integer $award_setpart
 * @property integer $routine_gain
 * @property integer $skill
 * @property string $title
 * @property string $txt
 * @property string $gate_name
 * @property boolean $gate_visited
 * @property string $award_dollar
 * @property integer $routine
 * @property integer $chance
 * @property integer $gained_xp
 * @property integer $gained_dollar
 * @property integer $gained_routine
 * @property boolean $gained_visit
 * @property array $reqPassed
 * @property array $errors
 * @property boolean $success
 * @property Item $found_setpart
 */
class Mission extends CModel
{
    private $id;
    private $water_id;
    private $gate;
    private $req_energy;
    private $req_energy_expansion = 0;
    private $req_bait_1;
    private $req_bait_1_count;
    private $req_bait_2;
    private $req_bait_2_count;
    private $req_baits; //list of item classes
    private $award_xp;
    private $award_dollar_min;
    private $award_dollar_max;
    private $award_setpart;
    private $routine_gain;
    private $routine_reduction = 0;
    private $skill;
    private $title;
    private $txt;
    private $gate_name;
    private $gate_visited;
    private $routine;
    private $chance;
    private $gained_xp;
    private $gained_dollar;
    private $gained_routine;
    private $gained_visit;
    private $found_setpart;
    private $skill_extended_at_visit;

    private $_reqPassed = [];
    private $_errors = ['requirements'=>false, 'inexperienced'=>false, 'routineFull'=>false];
    private $_success;
    
    public function attributeNames() {
        return [];
    }

    /* getters */
    public function getId() { return $this->id; }
    public function getWater_id() { return $this->water_id; }
    public function getGate() { return $this->gate; }
    public function getReq_energy() { return $this->req_energy; }
    public function getReq_bait_1() { return $this->req_bait_1; }
    public function getReq_bait_1_count() { return $this->req_bait_1_count; }
    public function getReq_bait_2() { return $this->req_bait_2; }
    public function getReq_bait_2_count() { return $this->req_bait_2_count; }
    public function getReq_baits() { return $this->req_baits; }
    public function getAward_xp() { return $this->award_xp; }
    public function getAward_dollar_min() { return $this->award_dollar_min; }
    public function getAward_dollar_max() { return $this->award_dollar_max; }
    public function getAward_setpart() { return $this->award_setpart; }
    public function getRoutine_gain() { return $this->routine_gain - $this->routine_reduction; }
    public function getSkill() { return $this->skill; }
    public function getTitle() { return $this->title; }
    public function getTxt() { return $this->txt; }
    public function getGate_name() { return $this->gate_name; }
    public function getGate_visited() { return $this->gate_visited; }
    public function getAward_dollar() {
        if ($this->award_dollar_min == $this->award_dollar_max) {
            return $this->award_dollar_min . '$';
        }

        return $this->award_dollar_min . '$ - ' . $this->award_dollar_max . '$';
    }
    public function getRoutine() { return $this->routine; }
    public function getChance() { return $this->chance; }
    public function getGained_xp() { return (int)$this->gained_xp; }
    public function getGained_dollar() { return (int)$this->gained_dollar; }
    public function getGained_routine() { return (int)$this->gained_routine; }
    public function getGained_visit() { return (bool)$this->gained_visit; }
    public function getReqPassed() { return $this->_reqPassed; }
    public function getErrors() { return $this->_errors; }
    public function getSuccess() { return $this->_success; }
    public function getFound_setpart() { return $this->found_setpart; }

    /* setters */
    public function setid($id) {
        $this->id = (int)$id;
    }
    public function setGate_name($name) {
        $this->gate_name = $name;
    }
    public function setRoutine($routine) {
        $this->routine = (int)$routine;
    }
    public function setGate_visited($visited) {
        $this->gate_visited = (bool)$visited;
    }
    public function setGained_visit($visit) {
        $this->gained_visit = (bool)$visit;
        $this->gate_visited = (bool)$visit;
    }
    public function setRoutine_reduction($reduction) {
        $this->routine_reduction = (int)$reduction;
    }
    public function setReq_energy_expansion($exp) {
        $this->req_energy_expansion = (int)$exp;
    }
    public function setSkill_extended_at_visit($value) {
        $this->skill_extended_at_visit = (int)$value;
        if ($this->skill_extended_at_visit < 2) $this->skill_extended_at_visit = 2; //min SEAV
    }
    
    public function addReqPassed($requirement, $passed) { 
        $this->_reqPassed[$requirement] = (boolean)$passed;
    }

    public function fetch() {
        if (!$this->id) return false;

        //read all from db
        $dependency = new CExpressionDependency('Yii::app()->params["missions_version"]');        
        $res = Yii::app()->db->cache(Yii::app()->params['cacheDuration'], $dependency)->createCommand()
            ->select('*')
            ->from('missions')
            ->where('id=:id', [':id'=>$this->id])
            ->queryRow();
        foreach ($res as $k => $v) {
            $this->$k = $v;
        }
        $this->req_energy += $this->req_energy_expansion;

        $this->routine = $this->fetchRoutine();
        //$this->chance = Yii::app()->player->model->chanceAgainstMission($this->missionSkill($this->skill));
        $this->skill = $this->missionSkill($this->chance);
        $this->chance = Yii::app()->player->model->chanceAgainstMission($this->skill); //recalculate chance

        $this->req_baits = $this->fetchBaits();
    }

    private function missionSkill($percent) {
        /* skillA = percentP * 100 / skillP */
        $skillM = 0;
        if ($this->skill_extended_at_visit and $percent) {
            $skillA = ($this->skill_extended_at_visit * 100) / $percent;
            $skillM = $skillA - $this->skill_extended_at_visit;
        }
        //echo $this->skill_extended_at_visit. 'SEAV, '.$percent . '%, skillM: ' . $skillM . "\n";
        return $skillM;
    }

    public function complete() {
        //echo "complete\n";

        if (!$this->requirementsOk()) {
            return false;
        }
        
        //echo "requirements are OK\n";

        $this->doMission();
        $this->incrementRoutine();
    }

    private function fetchRoutine() {
        $res = Yii::app()->db->createCommand()
            ->select('routine')
            ->from('users_missions')
            ->where('uid=:uid AND id=:id', [':uid'=>Yii::app()->player->model->uid, ':id'=>(int)$this->id])
            ->queryScalar();
        return (int)$res;
    }

    private function fetchBaits() {
        $baits = [];

        for ($b=1; $b<=2; $b++) {
            $key = 'req_bait_'.$b;
            $keyCount = 'req_bait_'.$b.'_count';

            if ($this->$key and $this->$keyCount) {
                $tmp = [];
                $tmp['required'] = $this->$keyCount;

                $i = new Item();
                $i->id = $this->$key;
                $i->item_type = Item::TYPE_BAIT;
                $i->fetch();
                $tmp['item'] = $i;

                $title = ($i->owned < $tmp['required'] ? $i->owned : $tmp['required']) . '/' . $tmp['required'] .' '. $i->title;
                $tmp['linkTitle'] = $title;
                $tmp['haveEnought'] = $i->owned >= $tmp['required'];
                
                if ($i->title) {
                    //item found in shop, add to requirements
                    $baits[$b] = $tmp;
                }
            }

        }
        return $baits;
    }

    private function requirementsOk() {
        $player = Yii::app()->player->model;
        
        

        //check energy
        $this->_reqPassed['energy'] = ($player->energy >= $this->req_energy);

        //check baits
        foreach ($this->req_baits as $req) {
            $this->_reqPassed['bait_'.$req['item']->id] = $req['haveEnought'];
        }

        foreach ($this->_reqPassed as $passed) {
            if (!$passed) {
                $this->_errors['requirements'] = true;
                return false;
            }
        }
        
        //routine full
        if ($this->routine >= 100) {
            $this->_errors['routineFull'] = true;
            return false;
        }

        return true;
    }

    private function doMission() {
        $incr = $decr = [];

        //take requirements
        $decr['energy'] = $this->req_energy;

        //complete
        $this->_success = $this->beatMission();

        //add awards
        $incr['xp_all'] = $incr['xp_delta'] = $this->gainXP();                
        $incr['dollar'] = $this->gainDollar();
        
        if ($this->_success) {
            if ($this->gate and !$this->gate_visited) $incr['gold'] = 10;
            if ($this->award_setpart) $this->addSetPart();
        }

        Yii::app()->player->model->updateAttributes($incr, $decr);

        //increment contest points
        $contest = new Contest;
        $contest->addPoints(Yii::app()->player->uid, Contest::ACT_MISSION, $decr['energy'], $incr['xp_all'], $incr['dollar']);

        return $this->_success;
    }

    private function addSetPart() {
        $player = Yii::app()->player->model;

        $logger = new Logger;
        $logger->key = 'setitem:'.$player->uid;
        $logger->addToSet('----start: '.date('Y.m.d. H:i:s').'----');
        //echo __FUNCTION__ . "\n";

        $minTimeDiff = 3600*24*1;
        $minXpDiff = 100;
        $findChance = 5; //  Chance % to find something
        /*if ($player->uid == 1981) {
            $minTimeDiff = 10;
            $minXpDiff = 1;
            $findChance = 3; //  "1/findChance" to find something
        }*/
        $logger->addToSet('initialize variables');

        $now = time();
        if ($now - strtotime($player->found_setitem_time) < $minTimeDiff) $findChance = 2; //decrease chance in last 24 hour
        if ($player->xp_all - $player->found_setitem_xp < $minXpDiff) $findChance = 1; //decrease chance in last xp interval

        $rnd = rand(1, 100);
        $logger->addToSet('chance: '. $rnd .'/'.$findChance);
        if ($rnd > $findChance) return false;

        //select rnd setitem
        $items = Yii::app()->db->createCommand()
            ->select('id')
            ->from('parts')
            ->where('level < :minLevel', [':minLevel'=>$player->level+1])
            ->queryAll();

        $rnd = array_rand($items);
        $logger->addToSet('items key: '.$rnd);
        $itemId = isset($items[$rnd]) ? $items[$rnd]['id'] : false;
        $logger->addToSet('itemId: '. $itemId);
        if (!$itemId) return false;

        $i = new Item;
        $i->id = $itemId;
        $i->item_type = Item::TYPE_PART;
        $i->fetch();
        $logger->addToSet('item: '. $i->title);

        //add to inventory
        $i->buy(1);
        $logger->addToSet('errors: '. CJSON::encode($i->errors));
        $logger->addToSet('price: '. $i->price);
        $player->rewriteAttributes([
            'found_setitem_time'=>date("Y-m-d H:i:s", $now),
            'found_setitem_xp'=>$player->xp_all,
            ]);
        $this->found_setpart = $i;
        $logger->addToSet('item bought');

        //log found part
        Yii::app()->gameLogger->log(['type'=>'setpart', 'found_setpart'=>$i->title]);
        $logger->addToSet('---- end: '.date('Y.m.d. H:i:s').'----');

        //stat
        $logger->uid = $player->uid;
        $logger->level = $player->level;
        $found = $logger->increment('found_part', 1);
        if ($found >= 3) Yii::app()->badge->model->trigger('setpart_3', ['cnt'=>$found]);
        if ($found >= 10) Yii::app()->badge->model->trigger('setpart_10', ['cnt'=>$found]);
        if ($found >= 30) Yii::app()->badge->model->trigger('setpart_30', ['cnt'=>$found]);
    }

    private function beatMission() {
        $random = rand(1,100);
        //echo "rnd: $random\n";
        $success = ($random <= $this->chance); //win
        $this->_errors['inexperienced'] = !$success;

        //log mission counter
        $cell = 'mission_' . ($this->gate ? 'gate_' : '') . ($success ? 'success' : 'fail');
        //todo:delete Yii::app()->gameLogger->logCounter($cell);

        $logger = new Logger;
        $logger->uid = Yii::app()->player->model->uid;
        $logger->level = Yii::app()->player->model->level;
        $logger->increment($cell, 1);

        return $success;
    }

    private function incrementRoutine() {
        if ($this->gate) return false; //do not increment for gate missions
        if (!$this->_success) return false; // do not increment on failed missions

        $uid = Yii::app()->player->model->uid;
        $routine = (int)$this->routine_gain - $this->routine_reduction;
        if ($routine<1) $routine = 1;
        
        if ($this->routine >= 100) {
            $this->routine_gain = 0;
            return false;
        }

        $update = Yii::app()->db
            ->createCommand("UPDATE users_missions SET routine=routine+:routine WHERE uid=:uid AND id=:id")
            ->bindValues([':uid'=>$uid, 'id'=>(int)$this->id, ':routine'=>$routine])
            ->execute();

        if (!$update) {
            Yii::app()->db->createCommand()
                ->insert('users_missions', [
                'uid'=>$uid,
                'id'=>(int)$this->id,
                'water_id'=>(int)$this->water_id,
                'routine'=>$routine
                ]);
        }
        $this->routine += $routine;
        $this->gained_routine = $routine;
        Yii::app()->badge->model->trigger('routine_100', ['routine'=>$this->routine]);
    }

    private function gainXP() {
        $xp = $this->award_xp;
        if (!$this->_success) $xp = round($this->award_xp / 10);
        $this->gained_xp = $xp;

        return $xp;
    }
    private function gainDollar() {
        $dollar = 0;
        if ($this->_success) {
            $dollar = rand($this->award_dollar_min, $this->award_dollar_max);
        }
        $this->gained_dollar = $dollar;

        return $dollar;
    }

    
}
