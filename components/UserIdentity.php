<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 * @property integer $uid
 */
class UserIdentity extends CUserIdentity
{
    private $uid;

    /**
     * Authenticates a user.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        $account=Account::model()->find('LOWER(email)=?',array(strtolower($this->username)));
        if($account===null)
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        else if(!$account->validatePassword($this->password))
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        else
        {
            //check player
            if ($this->playerExists($account->id)) {
                $this->uid=$account->id;
                $this->username=$account->email;
                $this->errorCode=self::ERROR_NONE;
            } else {
                $this->errorCode=self::ERROR_USERNAME_INVALID;
            }
        }
        return $this->errorCode==self::ERROR_NONE;
    }

    /**
     * @return integer the ID of the user record
     */
    public function getuId()
    {
        return $this->uid;
    }

    private function playerExists($uid)
    {
        $foundUid = Yii::app()->db->createCommand()
            ->select('uid')
            ->from('main')
            ->where('uid=:uid', [':uid'=>$uid])
            ->queryScalar();
        return $foundUid > 0;
    }
}
