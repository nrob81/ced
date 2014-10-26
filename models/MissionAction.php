<?php
/**
 * @property array $reqPassed
 * @property boolean $success
 * @property integer $gained_xp
 * @property integer $gained_dollar
 * @property integer $gained_routine
 * @property boolean $gained_visit
 * @property Item $found_setpart
 */
class MissionAction extends CModel
{
    private $mission;
    private $reqPassed = [];
    private $success;
    private $gained_xp;
    private $gained_dollar;
    private $gained_routine;
    private $gained_visit;
    private $found_setpart;

    public function getReqPassed()
    {
        return $this->reqPassed;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function getGained_xp()
    {
        return (int)$this->gained_xp;
    }

    public function getGained_dollar()
    {
        return (int)$this->gained_dollar;
    }

    public function getGained_routine()
    {
        return (int)$this->gained_routine;
    }

    public function getGained_visit()
    {
        return (bool)$this->gained_visit;
    }

    public function getFound_setpart()
    {
        return $this->found_setpart;
    }

    public function setMission($mission)
    {
        $this->mission = $mission;
    }

    public function setGained_visit($visit)
    {
        $this->gained_visit = (bool)$visit;
        $this->mission->gate_visited = (bool)$visit;
    }

    public function attributeNames()
    {
        return [];
    }
    
    public function complete()
    {
        //echo "complete\n";

        if (!$this->requirementsOk()) {
            return false;
        }
        
        //echo "requirements are OK\n";

        if (!$this->doMission()) {
            throw new CFlashException('A követelményeknek megfelelsz, mégsem sikerül teljesíteni a megbízást mivel csak '. $this->mission->chance .'% esélyed volt rá.<br/>
                Nagyobb szakértelemmel (több felszereléssel és csalival) ez növelhető.');
        }
        $this->incrementRoutine();
    }
    
    private function requirementsOk()
    {
        $player = Yii::app()->player->model;
        
        //check if the mission is gate and the submissions are maxed out
        if ($this->mission->gate) {
            $this->reqPassed['routinesFull'] = $this->mission->locationRoutinesFull;
        }

        //check energy
        $this->reqPassed['energy'] = ($player->energy >= $this->mission->req_energy);

        //check baits
        foreach ($this->mission->req_baits as $req) {
            $this->reqPassed['bait_'.$req['item']->id] = $req['haveEnought'];
        }

        foreach ($this->reqPassed as $passed) {
            if (!$passed) {
                throw new CFlashException('Nem tudod elvégezni a megbízást, mert nem teljesíted a követelményeket.');
            }
        }
        
        //routine full
        if ($this->mission->routine >= 100) {
            throw new CFlashException('Ezt a megbízást már 100% rutinnal végzed, ezért unalmas lenne ismételgetni.');
        }

        return true;
    }
    
    private function doMission()
    {
        $incr = $decr = [];

        //take requirements
        $decr['energy'] = $this->mission->req_energy;

        //complete
        $this->success = $this->beatMission();

        //add awards
        $incr['xp_all'] = $incr['xp_delta'] = $this->gainXP();
        $incr['dollar'] = $this->gainDollar();
        
        if ($this->success) {
            if ($this->mission->gate && !$this->mission->gate_visited) {
                $incr['gold'] = 10;
            }
            if ($this->mission->award_setpart) {
                $this->addSetPart();
            }
        }

        Yii::app()->player->model->updateAttributes($incr, $decr);

        //increment contest points
        $contest = new Contest;
        $contest->addPoints(Yii::app()->player->uid, Contest::ACT_MISSION, $decr['energy'], $incr['xp_all'], $incr['dollar']);

        return $this->success;
    }

    private function incrementRoutine()
    {
        if ($this->mission->gate) {
            return false; //do not increment for gate missions
        }
        if (!$this->success) {
            return false; // do not increment on failed missions
        }

        $uid = Yii::app()->player->model->uid;
        $routine = (int)$this->mission->routine_gain;// - $this->mission->routine_reduction;
        if ($routine<1) {
            $routine = 1;
        }
        
        if ($this->mission->routine >= 100) {
            $this->mission->routine_gain = 0;
            return false;
        }

        $update = Yii::app()->db
            ->createCommand("UPDATE users_missions SET routine=routine+:routine WHERE uid=:uid AND id=:id")
            ->bindValues([':uid'=>$uid, 'id'=>(int)$this->mission->id, ':routine'=>$routine])
            ->execute();

        if (!$update) {
            Yii::app()->db->createCommand()
                ->insert('users_missions', [
                'uid'=>$uid,
                'id'=>(int)$this->mission->id,
                'water_id'=>(int)$this->mission->water_id,
                'routine'=>$routine
                ]);
        }
        $this->mission->routine += $routine;
        $this->gained_routine = $routine;
        Yii::app()->badge->model->triggerRoutine($this->mission->routine);
    }

    private function beatMission()
    {
        $random = rand(1, 100);
        //echo "rnd: $random\n";
        $success = ($random <= $this->mission->chance); //win

        //log mission counter
        $cell = 'mission_' . ($this->mission->gate ? 'gate_' : '') . ($success ? 'success' : 'fail');
        //todo:delete Yii::app()->gameLogger->logCounter($cell);

        $logger = new Logger;
        $logger->uid = Yii::app()->player->model->uid;
        $logger->level = Yii::app()->player->model->level;
        $logger->increment($cell, 1);

        return $success;
    }
    
    private function gainXP()
    {
        $xp = $this->mission->award_xp;
        if (!$this->success) {
            $xp = round($this->mission->award_xp / 10);
        }
        $this->gained_xp = $xp;

        return $xp;
    }

    private function gainDollar()
    {
        $dollar = 0;
        if ($this->success) {
            $dollar = rand($this->mission->award_dollar_min, $this->mission->award_dollar_max);
        }
        $this->gained_dollar = $dollar;

        return $dollar;
    }
    
    private function addSetPart()
    {
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
        if ($now - strtotime($player->found_setitem_time) < $minTimeDiff) {
            $findChance = 2; //decrease chance in last 24 hour
        }
        if ($player->xp_all - $player->found_setitem_xp < $minXpDiff) {
            $findChance = 1; //decrease chance in last xp interval
        }

        $rnd = rand(1, 100);
        $logger->addToSet('chance: '. $rnd .'/'.$findChance);
        if ($rnd > $findChance) {
            return false;
        }

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
        if (!$itemId) {
            return false;
        }

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
        Yii::app()->badge->model->triggerSetPart($found);
    }
}
