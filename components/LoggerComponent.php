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
