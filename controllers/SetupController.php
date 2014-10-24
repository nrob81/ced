<?php
class SetupController extends GameController
{
    public function actionPassword()
    {
        $model=new Account('changePassword');

        if (isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];
            if ($model->validate()) {
                $account=$this->loadModel(Yii::app()->player->uid);

                if ($account->validatePassword($model->oldPassword)) {
                    $account->password = password_hash($model->password, PASSWORD_BCRYPT);
                    $account->save(false);

                    Yii::app()->user->setFlash('success', 'A jelszócsere sikerült.');
                    $this->redirect(['/player']);
                } else {
                    $model->addError('oldPassword', 'A megadott régi jelszó nem érvényes.');
                }
            }
        }

        $this->render('changePassword', ['model'=>$model]);
    }

    public function actionEmail()
    {
        $model=new Account('changeEmail');
        $account = $this->loadModel(Yii::app()->player->uid);

        if (isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];
            if ($model->validate()) {
                if ($account->validatePassword($model->password)) {
                    if (!$account->changeMailCode) {
                        $account->changeMailCode = $account->generateCode();
                    }

                    Account::model()->updateByPk(Yii::app()->player->uid, array(
                        'changeMailCode' => $account->changeMailCode,
                        'emailTemp' => $model->email
                    ));

                    // Send verification mail
                    $mail=Yii::app()->smtpmail;
                    $mail->CharSet = 'utf-8';
                    $mail->SetFrom('natkay.robert@nrcode.com', 'ced.local'); //todo: activate sender
                    $mail->Subject    = "Carp-e Diem e-mail cím beállítása";
                    $message = $this->renderPartial('_changeEmail', ['model'=>$account], true);
                    $mail->MsgHTML($message);
                    $mail->AddAddress($model->email, "");
                    //$sent = true; 
                    $sent = $mail->Send(); //todo: activate on production
                    if (!$sent) {
                        //echo "Mailer Error: " . $mail->ErrorInfo;
                        Yii::app()->user->setFlash('error', 'Az új e-mail cím aktiválásához szükséges információkat nem sikerült elküldeni. Kérlek próbálkozz később.');
                    } else {
                        Yii::app()->user->setFlash('success', 'Az új e-mail cím aktiválásához szükséges teendőket elküldtük e-mailben.');
                        $this->redirect('email');
                    }

                } else {
                    $model->addError('password', 'A megadott jelszó nem érvényes.');
                }
            }
        }

        $this->render('changeEmail', [
            'model'=>$model,
            'account'=>$account,
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
            throw new CHttpException(1, 'A keresett játékos nem található.');
        }

        return $model;
    }
}
