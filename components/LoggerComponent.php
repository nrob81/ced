<?php
class LoggerComponent extends CApplicationComponent
{
    public function log($data)
    {
        $params = $this->getPlayerParams();
        foreach ($data as $k => $v) {
            $params[$k] = $v;
        }
        
        Yii::app()->db->createCommand()->insert('log', $params);
    }

    public function logCounter($cell, $uid = 0, $level = 0)
    {
        if (!$uid) {
            $player = Yii::app()->player->model;
            $uid = $player->uid;
            $level = $player->level;
        }

        $update = Yii::app()->db
            ->createCommand("UPDATE log_counters SET {$cell}={$cell}+1 WHERE uid=:uid AND level=:level")
            ->bindValues([':uid'=>$uid, ':level'=>$level])
            ->execute();

        if (!$update) {
            Yii::app()->db->createCommand()
                ->insert('log_counters', [
                'uid'=>$uid,
                'level'=>$level,
                $cell=>1,
                ]);           
        }
    }

    private function getPlayerParams()
    {
        $params = ['uid','xp_all','xp_delta','level','energy_max','energy','skill','skill_extended','strength','dollar','gold','owned_items','owned_baits'];

        $ret = [];
        foreach ($params as $param) {
            $ret[$param] = Yii::app()->player->model->$param;
        }

        return $ret;
    }
}
