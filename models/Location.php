<?php
/**
 * @property integer $id
 * @property array $missions
 * @property array $missionTypes
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
    public function incrementRoutine()
    {
        return $this->routine++;
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

        if (!$visited && $id==1) {
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
        
        if ($this->id > 1 && $player->tutorial_mission==6) {
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
