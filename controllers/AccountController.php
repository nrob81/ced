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
            if($model->validate()) {
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
    public function actionCompleteSignup($id, $code)
    {
        $model = $this->loadModel($id);
        $model->scenario = 'completeSignup';

        if($model->verifyCode && !$model->username) {
            if($model->verifyCode == $code) {
                
                if(isset($_POST['Account'])) {
                    $model->attributes=$_POST['Account'];
                    $valid = $model->validate();
                    
                    if ($valid) {
                        Yii::import('vendor.*');
                        require_once('ircmaxell/password-compat/lib/password.php');
                        
                        $hash = password_hash($model->password, PASSWORD_BCRYPT);

                        if (password_verify($model->password, $hash)) {
                            $model->password = $hash;
                            $model->verifyCode = null;
                            $model->verified = new CDbExpression('NOW()');

                            $model->save(false);
                            Yii::app()->user->setFlash('success', $model->username . ', üdvözöllek a játékban!');
                            $this->redirect('/'); //todo: login
                        } else {
                            /* Invalid */
                            Yii::app()->user->setFlash(502, 'Hiba lépett fel a jelszó titkosítása során.');
                        }
                    }
                }
            } else {
                Yii::app()->user->setFlash('error','Az első belépéshez szükséges oldal címe nem érvényes. Pontosan másoltad be az e-mailből?');
                $this->redirect('/');
            }
        } else {
            Yii::app()->user->setFlash('info','Már állítottál be magadnak felhasználónevet. Kérlek jelentkezz be.');
            $this->redirect('/');
        }

        $this->render('complete-signup', ['model' => $model]);
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
        $model=Account::model()->findByPk((int)$id);
        
        if($model===null) {
            throw new CHttpException(1, 'A keresett játékos nem található. Ezen könnyen segíthetsz: ' . CHtml::link('regisztráld be.', ['signup']));
        }

        return $model;
    }
}
