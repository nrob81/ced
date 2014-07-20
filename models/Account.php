<?php
/**
 * This is the model class for table "account".
 *
 * The followings are the available columns in table 'account':
 * @property integer $id
 * @property string $email
 * @property string $password
 */
class Account extends CActiveRecord
{
    public $oldPassword;

    private $_identity;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Account the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'account';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('email', 'required', 'on'=>array('signup','login','changeEmail','resetPassword'), 'message'=>'Az {attribute} kitöltése kötelező.'),
            array('email', 'length', 'max'=>128, 'on'=>array('signup','changeEmail')),
            array('email', 'email', 'on'=>array('signup','changeEmail')),
            array('email', 'unique', 'on'=>array('signup','changeEmail')),
            array('email', 'exist', 'on'=>'resetPassword'),
            array('password', 'required', 'on'=>array('login','changeEmail','changePassword','completeResetPassword','desactivate'), 'message'=>'A {attribute} kitöltése kötelező.'),
            array('password', 'length', 'min'=>6, 'max'=>128, 'on'=>array('changePassword','completeResetPassword')),
            array('oldPassword', 'required', 'on'=>'changePassword'),
            array('password', 'authenticate', 'on'=>'login'),
            array('verifyCode', 'safe'),
        );
    }

    /**
     * Generates the password hash.
     * @param string password
     * @return string hash
     */
    public function hashPassword($password)
    {
        return crypt($password);
    }

    /**
	 * Generates a random code
	 */
	public function generateCode()
	{
		return md5(mt_rand());
	}
    
    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute,$params)
    {
        if(!$this->hasErrors())
        {
            $this->_identity=new UserIdentity($this->email,$this->password);
            if(!$this->_identity->authenticate())
                $this->addError('password','Incorrect email or password.');
        }
    }

    /**
     * Checks if the given password is correct.
     * @param string the password to be validated
     * @return boolean whether the password is valid
     */
    public function validatePassword($password)
    {
        return crypt($password,$this->password)===$this->password;
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login()
    {
        if($this->_identity===null)
        {
            $this->_identity=new UserIdentity($this->email,$this->password);
            $this->_identity->authenticate();
        }

        if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
        {
            $duration = 3600*24*30; // 30 days
            Yii::app()->user->login($this->_identity,$duration);
            Yii::app()->session['uid'] = $this->_identity->uid;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Finds an account by email
     * @param string $email The email
     */
    public function findByEmail($email)
    {
        return $this->find('LOWER(email)=?',array(strtolower($email)));
    }
}
