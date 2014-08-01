<?php
class SetupController extends GameController
{
    public function actionPassword()
    {
        $model=new Account('changePassword');

        if(isset($_POST['Account'])) {
            $model->attributes=$_POST['Account'];
            if($model->validate()) {
                $account=$this->loadModel(Yii::app()->player->uid);

                if($account->validatePassword($model->oldPassword)) {
                    $account->password = password_hash($model->password, PASSWORD_BCRYPT);
                    $account->save(false);
                    
                    Yii::app()->user->setFlash('success', 'A jelszócsere sikerült.');
                    $this->redirect(['/player']);
                } else {
                    $model->addError('oldPassword','Incorrect old password.');
                }
            }
        }

        $this->render('changePassword', ['model'=>$model]);
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
            throw new CHttpException(1, 'A keresett játékos nem található.');
        }

        return $model;
    }
}
