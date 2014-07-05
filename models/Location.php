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
    private $id;
    private $routine;
    private $skill_extended_at_visit;

    private $county = ['', 'Baranya', 'Bács-Kiskun', 'Jász-Nagykun-Szolnok', 'Csongrád', 'Békés', 'Hajdú-Bihar', 'Szabolcs-Szatmár-Bereg', 'Borsod-Abaúj-Zemplén', 'Heves', 'Nógrád', 'Pest', 'Komárom-Esztergom', 'Győr-Moson-Sopron', 'Fejér', 'Veszprém', 'Vas', 'Zala', 'Somogy', 'Tolna'];
    private $missions = [];
    private $missionTypes = ['simple'=>[], 'gate'=>[]];
    private $completedId = 0;
    private $visitedGates = [];

    public function attributeNames()
    {
        return [];
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMissions()
    {
        return $this->missions;
    }

    public function getMissionTypes()
    {
        return $this->missionTypes;
    }

    public function getCompletedId()
    {
        return $this->completedId;
    }

    public function getName($id = 0)
    {
        if (!$id) {
            $id = $this->id;
        }

        $res = $this->fetchWater($id);
        return $res['title'];
    }

    public function getCounty($id = 0)
    {
        if (!$id) {
            $id = $this->id;
        }

        $res = $this->fetchWater($id);
        $countyId = $res['county_id'];

        $county = @$this->county[$countyId];
        if (!$county) {
            $county = '?';
        }

        return $county;
    }

    public function getRoutine()
    {
        return $this->routine;
    }

    public function getRoutineStars($r = 0)
    {
        if (!$r) {
            $r = $this->routine;
        }

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

    public function getRoutineImages($routine)
    {
        $txt = '';
        foreach (['diamant', 'emerald', 'gold', 'silver', 'bronze'] as $star) {
            for ($i=0; $i<$routine[$star]; $i++) {
                $txt .= '<span class="spr star-'.$star[0].'"></span>';
            }
        }
        return $txt;
    }
    
    public function getNavigationLinks()
    {
        $nav = [];
        //previous locations
        $res = $this->fetchWater($this->id);

        foreach (['from', 'from2'] as $id) {
            if ($res[$id]) {
                $navId = (int)$res[$id];
                $link = [
                    'id' => $navId,
                    'type' => 'prev',
                    'title' => $this->getName($navId),
                    'active' => true,
                    ];
                $nav[] = $link;
            }
        }

        //next locations
        foreach ($this->missionTypes['gate'] as $missionId) {
            $nextId = (int)$this->missions[$missionId]->gate;
            $visited = $this->isVisited($nextId);
            $this->visitedGates[$nextId] = $visited;

            $link = [
                'id' => $nextId,
                'type' => 'next',
                'title' => $this->getName($nextId),
                'active' => $this->visitedGates[$nextId],
                ];
            $nav[] = $link;
        }

        return $nav;
    }

    public function isVisited($id = 0)
    {
        if (!$id) {
            $id = $this->id;
        }

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

    public function setActive()
    {
        $player = Yii::app()->player->model;
        if ($player->last_location == $this->id) {
            return false;
        }

        $attr = ['last_location'=>$this->id];
        
        if ($this->id > 1 and $player->tutorial_mission==6) {
            $attr['tutorial_mission'] = 7;
        }
        
        $player->rewriteAttributes($attr);
        Yii::app()->badge->model->triggerTravel($this->id);
        return true;
    }

    public function fetchMissions()
    {
        $res = Yii::app()->db->createCommand()
            ->select('id')
            ->from('missions')
            ->where('water_id=:id', [':id'=>$this->id])
            ->order('id ASC')
            ->queryAll();

        $this->fetchSkill_extended_at_visit();

        foreach ($res as $mission) {
            $m = new Mission();
            $m->id = $mission['id'];
            $m->skill_extended_at_visit = $this->skill_extended_at_visit;
            $m->req_energy_expansion = $this->getEnergyExpansion();
            $m->fetch();
            if ($m->gate) {
                $m->gate_name = $this->getName($m->gate);
                $m->gate_visited = $this->isVisited($m->gate);
                $this->visitedGates[$m->gate] = $m->gate_visited;
            }

            $this->missions[$mission['id']] = $m;
            $key = $m->gate ? 'gate' : 'simple';
            $this->missionTypes[$key][] = $mission['id'];
        }
    }

    public function completeMission($id)
    {
        if (!isset($this->missions[$id])) {
            return false;
        }

        //max routine
        if ($this->routine >= 243) {
            Yii::app()->user->setFlash('info', 'Ezen a helyszínen már elérted a legnagyobb helyszínrutint, így nem teljesítheted a megbízást.');
            return false; //max routine reached
        }

        $this->completedId = $id;
        $m = $this->missions[$id];
        if ($m->gate) {
            $m->locationRoutinesFull = $this->allMissionRoutinesAreFull();
        }

        $m->routine_reduction = $this->getReduction();
        $m->complete();
        if ($m->gate && $m->action->success) {
            $this->incrementRoutine();
            $this->visitNewLocation($m);
        }
    }

    private function allMissionRoutinesAreFull()
    {
        foreach ($this->missions as $mission) {
            if (!$mission->gate and $mission->routine < 100) {
                return false;
            }
        }
        return true;
    }

    private function incrementRoutine()
    {
        $uid = Yii::app()->player->model->uid;

        //increment location routine
        Yii::app()->db
            ->createCommand("UPDATE visited SET routine=routine+1 WHERE uid=:uid AND water_id=:water_id")
            ->bindValues([':uid'=>$uid, ':water_id'=>$this->id])
            ->execute();

        //reset all missions routine on this location
        Yii::app()->db
            ->createCommand("UPDATE users_missions SET routine=0 WHERE uid=:uid AND water_id=:water_id")
            ->bindValues([':uid'=>$uid, ':water_id'=>$this->id])
            ->execute();

        //refresh objects
        foreach ($this->missions as $mission) {
            $mission->routine = 0;
        }
        $this->routine++;
        Yii::app()->badge->model->triggerLocationRoutine($this->id, $this->routine);

        //add routine awards
        $this->addAwardForRoutine();
    }

    private function addAwardForRoutine()
    {
        $player = Yii::app()->player->model;

        if ($this->routine == 9) { //gold
            $logger = new Logger;
            $logger->key = 'routineAward:'.$player->uid;
            $logger->addToSet('----start: '.date('Y.m.d. H:i:s').'----');
            $logger->addToSet('id: ' . $this->id . ', routine: ' . $this->routine);
            $logger->addToSet('before: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');

            $player->updateAttributes(['status_points'=>1, 'gold'=>30], []);
            Yii::app()->user->setFlash('info', 'Gratulálok, elérted az <strong> arany </strong> helyszínrutint!<br/>Jutalmad: 1 státuszpont és 30 arany.');

            $logger->addToSet('after: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');
        }

        if ($this->routine == 81) { //diamant
            $logger = new Logger;
            $logger->key = 'routineAward:'.$player->uid;
            $logger->addToSet('----start: '.date('Y.m.d. H:i:s').'----');
            $logger->addToSet('id: ' . $this->id . ', routine: ' . $this->routine);
            $logger->addToSet('before: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');

            Yii::app()->player->model->updateAttributes(['status_points'=>1, 'gold'=>100], []);
            Yii::app()->user->setFlash('info', 'Gratulálok, elérted a <strong> gyémánt </strong> helyszínrutint!<br/>Jutalmad: 1 státuszpont és 100 arany.');

            $logger->addToSet('after: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');
        }
    }

    private function visitNewLocation($mission)
    {
        if ($mission->gate_visited) {
            return false; //do not open the same location twice
        }

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
        $mission->action->gained_visit = true;
        Yii::app()->gameLogger->log(['type'=>'travel', 'traveled_to'=>$gate]);

        Yii::app()->badge->model->triggerTravel($gate);
    }
    
    private function getReduction()
    {
        $reduction = 0;

        if ($this->routine >= 3) {
            $reduction = 1; // silver
        }

        if ($this->routine >= 9) {
            $reduction = 2; // gold
        }

        if ($this->routine >= 27) {
            $reduction = 3; // emerald
        }

        if ($this->routine >= 81) {
            $reduction = 4; // diamant
        }

        if ($this->routine >= 243) {
            $reduction = 5; // 3 diamants
        }

        return $reduction;
    }

    private function getEnergyExpansion()
    {
        $exp = 0;

        if ($this->routine >= 27) {
            $exp = 1; // gold
        }

        if ($this->routine >= 243) {
            $exp = 2; // diamant
        }

        return $exp;
    }

    public function fetchRoutine()
    {
        $res = Yii::app()->db->createCommand()
            ->select('routine')
            ->from('visited')
            ->where('uid=:uid AND water_id=:water_id', [':uid'=>Yii::app()->player->model->uid, ':water_id'=>$this->id])
            ->queryScalar();
        $this->routine = (int)$res;
    }

    public function fetchSkill_extended_at_visit()
    {
        $dependency = new CExpressionDependency('Yii::app()->params["visited_version"]');
        $res = Yii::app()->db->cache(Yii::app()->params['cacheDuration'], $dependency)->createCommand()
            ->select('skill_extended_at_visit')
            ->from('visited')
            ->where('uid=:uid AND water_id=:water_id', [':uid'=>Yii::app()->player->model->uid, ':water_id'=>$this->id])
            ->queryScalar();
        $this->skill_extended_at_visit = (int)$res;
    }

    public function listVisited()
    {
        $res = Yii::app()->db->createCommand()
            ->select('water_id, routine')
            ->from('visited')
            ->where('uid=:uid', [':uid'=>Yii::app()->player->model->uid])
            ->queryAll();
        $visited = [];
        foreach ($res as $l) {
            $water = $this->fetchWater($l['water_id']);
            if ($l['water_id'] == Yii::app()->player->model->last_location) {
                $water['last']=1;
            }

            $water['routine'] = $l['routine'];
            $visited[$l['water_id']] = $water;
        }
        return $visited;
    }

    private function fetchWater($id)
    {
        $dependency = new CExpressionDependency('Yii::app()->params["waters_version"]');
        $res = Yii::app()->db->cache(Yii::app()->params['cacheDuration'], $dependency)->createCommand()
            ->select('*')
            ->from('waters')
            ->where('id=:id', [':id'=>(int)$id])
            ->queryRow();
        return $res;
    }
}
