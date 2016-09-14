<?php
class Controller extends CController
{
    public $layout='//layouts/column1';
    public $menu=array();
    public $breadcrumbs=array();
    public $contentClass;

    public function beforeRender($view)
    {
        $cs = Yii::app()->getClientScript();

        //js
        $cs->registerCoreScript('jquery');
        $cs->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.countdown.min.js', CClientScript::POS_HEAD);
        $cs->registerScriptFile(Yii::app()->request->baseUrl . '/js/fish.js', CClientScript::POS_HEAD);
        $cs->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.mobile-1.4.5.min.js', CClientScript::POS_HEAD);
        $cs->registerScript(
            'vars',
            'var justAdvanced = ' . (Yii::app()->player->model->justAdvanced?'true':'false') . ';
            var refillTime = ' . Yii::app()->player->model->remainingTimeToRefill . ';
            var rpi = ' .  Yii::app()->player->model->refillPerInterval . '; ',
            CClientScript::POS_HEAD
        );

        //CSS
        $cs->registerCssFile(Yii::app()->request->baseUrl . '/css/themes/brown.css');
        //$cs->registerCssFile(Yii::app()->request->baseUrl . '/css/jquery.mobile.structure-1.3.2.min.css');
        $cs->registerCssFile(Yii::app()->request->baseUrl . '/css/game.css?4');
        return true;
    }
}
