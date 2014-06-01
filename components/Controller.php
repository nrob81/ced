<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();
    public $contentClass;

    public function beforeRender($view) {
        $cs = Yii::app()->clientScript;
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
