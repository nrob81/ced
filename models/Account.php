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
    public $confirmPassword;

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
            array('username', 'required', 'on'=>array('completeSignup'), 'message'=>'A {attribute} kitöltése kötelező.'),
            array('username', 'unique', 'on'=>'completeSignup', 'message'=>'A választott {attribute} már foglalt.'),
            array('username', 'length', 'min'=>4, 'max'=>16, 'on'=>array('login', 'completeSignup')),
            array('username', 'match', 'pattern' => '/^[A-Za-z0-9-]+$/u', 'on'=>array('login','completeSignup'), 'message'=>'A {attribute} csak a következő karakterekből állhat: A-Z, a-z, 0-9 és -'),
            array('username', 'match', 'pattern' => '/^[A-Za-z]+/u', 'on'=>array('login', 'completeSignup'), 'message'=>'A {attribute} csak a betűvel kezdődhet.'),
            array('username', 'match', 'pattern' => '/(\-).*(\-)/u', 'not'=>true, 'on'=>array('login', 'completeSignup'), 'message'=>'A {attribute} csak egy kötőjelet tartalmazhat.'),
            array('email', 'required', 'on'=>array('signup','login','changeEmail','resetPassword'), 'message'=>'Az {attribute} kitöltése kötelező.'),
            array('email', 'length', 'max'=>128, 'on'=>array('signup','changeEmail')),
            array('email', 'email', 'on'=>array('signup','changeEmail'), 'message'=>'Az {attribute} nem érvényes.'),
            array('email', 'unique', 'on'=>array('signup','changeEmail'), 'message'=>'A választott {attribute} már foglalt.'),
            array('email', 'exist', 'on'=>'resetPassword'),
            array('password', 'required', 'on'=>array('login', 'completeSignup', 'changeEmail','changePassword','completeResetPassword','desactivate'), 'message'=>'A {attribute} kitöltése kötelező.'),
            array('password', 'length', 'min'=>6, 'max'=>255, 'on'=>array('completeSignup','changePassword','completeResetPassword')),
            array('password', 'match', 'pattern' => '/[A-Za-z]/u', 'on'=>array('completeSignup','changePassword','completeResetPassword'), 'message'=>'A {attribute}nak tartalmaznia kell legalább egy betűt: A-Z, a-z'),
            array('password', 'match', 'pattern' => '/[0-9]/u', 'on'=>array('completeSignup','changePassword','completeResetPassword'), 'message'=>'A {attribute}nak tartalmaznia kell legalább egy számot.'),
            array('oldPassword', 'required', 'on'=>'changePassword'),
            array('confirmPassword', 'required', 'on'=>array('changePassword','completeResetPassword')),
            array('confirmPassword', 'compare', 'compareAttribute'=>'password', 'on'=>array('changePassword','completeResetPassword')),
            array('password', 'authenticate', 'on'=>'login'),
            array('verifyCode, verified', 'safe', 'on'=>'completeSignup'),
            array('resetPasswordCode, passwordReset', 'safe', 'on'=>'completeResetPassword'),
        );
    }

    public function AttributeLabels()
    {
        return [
            'username' => 'felhasználónév',
            'email' => 'e-mail cím',
            'password' => 'jelszó',
            'oldPassword' => 'régi jelszó',
            ];
    }

    public function validatePassword($password)
    {
        Yii::import('vendor.*');
        require_once('ircmaxell/password-compat/lib/password.php');
        return password_verify($password, $this->password);
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
            if(!$this->_identity->authenticate()) {
                $this->addError('validation','Incorrect email or password.');
                echo $this->_identity->errorCode;
            }
        }
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login()
    {
        echo __FUNCTION__;
        if($this->_identity===null) {
            $this->_identity=new UserIdentity($this->email,$this->password);
            $this->_identity->authenticate();
        }

        if($this->_identity->errorCode===UserIdentity::ERROR_NONE) {
            $duration = 3600*24*30; // 30 days
            Yii::app()->user->login($this->_identity,$duration);
            Yii::app()->session['uid'] = $this->_identity->uid;

            echo 't';
            return true;
        } else {
            echo 'f';
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
