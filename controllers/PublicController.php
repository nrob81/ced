<?php
class PublicController extends LoginController
{
    public $layout='//layouts/columnGuest';

    public function actionIndex()
    {
        Yii::app()->session->open();
        if (isset(Yii::app()->session['uid'])) {
            $this->redirect(['/site']);
        }

        $model=new Account('login');

        // collect user input data
        if(isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];
            // validate user input and redirect to the previous page if valid
            if($model->validate() && $model->login()) {
                $this->incrementLoginDays(Yii::app()->session['uid']);

                $b = new CommonBadgeActivator;
                $b->uid = Yii::app()->session['uid'];
                $b->triggerLoginDays();

                $this->redirect('/site');
            }
        }
        // display the login form
        $this->render('/account/login', ['model'=>$model]);   
    }
}
