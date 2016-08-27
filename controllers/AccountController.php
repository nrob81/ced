<?php
class AccountController extends Controller
{
    public $layout='//layouts/columnGuest';

    /**
     * Registers a new account.
     * If registration is successful, the browser will be redirected to the to the previous page.
     */
    public function actionSignup()
    {
        //$this->signupWithMail();
        $this->signupNoMail();
    }

    private function signupNoMail()
    {
        $model=new Account('signupNoMail');

        if (isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];
            $originalPassword = $model->password;
            $valid = $model->validate();

            if ($valid) {
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

                        Yii::app()->user->setFlash('success', $model->username . ', a regisztrációd elkészült! Bejelentkezhetsz.');
                        $this->redirect('/');
                    } catch (Exception $e) {
                        $transaction->rollback();
                        Yii::app()->user->setFlash('error', 'Hiba lépett fel a játékos mentése során.');
                    }
                    $model->password = $originalPassword;

                } else {
                    Yii::app()->user->setFlash('error', 'Hiba lépett fel a jelszó titkosítása során..');
                }
            }
        }

        $this->render('signupNoMail', array(
            'model'=>$model,
        ));
    }
    private function signupWithMail()
    {
        $model=new Account('signupWithMail');

        if (isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];
            if ($model->validate()) {
                // Create account
                $model->save(false);
                $model->verifyCode = $model->generateCode();

                // Send verification mail
                $mail=Yii::app()->smtpmail;
                $mail->CharSet = 'utf-8';
                $mail->SetFrom('natkay.robert@nrcode.com', 'ced.local'); //todo: activate sender
                $mail->Subject    = "Carp-e Diem regisztráció";
                $message = $this->renderPartial('_verification', ['model'=>$model], true);
                $mail->MsgHTML($message);
                $mail->AddAddress($model->email, "");
                $sent = $mail->Send();
                if (!$sent) {
                    Yii::app()->user->setFlash('error', 'A regisztráció befejezéséhez szükséges információkat nem sikerült elküldeni. Kérlek próbálkozz később.');
                } else {
                    $model->save(false);
                    Yii::app()->user->setFlash('success', 'A regisztráció befejezéséhez szükséges teendőket elküldtük e-mailben.');
                    $this->redirect('/');
                }

            }
        }

        $this->render('signup', array(
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

        if (!$model->verifyCode || $model->username) {
            Yii::app()->user->setFlash('info', 'Már állítottál be magadnak felhasználónevet. Kérlek jelentkezz be.');
            $this->redirect('/');
        }

        if ($model->verifyCode !== $code) {
            Yii::app()->user->setFlash('error', 'Az első belépéshez szükséges oldal címe nem érvényes. Pontosan másoltad be az e-mailből?');
            $this->redirect('/');
        }

        if (isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];
            $originalPassword = $model->password;
            $valid = $model->validate();

            if ($valid) {
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

        $this->render('completeSignup', ['model' => $model]);
    }

    public function actionResetPassword()
    {
        $model = new Account('resetPassword');

        if (isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];

            if ($model->validate()) {
                // Find account
                $model = Account::model()->findByEmail($model->email);

                if (!$model->password) {
                    $model->addError('email', 'A megadott e-mail címhez tartozó játékost még nem regisztráltad.');
                } else {
                    $this->sendResetLink($model);
                }
            }
        }

        $this->render('resetPassword', array(
            'model'=>$model,
        ));
    }

    public function actionCompleteResetPassword($id, $code)
    {
        $model = $this->loadModel($id);
        $model->password = false;
        $model->scenario = 'completeResetPassword';

        if (!$model->resetPasswordCode) {
            Yii::app()->user->setFlash('error', 'A jelszó visszaállításához szükséges oldal címe nem érvényes. Pontosan másoltad be az e-mailből?');
            $this->redirect('/');
        }

        if ($model->resetPasswordCode !== $code) {
            Yii::app()->user->setFlash('error', 'A jelszó visszaállításához szükséges oldal címe nem érvényes. Pontosan másoltad be az e-mailből?');
            $this->redirect('/');
        }

        if (isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];
            $originalPassword = $model->password;

            if ($model->validate()) {
                $hash = password_hash($model->password, PASSWORD_BCRYPT);

                if (password_verify($model->password, $hash)) {
                    //delete passwordCode
                    $model->resetPasswordCode = null;

                    //set new password
                    $model->password = $hash;
                    $model->save(false);
                    $model->refresh();

                    Yii::app()->user->setFlash('success', $model->username . ', a jelszó mentése sikerült!');
                    Yii::app()->session->open();

                    $model->password = $originalPassword;
                    $model->login();
                    $this->redirect('/site');

                } else {
                    Yii::app()->user->setFlash('error', 'Hiba lépett fel a jelszó titkosítása során.');
                }
            }
        }

        $this->render('completeResetPassword', ['model' => $model]);
    }

    public function actionCompleteChangeEmail($id, $code)
    {
        $model = $this->loadModel($id);
        if (!$model->changeMailCode) {
            Yii::app()->user->setFlash('error', 'A beállított e-mail címed már aktiválva van.');
            $this->redirect('/');
        }

        if ($model->changeMailCode !== $code) {
            Yii::app()->user->setFlash('error', 'Az e-mail aktiválásához szükséges oldal címe nem érvényes.');
            $this->redirect('/');
        }

        $account=$this->loadModel($id);
        $account->email = $account->emailTemp;
        $account->emailTemp = '';
        $account->changeMailCode = '';
        $account->save();

        Yii::app()->user->setFlash('success', 'Sikeresen aktiváltuk az e-mail címedet.');
        $this->redirect('/');
    }

    /**
     * @param Account $model
     */
    private function sendResetLink($model)
    {
        // New verification if not exists
        if (!$model->resetPasswordCode) {
            $model->resetPasswordCode = $model->generateCode();
            $model->save(false);
        }

        // Send verification mail
        $mail=Yii::app()->smtpmail;
        $mail->CharSet = 'utf-8';
        $mail->SetFrom('natkay.robert@nrcode.com', 'ced.local'); //todo: activate sender
        $mail->Subject    = "Carp-e Diem elfelejtett jelszó";
        $message = $this->renderPartial('_resetPassword', ['model'=>$model], true);
        $mail->MsgHTML($message);
        $mail->AddAddress($model->email, "");
        $sent = $mail->Send();
        if (!$sent) {
            Yii::app()->user->setFlash('error', 'A jelszó visszaállításához szükséges információkat nem sikerült elküldeni. Kérlek próbálkozz később.');
        } else {
            Yii::app()->user->setFlash('success', 'A jelszó visszaállításához szükséges teendőket elküldtük e-mailben.');
            $this->redirect('/');
        }
    }

    /**
     * @param Account $model
     */
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

        if ($model===null) {
            throw new CHttpException(1, 'A keresett játékos nem található. Ezen könnyen segíthetsz: ' . CHtml::link('regisztráld be.', ['signup']));
        }

        return $model;
    }

    protected function beforeAction($action)
    {
        if (Yii::app()->params['isPartOfWline']) {
            throw new CHttpException(1, 'Ez az aloldal nem használható. ' . CHtml::link('főoldal', ['/site'])); //own nick
        }

        return true;
    }
}
