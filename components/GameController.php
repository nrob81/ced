<?php
class GameController extends Controller
{
    public $pageID;
    public $contentClass;
	public $layout='//layouts/column1';
    private $lastRefresh = 0;

    protected function beforeAction($action) {
        $this->checkCookie();
        if (!Yii::app()->player->uid) throw new CHttpException(403, 'Regisztráció nélkül a játék nem használható.'); //own nick

        $this->updateInternals();
        $this->autoLogout();
        $this->setTimers();

        //init Player component, increase energy
        Yii::app()->player->rest();


        return true;
    }

    private function checkCookie() {
        $missingGameVar = true;
        $enabledCookie = isset(Yii::app()->request->cookies['PHPSESSID']->value);
        if ($enabledCookie) {
            $session = Yii::app()->session;
            $session->open();
            if ($session->get('fish_game') == 1) $missingGameVar = false;             
        }

        if ($missingGameVar) {
            $this->redirect(['gate/cookie']);
        }
    }

    private function updateInternals() {
        $session = Yii::app()->session;
        

        $lastRefresh = Yii::app()->dbWline->createCommand()
            ->select( Yii::app()->params['wlineRefreshAttribute'] )
            ->from( Yii::app()->params['wlineUsersTable'] )
            ->where('uid=' . Yii::app()->player->uid)
            ->queryScalar();

        if (!isset($session['r_time'])) {
            $session['r_time'] = time();
        }

        $this->lastRefresh = $lastRefresh;
    }
    private function autoLogout() {
        $session = Yii::app()->session;

        if (time() - $session['r_time'] > Yii::app()->params['maxtime']) {
            $wid =  @Yii::app()->request->cookies['PHPSESSID']->value;
            $this->redirect(Yii::app()->params['wlineHost'] . "menu.php?wid=$wid#autoLogout");
        }
    }

    private function setTimers() {
        if (Yii::app()->request->getParam('auto', 0) == 1) return false;

        //set r_time
        Yii::app()->session['r_time'] = time();


        if (time() - $this->lastRefresh < 30) return false;
        $res = Yii::app()->dbWline->createCommand()->update(Yii::app()->params['wlineUsersTable'], [
            Yii::app()->params['wlineRefreshAttribute'] => time(),
            ], 'uid=' . Yii::app()->player->uid);

    }
}
