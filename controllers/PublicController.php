<?php
class PublicController extends Controller
{
    public function actionIndex()
    {
        Yii::app()->session->open();
        if (isset(Yii::app()->session['uid'])) {
            $this->redirect(['/site']);
        }

        $model=new Account('login');
	
		// collect user input data
		if(isset($_POST['Account']))
		{
			$model->attributes=$_POST['Account'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login()) {
                $this->redirect('/site');
            }
		}
		// display the login form
		$this->render('/account/login', ['model'=>$model]);   
    }
}
