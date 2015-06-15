<?php
/**
 * @property BadgeActivator $model
 */
class BadgeComponent extends CApplicationComponent
{
    private $model;
    
    public function getModel()
    {
        return $this->model;
    }

    public function init()
    {
        $this->model = new CommonBadgeActivator();
        $this->model->uid = Yii::app()->player->uid;
    }
}
