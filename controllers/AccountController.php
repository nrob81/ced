<?php
class AccountController extends Controller
{
	/**
	 * Registers a new account.
	 * If registration is successful, the browser will be redirected to the to the previous page.
	 */
	public function actionRegister()
	{
		$model=new Account('register');
	
		if(isset($_POST['Account']))
		{
			$model->attributes=$_POST['Account'];
			if($model->validate())
			{
				// Create account
				$unhashedPassword=$model->password;
				$model->password=$model->hashPassword($model->password);
				$model->save(false);
				
				// Create verification
				$verification=new Verification;
				$verification->account_id=$model->id;
				$verification->type=Verification::TYPE_REGISTER;
				$verification->code=$verification->generateCode();
				$verification->save(false);
				
				// Send verification mail
				Yii::app()->mailer->sendMIME(
					Yii::app()->name.' <'.Yii::app()->params['adminEmail'].'>',
					$model->email,
					'Registration at '.Yii::app()->name,
					'',
					$this->renderPartial('/verification/register', array(
						'verification'=>$verification,
					), true)
				);
				
				// Login
				$model->password=$unhashedPassword;
				$model->login();
				
				// Redirect
				Yii::app()->user->setFlash('notice','To complete your registration, please check your email');
				$this->redirect(Yii::app()->user->returnUrl);
			}
		}
	
		$this->render('register',array(
			'model'=>$model,
		));
	}
	
	/**
	 * Completes an account registration
	 * @param string $account_id Account id
	 * @param string $code Verification code
	 */
	public function actionCompleteRegister($account_id, $code)
	{
		$verification=Verification::model()->findByPk(array(
			'account_id'=>$account_id,
			'type'=>Verification::TYPE_REGISTER,
		));
		
		if($verification)
		{
			if($verification->validateCode($code))
			{
				$verification->delete();
				
				Yii::app()->user->setFlash('success','Your registration has been completed');
			}
			else
			{
				Yii::app()->user->setFlash('error','Your registration could not be completed');
			}
		}
		else
		{
			Yii::app()->user->setFlash('notice','Your registration is already completed');
		}
		
		$this->redirect(Yii::app()->homeUrl);
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Account the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Account::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
