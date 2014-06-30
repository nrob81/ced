<?php
class Skill extends CModel
{
    private $usedItems = [];
    private $usedBaits = [];
    
    public function attributeNames()
    {
        return [];
    }

    public function updateExtended()
    {
        //echo __FUNCTION__ . "\n";

        //calculate
        $limitItems = $this->minOwnedCount();
        //echo "limitItems: {$limitItems}\n";
        $sumSkill = Yii::app()->player->model->skill;
        //echo "skill: {$sumSkill}\n";

        //get items limited
        $sumSkill += $this->sumSkill($limitItems, false);
        //echo "sum+itemsSkill: {$sumSkill}\n";

        //get baits limited
        $sumSkill += $this->sumSkill($limitItems, true);
        //echo "sum+baitSkill: {$sumSkill}\n";
        if ($sumSkill < 1) {
            $sumSkill = 1; //lowest value for player skill.
        }
        //echo "sumSkill: {$sumSkill}\n";

        //print_r($this->usedItems);
        //print_r($this->usedBaits);
        Yii::app()->player->model->rewriteAttributes(['skill_extended'=>$sumSkill]);
    }
    
    private function minOwnedCount()
    {
        $smaller = Yii::app()->player->model->owned_items < Yii::app()->player->model->owned_baits ? Yii::app()->player->model->owned_items : Yii::app()->player->model->owned_baits;
        return $smaller;
    }

    /**
     * Reads the max number of items from the database that can be used and sums their skill points.
     */
    private function sumSkill($limitItems, $isBait = false)
    {
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
                ->where('uid=:uid', [':uid'=>Yii::app()->player->uid])
                ->order('skill DESC')
                ->offset($offset)
                ->limit($limit)
                ->queryAll();
            foreach ($res as $item) {
                //echo "item_id: {$item['item_id']}, ";
                //echo "item_count: {$item['item_count']}, ";
                //echo "skill: {$item['skill']} | ";

                $toAdd = $limitItems - $countItem;
                if ($toAdd > $item['item_count']) {
                    $toAdd = $item['item_count'];
                }

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
                if (!$doLoop) {
                    break;
                }
            }
            $loop++;
        } while ($doLoop);

        return $skill;
    }
}
