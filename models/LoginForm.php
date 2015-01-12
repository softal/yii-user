<?php
namespace softal\user\models;

use softal\user\components\Finder;
use yii\base\Model;
use yii\captcha\Captcha;
use softal\user\helpers\Password;

/**
 * LoginForm get user's login and password, validates them and logs the user in. If user has been blocked, it adds
 * an error to login form.
 *
 */
class LoginForm extends Model
{
    /** @var string User's email or username */
    public $login;

    /** @var string User's plain password */
    public $password;

    /** @var string Whether to remember the user */
    public $rememberMe = false;

    /** @var \softal\user\models\User */
    protected $user;

    /** @var \softal\user\Module */
    protected $module;

    /** @var Finder */
    protected $finder;

    /** @var string */
    public $captcha;

    /**
     * @param Finder $finder
     * @param array $config
     */
    public function __construct(Finder $finder, $config = [])
    {
        $this->finder = $finder;
        $this->module = \Yii::$app->getModule('user');
        parent::__construct($config);
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'login'      => \Yii::t('user', 'Login'),
            'password'   => \Yii::t('user', 'Password'),
            'rememberMe' => \Yii::t('user', 'Remember me next time'),
            'captcha'    => \Yii::t('user', 'Verification Code'),
        ];
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            ['login', 'trim'],
            ['password', function ($attribute) {
                if ($this->user === null || !Password::validate($this->password, $this->user->password_hash)) {
                    $this->addError($attribute, \Yii::t('user', 'Invalid login or password'));
                }
            }],
            ['login', function ($attribute) {
                if ($this->user !== null) {
                    $confirmationRequired = $this->module->enableConfirmation && !$this->module->enableUnconfirmedLogin;
                    if ($confirmationRequired && !$this->user->getIsConfirmed()) {
                        $this->addError($attribute, \Yii::t('user', 'You need to confirm your email address'));
                    }
                    if ($this->user->getIsBlocked()) {
                        $this->addError($attribute, \Yii::t('user', 'Your account has been blocked'));
                    }
                    // softal
//                    if ($this->user->getIsLocked()) {
//                        $this->addError($attribute, \Yii::t('user', 'Your account has been locked due to too many failed login attempts'));
//                    }
                }
            }],
            ['rememberMe', 'boolean'],
            ['captcha',  'captcha', 'on' => 'captcha'],
        ];
    }

    /**
     * Validates form and logs the user in.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $success = \Yii::$app->getUser()->login($this->user, $this->rememberMe ? $this->module->rememberFor : 0);
            $this->createHistoryEntry($success);
            $this->user->updateAttributes([
                'login_count' => $this->user->login_count + 1, 
            ]);
            return $success;
        } else {
            return false;
        }
    }
    
    /** @inheritdoc */
    protected function createHistoryEntry($success)
    {
        $loginHistory = \Yii::createObject([
            'class'   => UserLoginHistory::className(),
            'user_id' => $this->user->id,
            'login_ip' => \Yii::$app->request->userIP,
            'platform' => '',
            'browser' => '',
            'failed_attempts' => $success ? 0 : 1,
            'success' => $success,
        ]);
        $loginHistory->save(false);
    }
    
    /** @inheritdoc */
    public function formName()
    {
        return 'login-form';
    }

    /** @inheritdoc */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->user = $this->finder->findUserByUsernameOrEmail($this->login);
            return true;
        } else {
            return false;
        }
    }
}
