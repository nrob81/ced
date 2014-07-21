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
     * @param string $id Account id
     * @param string $code Verification code
     */
    public function actionCompleteSignup($id, $code)
    {
        $model = $this->loadModel($id);
        $model->scenario = 'completeSignup';

        if(!$model->verifyCode || $model->username) {
            Yii::app()->user->setFlash('info', 'Már állítottál be magadnak felhasználónevet. Kérlek jelentkezz be.');
            $this->redirect('/');
        }

        if($model->verifyCode !== $code) {
            Yii::app()->user->setFlash('error', 'Az első belépéshez szükséges oldal címe nem érvényes. Pontosan másoltad be az e-mailből?');
            $this->redirect('/');
        }

        if(isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];
            $originalPassword = $model->password;
            $valid = $model->validate();
            
            if ($valid) {
                Yii::import('vendor.*');
                require_once('ircmaxell/password-compat/lib/password.php');
                
                $hash = password_hash($model->password, PASSWORD_BCRYPT);

                if (password_verify($model->password, $hash)) {
                    $model->password = $hash;
                    $model->verifyCode = null;
                    $model->verified = new CDbExpression('NOW()');

                    $transaction = $model->getDbConnection()->beginTransaction();
                    try {
                        $model->save(false);
                        $model->refresh();

                        $this->createPlayer($model);

                        $transaction->commit();

                        Yii::app()->user->setFlash('success', $model->username . ', üdvözöllek a játékban!');
                        Yii::app()->session->open();

                        $model->password = $originalPassword;
                        $model->login();
                        $this->redirect('/site');
                    } catch (Exception $e) {
                        $transaction->rollback();
                        Yii::app()->user->setFlash('error', 'Hiba lépett fel a játékos mentése során.');
                    }
                    $model->password = $originalPassword;
                    
                } else {
                    Yii::app()->user->setFlash('error', 'Hiba lépett fel a jelszó titkosítása során.');
                }
            }
        }

        $this->render('complete-signup', ['model' => $model]);
    }

    private function createPlayer($model)
    {
        $command = Yii::app()->db->createCommand();
        $command->insert('main', [
            'uid'=>$model->id,
            'user'=>$model->username,
            ]);

        $command->insert('users_items', [
            'uid'=>$model->id,
            'item_id'=>1,
            'skill'=>1,
            'item_count'=>1,
            ]);

        $command->insert('users_baits', [
            'uid'=>$model->id,
            'item_id'=>1,
            'skill'=>1,
            'item_count'=>1,
            ]);
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
