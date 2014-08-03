<?php
class GateController extends LoginController
{
    public function actionIndex()
    {
        $session = Yii::app()->session;
        $session->open();
        $this->incrementLoginDays(@$_SESSION['uid']);

        $b = new CommonBadgeActivator;
        $b->uid = @$_SESSION['uid'];
        $b->triggerLoginDays();

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
        if ($error=Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest) {
                echo $error['message'];
            } else {
                $this->render('error', $error);
            }
        }
    }

    public function actionBackToMenu()
    {
        $this->redirect(Yii::app()->params['wlineHost'] . 'menu.php#btm');
    }

    public function actionBackToForum()
    {
        $this->redirect(Yii::app()->params['wlineHost'] . 'forum_read.php?id=1865');
    }
}
