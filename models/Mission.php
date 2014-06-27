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
 * @property integer $skill_extended_at_visit
 * @property integer $req_energy_expansion
 */
class Mission extends CModel
{
    private $id;
    private $action;
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
    private $skill_extended_at_visit;
    private $locationRoutinesFull;
    
    public function attributeNames() {
        return [];
    }

    /* getters */
    public function getId() { return $this->id; }
    public function getAction() { 
        if (!$this->action) {
            $this->action = new MissionAction();
        }
        return $this->action; 
    }
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
    public function getRoutine_reduction() { return $this->routine_reduction; }
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
    public function getLocationRoutinesFull() { return $this->locationRoutinesFull; }

    /* setters */
    public function setId($id) {
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
    public function setLocationRoutinesFull($value) {
        $this->locationRoutinesFull = (bool)$value;
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
        $this->action = new MissionAction();
        $this->action->mission = $this;
        $this->action->complete();
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
}
