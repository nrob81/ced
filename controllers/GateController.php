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

    public function actionLogout()
    {
        $this->redirect(Yii::app()->params['wlineHost'] . 'menu.php#btm');
    }

    public function actionForum()
    {
        $this->redirect(Yii::app()->params['wlineHost'] . 'forum_read.php?id=1865');
    }
    
    protected function beforeAction($action)
    {
        if (!Yii::app()->params['isPartOfWline']) {
            throw new CHttpException(1, 'Ez az aloldal nem használható. ' . CHtml::link('főoldal', '/')); //own nick
        }

        return true;
    }
}
