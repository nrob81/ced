<?php
/**
 * This is the model class for table "account".
 *
 * The followings are the available columns in table 'account':
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property string $verifyCode
 * @property string $verified
 * @property string $emailTemp
 */
class Account extends CActiveRecord
{
    public $oldPassword;
    public $confirmPassword;
    public $acceptTerms;

    private $identity;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Account the static model class
     */
    public static function model($className = __CLASS__)
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
            array('oldPassword', 'required', 'on'=>'changePassword', 'message'=>'A {attribute} kitöltése kötelező.'),
            array('username', 'required', 'on'=>array('completeSignup', 'signupNoMail'), 'message'=>'A {attribute} kitöltése kötelező.'),
            array('username', 'unique', 'on'=>array('completeSignup', 'signupNoMail'), 'message'=>'A választott {attribute} már foglalt.'),
            array('username', 'length', 'min'=>4, 'max'=>16, 'on'=>array('login', 'completeSignup', 'signupNoMail')),
            array('username', 'match', 'pattern' => '/^[A-Za-z0-9-]+$/u', 'on'=>array('login','completeSignup', 'signupNoMail'), 'message'=>'A {attribute} csak a következő karakterekből állhat: A-Z, a-z, 0-9 és -'),
            array('username', 'match', 'pattern' => '/^[A-Za-z]+/u', 'on'=>array('login', 'completeSignup', 'signupNoMail'), 'message'=>'A {attribute} csak a betűvel kezdődhet.'),
            array('username', 'match', 'pattern' => '/(\-).*(\-)/u', 'not'=>true, 'on'=>array('login', 'completeSignup', 'signupNoMail'), 'message'=>'A {attribute} csak egy kötőjelet tartalmazhat.'),
            array('email', 'required', 'on'=>array('signupWithMail','login','changeEmail','resetPassword'), 'message'=>'Az {attribute} kitöltése kötelező.'),
            array('email', 'length', 'max'=>128, 'on'=>array('signupWithMail','changeEmail')),
            array('email', 'email', 'on'=>array('signupWithMail','changeEmail'), 'message'=>'Az {attribute} nem érvényes.'),
            array('email', 'unique', 'on'=>array('signupWithMail','changeEmail'), 'message'=>'A választott {attribute} már foglalt.'),
            array('email', 'exist', 'on'=>'resetPassword'),
            array('password', 'required', 'on'=>array('login', 'completeSignup', 'signupNoMail', 'changeEmail','changePassword','completeResetPassword','desactivate'), 'message'=>'A {attribute} kitöltése kötelező.'),
            array('password', 'length', 'min'=>6, 'max'=>255, 'on'=>array('completeSignup', 'signupNoMail','changePassword','completeResetPassword')),
            array('password', 'match', 'pattern' => '/[A-Za-z]/u', 'on'=>array('completeSignup', 'signupNoMail','changePassword','completeResetPassword'), 'message'=>'A {attribute}nak tartalmaznia kell legalább egy betűt: A-Z, a-z'),
            array('password', 'match', 'pattern' => '/[0-9]/u', 'on'=>array('completeSignup', 'signupNoMail','changePassword','completeResetPassword'), 'message'=>'A {attribute}nak tartalmaznia kell legalább egy számot.'),
            array('confirmPassword', 'required', 'on'=>array('changePassword','completeResetPassword', 'signupNoMail'), 'message'=>'A {attribute} kitöltése kötelező.'),
            array('confirmPassword', 'compare', 'compareAttribute'=>'password', 'on'=>array('changePassword','completeResetPassword', 'signupNoMail')),
            array('password', 'authenticate', 'on'=>'login'),
            array('verifyCode, verified', 'safe', 'on'=>'completeSignup'),
            array('resetPasswordCode, passwordReset', 'safe', 'on'=>'completeResetPassword'),
            array('acceptTerms', 'required', 'on'=>array('completeSignup', 'signupNoMail'), 'requiredValue'=>1, 'message'=>'A regisztrációhoz el kell fogadni az általános felhasználói szabályzatot.'),
        );
    }

    public function attributeLabels()
    {
        $attributes = [
            'username' => 'felhasználónév',
            'email' => 'e-mail cím',
            'password' => 'jelszó',
            'oldPassword' => 'régi jelszó',
            'confirmPassword' => 'jelszó újra',
            'acceptTerms' => 'Elfogadom az ÁFSZ-ot',
            ];
        if ($this->scenario == 'login') {
            $attributes['email'] = 'e-mail vagy felh.név';
        }
        return $attributes;
    }

    /**
     * @return boolean
     */
    public function validatePassword($password)
    {
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
    public function authenticate($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->identity=new UserIdentity($this->email, $this->password);
            if (!$this->identity->authenticate()) {
                if ($this->identity->findEmail) {
                    $this->addError('validation', 'A megadott e-mail cím és jelszó nem érvényes.');
                } else {
                    $this->addError('validation', 'A megadott felhasználónév és jelszó nem érvényes.');
                }
            }
        }
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login()
    {
        if ($this->identity===null) {
            $this->identity=new UserIdentity($this->email, $this->password);
            $this->identity->authenticate();
        }

        if ($this->identity->errorCode===UserIdentity::ERROR_NONE) {
            $duration = 3600*24*30; // 30 days
            Yii::app()->user->login($this->identity, $duration);
            Yii::app()->session['uid'] = $this->identity->uid;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Finds an account by email
     * @param string $email The email
     * @return Account
     */
    public function findByEmail($email)
    {
        return $this->find('LOWER(email)=?', array(strtolower($email)));
    }
}
