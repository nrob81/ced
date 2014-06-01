<?php
class AjaxController extends Controller
{
    public function actionTest($id = 0) {
        $ch = new Challenge;
        $ch->id = $id;
        $ch->fetch();

        $res = "<p class='ui-li-desc'>{$ch->point_caller} pont</p><p class='ui-li-desc'>{$ch->cnt_won_caller}x győzött</p><p class='ui-li-desc'>{$ch->loot_caller}$ zsákmány</p>";
        $res .= "|";
        $res .= "<p class='ui-li-desc'>{$ch->point_opponent} pont</p><p class='ui-li-desc'>{$ch->cnt_won_opponent}x győzött</p><p class='ui-li-desc'>{$ch->loot_opponent}$ zsákmány</p>";
        
        echo $res;
    }

    public function _actionTest2($id = 0) {
        $ch = new Challenge;
        $ch->id = $id;
        $ch->fetch();

        $res = [];
        $res['caller'] = [
            'point'=>$ch->point_caller,
            'cnt_won'=>$ch->cnt_won_caller,
            'loot'=>$ch->loot_caller
            ];
        $res['opponent'] = [
            'point'=>$ch->point_opponent,
            'cnt_won'=>$ch->cnt_won_opponent,
            'loot'=>$ch->loot_opponent
            ];

        echo CJSON::encode($res);
    }
}
