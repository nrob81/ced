<?php
class AccountController extends Controller
{
	/**
	 * Registers a new account.
	 * If registration is successful, the browser will be redirected to the to the previous page.
	 */
	public function actionSignup()
    {
		$model=new Account('signup');
	
		if(isset($_POST['Account']))
		{
			$model->attributes=$_POST['Account'];
			if($model->validate())
			{
				// Create account
				$model->verifyCode = $model->generateCode();
                $model->save(false);
				
                // Send verification mail
                $mail=Yii::app()->smtpmail;
                $mail->CharSet = 'utf-8';
                $mail->SetFrom('natkay.robert@nrcode.com', 'ced.local'); //todo: activate sender
                $mail->Subject    = "Carp-e Diem regisztráció";
                $message = $this->renderPartial('_verification', ['model'=>$model], true);
                $mail->MsgHTML($message);
                $mail->AddAddress($model->email, "");
                $sent = true; 
                $sent = $mail->Send(); //todo: activate on production
                if(!$sent) {
                    //echo "Mailer Error: " . $mail->ErrorInfo;
				    Yii::app()->user->setFlash('error', 'A regisztráció befejezéséhez szükséges információkat nem sikerült elküldeni. Kérlek próbálkozz később.');
                } else {
                    Yii::app()->user->setFlash('success', 'A regisztráció befejezéséhez szükséges teendőket elküldtük e-mailben.');
                    $this->redirect('/');
                }

            } else {
                foreach ($model->errors as $error) {
				    Yii::app()->user->setFlash('error', $error[0]);
                }
            }
		}
	
		$this->render('signup',array(
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
