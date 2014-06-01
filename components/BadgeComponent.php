<?php
class BadgeComponent extends CApplicationComponent
{
    private $_model;

    public function init() {
        //echo __FUNCTION__."\n";
        $this->_model = new BadgeActivator();
        $this->_model->uid = Yii::app()->player->uid;
    }

    public function getModel() {
        return $this->_model;
    }
}

