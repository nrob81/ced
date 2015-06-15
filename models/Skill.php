<?php
class Skill extends CModel
{
    public function attributeNames()
    {
        return [];
    }

    public function updateExtended()
    {
        //calculate
        $limitItems = $this->minOwnedCount();
        $sumSkill = Yii::app()->player->model->skill;

        //get items limited
        $sumSkill += $this->sumSkill($limitItems, false);

        //get baits limited
        $sumSkill += $this->sumSkill($limitItems, true);
        if ($sumSkill < 1) {
            $sumSkill = 1; //lowest value for player skill.
        }

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
            $res = Yii::app()->db->createCommand()
                ->select('item_id, item_count, skill')
                ->from($table)
                ->where('uid=:uid', [':uid'=>Yii::app()->player->uid])
                ->order('skill DESC')
                ->offset($offset)
                ->limit($limit)
                ->queryAll();
            foreach ($res as $item) {

                $toAdd = $limitItems - $countItem;
                if ($toAdd > $item['item_count']) {
                    $toAdd = $item['item_count'];
                }

                $countItem += $toAdd;

                if ($toAdd) {
                    $skill += $toAdd * $item['skill'];
                }

                $doLoop = $countItem < $limitItems;

                if (!$doLoop) {
                    break;
                }
            }
            $loop++;
        } while ($doLoop);

        return $skill;
    }
}
