<?php
/**
 * @property integer $id
 * @property array $missions
 * @property array $missionTypes
 * @property integer $completedId
 * @property string $name
 * @property string $county
 * @property integer $routine
 * @property array $routineStars
 * @property string $routineImages
 * @property array $navigationLinks
 */
class Location extends CModel
{
    private $_id;
    private $_routine;
    private $_skill_extended_at_visit;

    private $_county = ['', 'Baranya', 'Bács-Kiskun', 'Jász-Nagykun-Szolnok', 'Csongrád', 'Békés', 'Hajdú-Bihar', 'Szabolcs-Szatmár-Bereg', 'Borsod-Abaúj-Zemplén', 'Heves', 'Nógrád', 'Pest', 'Komárom-Esztergom', 'Győr-Moson-Sopron', 'Fejér', 'Veszprém', 'Vas', 'Zala', 'Somogy', 'Tolna'];
    private $_missions = [];
    private $_missionTypes = ['simple'=>[], 'gate'=>[]];
    private $_completedId = 0;
    private $_visitedGates = [];

    public function attributeNames() {
        return [];
    }

    public function setId($id) {
        $this->_id = (int)$id;
    }

    public function getId() {
        return $this->_id;
    }

    public function getMissions() {
        return $this->_missions;
    }

    public function getMissionTypes() {
        return $this->_missionTypes;
    }

    public function getCompletedId() {
        return $this->_completedId;
    }

    public function getName($id = 0) {
        //echo __FUNCTION__ . "\n";
        if (!$id) $id = $this->_id;
        $res = $this->fetchWater($id);
        return $res['title'];
    }

    public function getCounty($id = 0) {
        //echo __FUNCTION__ . "\n";
        if (!$id) $id = $this->_id;
        $res = $this->fetchWater($id);
        $countyId = $res['county_id'];

        $county = @$this->_county[$countyId];
        if (!$county) $county = '?';

        return $county;
    }

    public function getRoutine() {
        //echo __FUNCTION__ . "\n";
        return $this->_routine;
    }
    public function getRoutineStars($r = 0) {
        if (!$r) $r = $this->_routine;

        $d = floor($r / 81);

        $eRem = $r % 81;
        $e = floor($eRem / 27);

        $gRem = $r % 27;
        $g = floor($gRem / 9);

        $sRem = $r % 9;
        $s = floor($sRem / 3);

        $bRem = $r % 3;
        $b = $bRem;

        $ret = ['routine'=>$r, 'diamant'=>$d, 'emerald'=>$e, 'gold'=>$g, 'silver'=>$s, 'bronze'=>$b];
        return $ret;
    }
    public function getRoutineImages($routine) {
        $txt = '';
        foreach (['diamant', 'emerald', 'gold', 'silver', 'bronze'] as $star) {
            for ($i=0; $i<$routine[$star]; $i++) {
                $txt .= '<span class="spr star-'.$star[0].'"></span>';
            }
        }
        return $txt;
    }
    
    public function getNavigationLinks() {
        $nav = [];
        //previous locations
        $res = $this->fetchWater($this->_id);

        if ($res['from']) {
            $navId = (int)$res['from'];
            $link = [
                'id' => $navId,
                'type' => 'prev',
                'title' => $this->getName($navId),
                'active' => true,
                ];
            $nav[] = $link;
        }
        if ($res['from2']) {
            $navId = (int)$res['from2'];
            $link = [
                'id' => $navId,
                'type' => 'prev',
                'title' => $this->getName($navId),
                'active' => true,
                ];
            $nav[] = $link;
        }

        //next locations
        foreach ($this->missionTypes['gate'] as $missionId) {
            $nextId = (int)$this->missions[$missionId]->gate;
            $visited = $this->isVisited($nextId);
            $this->_visitedGates[$nextId] = $visited;

            $link = [
                'id' => $nextId,
                'type' => 'next',
                'title' => $this->getName($nextId),
                'active' => $this->_visitedGates[$nextId],
                ];
            $nav[] = $link;
        }

        return $nav;
    }

    public function isVisited($id = 0) {
        //echo __FUNCTION__ . "\n";
        if (!$id) $id = $this->_id;
        $uid = Yii::app()->player->model->uid;

        $visited = Yii::app()->db->createCommand()
            ->select('*')
            ->from('visited')
            ->where('uid=:uid AND water_id=:id', [':uid'=>$uid, ':id'=>$id])
            ->queryScalar();

        if (!$visited and $id==1) {
            //visit 1. location
            Yii::app()->db->createCommand()
                ->insert('visited', [
                'uid'=>$uid,
                'water_id'=>$id,
                ]);
            $visited = true;
            Yii::app()->gameLogger->log(['type'=>'travel', 'traveled_to'=>$id]);
        }

        return $visited ? true : false;
    }

    public function setActive() {
        $player = Yii::app()->player->model;
        if ($player->last_location == $this->id) return false;

        $attr = ['last_location'=>$this->id];
        
        if ($this->id > 1 and $player->tutorial_mission==6) {
            $attr['tutorial_mission'] = 7;
        }
        
        $player->rewriteAttributes($attr);
        Yii::app()->badge->model->triggerTravel($this->id);
        return true;
    }

    public function fetchMissions() {
        //echo __FUNCTION__ . "\n";
        $res = Yii::app()->db->createCommand()
            ->select('id')
            ->from('missions')
            ->where('water_id=:id', [':id'=>$this->_id])
            ->order('id ASC')
            ->queryAll();

        $this->fetchSkill_extended_at_visit();

        foreach ($res as $mission) {
            $m = new Mission();
            $m->id = $mission['id'];
            $m->skill_extended_at_visit = $this->_skill_extended_at_visit;
            $m->req_energy_expansion = $this->getEnergyExpansion();
            $m->fetch();
            if ($m->gate) {
                $m->gate_name = $this->getName($m->gate);
                $m->gate_visited = $this->isVisited($m->gate);
                $this->_visitedGates[$m->gate] = $m->gate_visited;
            }

            $this->_missions[$mission['id']] = $m;
            $key = $m->gate ? 'gate' : 'simple';
            $this->_missionTypes[$key][] = $mission['id'];
        }
    }    
    public function completeMission($id) {
        //echo __FUNCTION__ . "\n";
        if (!isset($this->missions[$id])) {
            //todo log missing mission
            return false;
        }

        //max routine
        if ($this->routine >= 243) {
            Yii::app()->user->setFlash('info', 'Ezen a helyszínen már elérted a legnagyobb helyszínrutint, így nem teljesítheted a főmegbízást.');
            return false; //max routine reached
        }

        $this->_completedId = $id;
        $m = $this->missions[$id];
        if ($m->gate) {
            $m->addReqPassed('routinesFull', $this->allMissionRoutinesAreFull());
        }

        $m->routine_reduction = $this->getReduction();
        $m->complete();
        if ($m->gate && $m->success) {
            $this->incrementRoutine();
            $this->visitNewLocation($m);
        }
    }

    private function allMissionRoutinesAreFull() {
        //echo __FUNCTION__ . "\n";
        foreach ($this->missions as $mission) {
            if (!$mission->gate and $mission->routine < 100) return false;
        }
        return true;
    }
    private function incrementRoutine() {
        //echo __FUNCTION__ . "\n";
        $uid = Yii::app()->player->model->uid;

        //increment location routine
        Yii::app()->db
            ->createCommand("UPDATE visited SET routine=routine+1 WHERE uid=:uid AND water_id=:water_id")
            ->bindValues([':uid'=>$uid, ':water_id'=>$this->_id])
            ->execute();

        //reset all missions routine on this location
        Yii::app()->db
            ->createCommand("UPDATE users_missions SET routine=0 WHERE uid=:uid AND water_id=:water_id")
            ->bindValues([':uid'=>$uid, ':water_id'=>$this->_id])
            ->execute();

        //refresh objects
        foreach ($this->missions as $mission) {
            $mission->routine = 0;
        }
        $this->_routine++;
        Yii::app()->badge->model->triggerLocationRoutine($this->_id, $this->_routine);

        //add routine awards
        $this->addAwardForRoutine();
    }
    private function addAwardForRoutine() {
        $player = Yii::app()->player->model;

        if ($this->_routine == 9) { //gold
            $logger = new Logger;
            $logger->key = 'routineAward:'.$player->uid;
            $logger->addToSet('----start: '.date('Y.m.d. H:i:s').'----');
            $logger->addToSet('id: ' . $this->_id . ', routine: ' . $this->_routine);
            $logger->addToSet('before: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');

            $player->updateAttributes(['status_points'=>1, 'gold'=>30], []);
            Yii::app()->user->setFlash('info', 'Gratulálok, elérted az <strong> arany </strong> helyszínrutint!<br/>Jutalmad: 1 státuszpont és 30 arany.');

            $logger->addToSet('after: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');
        }

        if ($this->_routine == 81) { //diamant
            $logger = new Logger;
            $logger->key = 'routineAward:'.$player->uid;
            $logger->addToSet('----start: '.date('Y.m.d. H:i:s').'----');
            $logger->addToSet('id: ' . $this->_id . ', routine: ' . $this->_routine);
            $logger->addToSet('before: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');

            Yii::app()->player->model->updateAttributes(['status_points'=>1, 'gold'=>100], []);
            Yii::app()->user->setFlash('info', 'Gratulálok, elérted a <strong> gyémánt </strong> helyszínrutint!<br/>Jutalmad: 1 státuszpont és 100 arany.');

            $logger->addToSet('after: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');
        }
    }

    private function visitNewLocation($mission) {
        if ($mission->gate_visited) return false; //do not open the same location twice

        //echo __FUNCTION__ . "\n";
        $player = Yii::app()->player->model;
        $gate = (int)$mission->gate;

        $location = Yii::app()->db->createCommand()
            ->select('uid, water_id')
            ->from('visited')
            ->where('uid=:uid AND water_id=:water_id', [':uid'=>$player->uid, ':water_id'=>$gate])
            ->queryScalar();

        if (!$location) {
            Yii::app()->db->createCommand()
                ->insert('visited', [
                'uid'=>$player->uid,
                'water_id'=>$gate,
                'skill_extended_at_visit'=>(int)$player->skill_extended,
                ]);
        }
        $mission->gained_visit = true;
        Yii::app()->gameLogger->log(['type'=>'travel', 'traveled_to'=>$gate]);

        Yii::app()->badge->model->triggerTravel($gate);
    }
    
    private function getReduction() {
        $reduction = 0;
        if ($this->_routine >= 3) $reduction = 1; // silver
        if ($this->_routine >= 9) $reduction = 2; // gold
        if ($this->_routine >= 27) $reduction = 3; // emerald
        if ($this->_routine >= 81) $reduction = 4; // diamant 
        if ($this->_routine >= 243) $reduction = 5; // 3 diamants
        return $reduction;
    }
    private function getEnergyExpansion() {
        $exp = 0;
        if ($this->_routine >= 27) $exp = 1; // gold
        if ($this->_routine >= 243) $exp = 2; // diamant
        return $exp;
    }

    public function fetchRoutine() {
        //echo __FUNCTION__ . "\n";
        $res = Yii::app()->db->createCommand()
            ->select('routine')
            ->from('visited')
            ->where('uid=:uid AND water_id=:water_id', [':uid'=>Yii::app()->player->model->uid, ':water_id'=>$this->_id])
            ->queryScalar();
        $this->_routine = (int)$res;
    }
    public function fetchSkill_extended_at_visit() {
        //echo __FUNCTION__ . "\n";
        $dependency = new CExpressionDependency('Yii::app()->params["visited_version"]');        
        $res = Yii::app()->db->cache(Yii::app()->params['cacheDuration'], $dependency)->createCommand()
            ->select('skill_extended_at_visit')
            ->from('visited')
            ->where('uid=:uid AND water_id=:water_id', [':uid'=>Yii::app()->player->model->uid, ':water_id'=>$this->_id])
            ->queryScalar();
        $this->_skill_extended_at_visit = (int)$res;
    }

    

    public function listVisited() {
        $res = Yii::app()->db->createCommand()
            ->select('water_id, routine')
            ->from('visited')
            ->where('uid=:uid', [':uid'=>Yii::app()->player->model->uid])
            ->queryAll();
        $visited = [];
        foreach ($res as $l) {
            $water = $this->fetchWater($l['water_id']);
            if ($l['water_id'] == Yii::app()->player->model->last_location) $water['last']=1;
            $water['routine'] = $l['routine'];
            $visited[$l['water_id']] = $water;
        }
        return $visited;
    }

    private function fetchWater($id) {
        //echo __FUNCTION__ . "\n";
        $dependency = new CExpressionDependency('Yii::app()->params["waters_version"]');        
        $res = Yii::app()->db->cache(Yii::app()->params['cacheDuration'], $dependency)->createCommand()
            ->select('*')
            ->from('waters')
            ->where('id=:id', [':id'=>(int)$id])
            ->queryRow();
        return $res;
    }
}
