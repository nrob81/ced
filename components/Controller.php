<?php
class Controller extends CController
{
	public $layout='//layouts/column1';
	public $menu=array();
	public $breadcrumbs=array();
    public $contentClass;

    public function beforeRender($view) {
        $cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.countdown.min.js', CClientScript::POS_HEAD);
        $cs->registerScriptFile(Yii::app()->request->baseUrl . '/js/fish.js', CClientScript::POS_HEAD);
        $cs->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.mobile-1.3.2.min.js', CClientScript::POS_HEAD);
        $cs->registerScript('vars', '
            var justAdvanced = ' . (Yii::app()->player->model->justAdvanced?'true':'false') . '; 
            var refillTime = ' . Yii::app()->player->model->remainingTimeToRefill . '; 
            var rpi = ' .  Yii::app()->player->model->refillPerInterval . '; '
        , CClientScript::POS_HEAD);

        $cs->registerScript('vars', '
            var _paq = _paq || [];
            _paq.push(["trackPageView"]);
            _paq.push(["enableLinkTracking"]);

            (function() {
                var u=(("https:" == document.location.protocol) ? "https" : "http") + "://nrcode.com/analytics/";
                _paq.push(["setTrackerUrl", u+"piwik.php"]);
                _paq.push(["setSiteId", "1"]);
                var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
                g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
            })();', CClientScript::POS_END);

        //CSS
        $cs->registerCssFile(Yii::app()->request->baseUrl . '/css/themes/brown.css');
        $cs->registerCssFile(Yii::app()->request->baseUrl . '/css/jquery.mobile.structure-1.3.2.min.css');
        $cs->registerCssFile(Yii::app()->request->baseUrl . '/css/game.css');
        
        return true;
    }
    
}
