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
    private $findEmail;

    /**
     * Authenticates a user.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        if (strpos($this->username, "@")) {
            $this->findEmail = true;
            $account = Account::model()->find('LOWER(email) = :email', [':email' => strtolower($this->username)]);
        } else {
            $this->findEmail = false;
            $account = Account::model()->find('username = :username', [':username' => $this->username]);
        }

        if ($account===null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } elseif (!$account->validatePassword($this->password)) {
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        } else {
            //check player
            if ($this->playerExists($account->id)) {
                $this->uid=$account->id;
                $this->username=$account->username;
                $this->errorCode=self::ERROR_NONE;
            } else {
                $this->errorCode=3; //username exists without player
            }
        }
        return $this->errorCode==self::ERROR_NONE;
    }

    /**
     * @return integer the ID of the user record
     */
    public function getUid()
    {
        return $this->uid;
    }
    public function getFindEmail()
    {
        return $this->findEmail;
    }

    /**
     * @param integer $uid
     */
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
