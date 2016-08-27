<?php
class CronController extends CController
{
    public $contentClass;

    protected function beforeAction($action)
    {
        return (Yii::app()->request->getParam('p', '') === Yii::app()->params['cronPassword']);
    }
}
