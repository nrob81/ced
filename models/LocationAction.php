<?php
/* 
 * @property integer $completedId
 */
class LocationAction extends CModel
{
    private $location;
    private $completedId = 0;
    
    public function attributeNames()
    {
        return [];
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }
    
    public function getCompletedId()
    {
        return $this->completedId;
    }

    private function getReduction()
    {
        $reduction = 0;

        if ($this->location->routine >= 3) {
            $reduction = 1; // silver
        }

        if ($this->location->routine >= 9) {
            $reduction = 2; // gold
        }

        if ($this->location->routine >= 27) {
            $reduction = 3; // emerald
        }

        if ($this->location->routine >= 81) {
            $reduction = 4; // diamant
        }

        if ($this->location->routine >= 243) {
            $reduction = 5; // 3 diamants
        }

        return $reduction;
    }

    public function completeMission($id)
    {
        if (!isset($this->location->missions[$id])) {
            return false;
        }

        //max routine
        if ($this->location->routine >= 243) {
            Yii::app()->user->setFlash('info', 'Ezen a helyszínen már elérted a legnagyobb helyszínrutint, így nem teljesítheted a megbízást.');
            return false; //max routine reached
        }

        $this->completedId = $id;
        $m = $this->location->missions[$id];
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
        foreach ($this->location->missions as $mission) {
            if (!$mission->gate && $mission->routine < 100) {
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
            ->bindValues([':uid'=>$uid, ':water_id'=>$this->location->id])
            ->execute();

        //reset all missions routine on this location
        Yii::app()->db
            ->createCommand("UPDATE users_missions SET routine=0 WHERE uid=:uid AND water_id=:water_id")
            ->bindValues([':uid'=>$uid, ':water_id'=>$this->location->id])
            ->execute();

        //refresh objects
        foreach ($this->location->missions as $mission) {
            $mission->routine = 0;
        }
        $this->location->incrementRoutine();
        Yii::app()->badge->model->triggerLocationRoutine($this->location->id, $this->location->routine);

        //add routine awards
        $this->addAwardForRoutine();
    }
    
    private function addAwardForRoutine()
    {
        if ($this->location->routine == 9) { //gold
            $this->addAward(1, 30, 'az arany');
        }

        if ($this->location->routine == 81) { //diamant
            $this->addAward(1, 100, 'a gyémánt');
        }
    }
    
    private function addAward($sp, $gold, $title)
    {
        $player = Yii::app()->player->model;

        $logger = new Logger;
        $logger->key = 'routineAward:'.$player->uid;
        $logger->addToSet('----start: '.date('Y.m.d. H:i:s').'----');
        $logger->addToSet('id: ' . $this->location->id . ', routine: ' . $this->location->routine);
        $logger->addToSet('before: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');

        $player->updateAttributes(['status_points'=>$sp, 'gold'=>$gold], []);
        Yii::app()->user->setFlash('info', "Gratulálok, elérted <strong> {$title} </strong> helyszínrutint!<br/>Jutalmad: {$sp} státuszpont és {$gold} arany.");

        $logger->addToSet('after: ' . $player->status_points . 'sp, ' . $player->gold . 'gold');
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
}
