<?php
/**
 * @property integer $uid
 * @property integer $dollarImprovement
 * @property integer $skillImprovement
 */
class Advancement extends CModel
{
    private $uid;
    
    public function attributeNames()
    {
        return [];
    }

    // getters
    public function getUid()
    {
        return (int)$this->uid;
    }
    
    public function getDollarImprovement()
    {
        return 30 + (5 * Yii::app()->player->model->level);
    }

    public function getSkillImprovement()
    {
        $di = $this->dollarImprovement;

        //strongest bait
        $bait = Yii::app()->db->createCommand()
            ->select('id, skill, price')
            ->from('baits')
            ->where('level<=:level', [':level'=>(int)Yii::app()->player->model->level])
            ->order('level DESC')
            ->limit(1)
            ->queryRow();
        $skill = round($di / $bait['price'] * $bait['skill'] / 2 * 0.8);
        return (int)$skill;
    }

    public function setUid($uid)
    {
        $this->uid = (int)$uid;
    }
        
    public function incrementForStatuspoint($id)
    {
        $player = Yii::app()->player->model;
        if (!$player->itsMe()) {
            return false;
        }
        if ($player->status_points < 1) {
            return false;
        }

        $mapIdAttribute = [
            1=>['energy_max'=>1, 'energy'=>1],
            ['skill'=>2, 'skill_extended'=>2],
            ['strength'=>2],
            ['dollar'=>$this->dollarImprovement]
            ];
        $mapIdAttribute[2]['skill'] = $mapIdAttribute[2]['skill_extended'] = $this->skillImprovement;

        $increment = isset($mapIdAttribute[$id]) ? $mapIdAttribute[$id] : false;

        if ($increment) {
            $player->updateAttributes($increment, ['status_points'=>1]);
            //badge
            $b = Yii::app()->badge->model;
            if ($id==1) {
                $b->triggerMaxNrg($player->energy_max);
            }
            if ($id==2) {
                $b->triggerSkill($player->skill);
            }
            if ($id==3) {
                $b->triggerStrength($player->strength);
            }
        }
        return true;
    }
}
