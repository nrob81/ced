<?php
class CronController extends CController
{
    public $contentClass;

    protected function beforeAction($action) {
        if (Yii::app()->request->getParam('p','') !== Yii::app()->params['cronPassword']) {
            return false;
        }

        return true;
    }
}
