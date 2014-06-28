<?php
/**
 * @property BadgeActivator $model
 */
class BadgeComponent extends CApplicationComponent
{
    private $_model;
    
    public function getModel() {
        return $this->_model;
    }

    public function init() {
        //echo __FUNCTION__."\n";
        $this->_model = new CommonBadgeActivator();
        $this->_model->uid = Yii::app()->player->uid;
    }    
}
