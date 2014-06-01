<?php

class GateController extends Controller
{
	public function actionIndex()
	{
        $session = Yii::app()->session;
        $session->open();
        $this->incrementLoginDays();
        
        $b = new BadgeActivator;
        $b->uid = @$_SESSION['uid'];
        $b->trigger('login_days_7');
        $b->trigger('login_days_30');
        $b->trigger('login_days_60');

		$this->redirect(['check']);
    }
    public function actionCheck()
	{
        $enabledCookie = isset(Yii::app()->request->cookies['PHPSESSID']->value);
        if ($enabledCookie) {
            $this->redirect(Yii::app()->homeUrl);
        }

        $this->render('check');
    }
    
    public function actionCookie()
	{
        $this->render('check');
    }

    public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
    }
    
    public function actionBackToMenu() {
        $this->redirect(Yii::app()->params['wlineHost'] . 'menu.php#btm');
    }
    public function actionBackToForum() {
        $this->redirect(Yii::app()->params['wlineHost'] . 'forum_read.php?id=1865');
    }

    private function incrementLoginDays() {
        $uid = @$_SESSION['uid'];
        if (!$uid) return false;

        $redis = Yii::app()->redis->getClient();
        $key = "counter:login:days:".$uid;
        $yesterday = array(
            'start'	=>	mktime(0,0,0,date('m'),date('d')-1,date('Y')),
            'end'		=>	mktime(0,0,-1,date('m'),date('d'),date('Y')),
        );

        $cnt = 0;
        $last = $redis->hGet($key, 'last');
        if ($last >= $yesterday['start'] && $last <= $yesterday['end']) { //yesterday
            $cnt = $redis->hIncrBy($key, 'cnt', 1);
        } elseif ($last < $yesterday['start']) {	//more than 1 day ago
            $redis->hSet($key, 'cnt', 0);
        }
        $redis->hSet($key, 'last', time());
        return $cnt;
    }
}
