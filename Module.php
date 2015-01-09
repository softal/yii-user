<?php
namespace softal\user;

use yii\base\Module as BaseModule;

/**
 * This is the main module class for the Yii2-user.
 * @author Alek
 */
class Module extends BaseModule
{
    const VERSION = '0.1.1-dev';

    /** Email is changed right after user enter's new email address. */
    const STRATEGY_INSECURE = 0;

    /** Email is changed after user clicks confirmation link sent to his new email address. */
    const STRATEGY_DEFAULT = 1;

    /** Email is changed after user clicks both confirmation links sent to his old and new email addresses. */
    const STRATEGY_SECURE = 2;

    /** @var bool Whether to show flash messages. */
    public $enableFlashMessages = true;

    /** @var bool Whether to enable registration. */
    public $enableRegistration = true;

    /** @var bool Whether to remove password field from registration form. */
    public $enableGeneratingPassword = false;

    /** @var bool Whether user has to confirm his account. */
    public $enableConfirmation = true;

    /** @var bool Whether to allow logging in without confirmation. */
    public $enableUnconfirmedLogin = false;

    /** @var bool Whether to enable password recovery. */
    public $enablePasswordRecovery = true;

    /** @var integer Email changing strategy. */
    public $emailChangeStrategy = self::STRATEGY_DEFAULT;

    /** @var int The time you want the user will be remembered without asking for credentials. */
    public $rememberFor = 1209600; // two weeks

    /** @var int The time before a confirmation token becomes invalid. */
    public $confirmWithin = 86400; // 24 hours

    /** @var int The time before a recovery token becomes invalid. */
    public $recoverWithin = 21600; // 6 hours

    /** @var int Cost parameter used by the Blowfish hash algorithm. */
    public $cost = 10;

    /** @var array An array of administrator's usernames. */
    public $admins = [];

    /** @var array Mailer configuration */
    public $mailer = [];

    /** @var array Model map */
    public $modelMap = [];

    /**
     * @var string The prefix for user module URL.
     * @See [[GroupUrlRule::prefix]]
     */
    public $urlPrefix = 'user';

    /** @var array The rules to be used in URL management. */
    public $urlRules = [
        '<id:\d+>'                    => 'profile/show',
        '<action:(login|logout)>'     => 'security/<action>',
        '<action:(register|resend)>'  => 'registration/<action>',
        'confirm/<id:\d+>/<code:\w+>' => 'registration/confirm',
        'forgot'                      => 'recovery/request',
        'recover/<id:\d+>/<code:\w+>' => 'recovery/reset',
        'settings/<action:\w+>'       => 'settings/<action>'
    ];

        /**
    * @var Closure an anonymous function that will return current timestamp
    * for populating the timestamp fields. Defaults to
    * `function() { return date("Y-m-d H:i:s"); }`
    */
    public $now;
    
    /**
     * @var array the login settings for the module. The following options can be set:
     * - loginType: integer, whether users can login with their username, email address, or both.
     *   Defaults to `Module::LOGIN_BOTH`.
     * - rememberMeDuration: integer, the duration in seconds for which user will remain logged in on his/her client
     *   using cookies. Defaults to 3600*24*1 seconds (30 days).
     * - wrongAttempts: integer|bool, the number of consecutive wrong password type attempts, at login, after which
     *   the account is inactivated and needs to be reset. Defaults to `false`. If set to `0` or `false`, the account
     *   is never inactivated after any wrong password attempts.
     * - loginRedirectUrl: string|array, the default url to redirect after login. Normally the last return
     *   url will be used. This setting will only be used if no return url is found.
     * - logoutRedirectUrl: string|array, the default url to redirect after logout. If not set, it will redirect
     *   to home page.
     * @see `setConfig()` method for the default settings
     */
    public $loginSettings = [];
    
    /**
     * @var array the settings for the password in the module. The following options can be set"
     * - validateStrength: array|boolean, the list of forms where password strength will be validated. If
     *   set to `false` or an empty array, no strength will be validated. The strength will be validated
     *   using `\kartik\password\StrengthValidator`. Defaults to `[self::UI_INSTALL, self::UI_REGISTER, Module::UI_RESET]`.
     * - strengthRules: array, the strength validation rules as required by `\kartik\password\StrengthValidator`
     * - strengthMeter: array|boolean, the list of forms where password strength meter will be displayed.
     *   If set to `false` or an empty array, no strength meter will be displayed.  Defaults to
     *   `[self::UI_INSTALL, self::UI_REGISTER, Module::UI_RESET]`.
     * - activationKeyExpiry: integer|bool, the time in seconds after which the account activation key/token will expire.
     *   Defaults to 3600*24*2 seconds (2 days). If set to `0` or `false`, the key never expires.
     * - resetKeyExpiry: integer|bool, the time in seconds after which the password reset key/token will expire.
     *   Defaults to 3600*24*2 seconds (2 days). If set to `0` or `false`, the key never expires.
     * - passwordExpiry: integer|bool, the timeout in seconds after which user is required to reset his password
     *   after logging in. Defaults to `false`. If set to `0` or `false`, the password never expires.
     * - enableRecovery: bool, whether password recovery is permitted. If set to `true`, users will be given an option
     *   to reset/recover a lost password. Defaults to `true`.
     * @see `setConfig()` method for the default settings
     */
    public $passwordSettings = [];
    
     /**
     * Initialize the module
     */
    public function init()
    {
        parent::init();
        $this->setConfig();
    }   
    
    /**
     * Sets the module configuration defaults
     */
    public function setConfig()
    {
        if (empty($this->now) || !$this->now instanceof \Closure) {
            $this->now = function () {
                return date('Y-m-d H:i:s');
            };
        }
        $this->actionSettings += [
            // the list of account actions
            self::ACTION_LOGIN => 'account/login',
            self::ACTION_LOGOUT => 'account/logout',
            self::ACTION_REGISTER => 'account/register',
            self::ACTION_ACTIVATE => 'account/activate',
            self::ACTION_RESET => 'account/reset',
            self::ACTION_RECOVERY => 'account/recovery',
            // the list of social actions
            self::ACTION_SOCIAL_LOGIN => 'social/login',
            // the list of profile actions
            self::ACTION_PROFILE_VIEW => 'profile/view',
            self::ACTION_PROFILE_LIST => 'profile/index',
            self::ACTION_PROFILE_EDIT => 'profile/update',
            self::ACTION_PROFILE_UPLOAD => 'profile/upload',
            // the list of admin actions
            self::ACTION_ADMIN_LIST => 'admin/index',
            self::ACTION_ADMIN_VIEW => 'admin/view',
            self::ACTION_ADMIN_EDIT => 'admin/update',
            self::ACTION_ADMIN_BAN => 'admin/ban',
            self::ACTION_ADMIN_UNBAN => 'admin/unban',
        ];
        $this->loginSettings += [
            'loginType' => self::LOGIN_BOTH,
            'rememberMeDuration' => 2592000
        ];
        $this->passwordSettings += [
            'validateStrength' => [self::UI_INSTALL, self::UI_REGISTER, Module::UI_RESET],
            'strengthRules' => [
                'min' => 8,
                'upper' => 1,
                'lower' => 1,
                'digit' => 1,
                'special' => 0,
                'hasUser' => true,
                'hasEmail' => true
            ],
            'strengthMeter' => [self::UI_INSTALL, self::UI_REGISTER, Module::UI_RESET],
            'activationKeyExpiry' => 172800,
            'resetKeyExpiry' => 172800,
            'passwordExpiry' => false,
            'wrongAttempts' => false,
            'enableRecovery' => true
        ];
        $this->registrationSettings += [
            'enabled' => true,
            'captcha' => [],
            'autoActivate' => false,
            'userNameRules' => ['min' => 4, 'max' => 30],
            'userNamePattern' => '/^[A-Za-z0-9_-]+$/u',
            'userNameValidMsg' => Yii::t('user', '{attribute} can contain only letters, numbers, hyphen, and underscore.')
        ];
        $appName = \Yii::$app->name;
        $supportEmail = isset(\Yii::$app->params['supportEmail']) ? \Yii::$app->params['supportEmail'] : 'nobody@support.com';
        $this->notificationSettings += [
            'enabled' => true,
            'viewPath' => '@communityii/user/views/mail',
            'activation' => [
                'enabled' => true,
                'fromEmail' => $supportEmail,
                'fromName' => Yii::t('user', '{appname} Robot', ['appname' => $appName]),
                'subject' => Yii::t('user', Yii::t('user', 'Account activation for {appname}', ['appname' => $appName])),
                'template' => 'activation'
            ],
            'recovery' => [
                'enabled' => true,
                'fromEmail' => $supportEmail,
                'fromName' => Yii::t('user', '{appname} Robot', ['appname' => $appName]),
                'subject' => Yii::t('user', Yii::t('user', 'Account recovery for {appname}', ['appname' => $appName])),
                'template' => 'recovery'
            ],
            'mailDelivery' => self::ENQUEUE_AND_MAIL
        ];
        $this->socialAuthSettings += [
            'enabled' => true,
            'refreshAttributes' => [
                'profile_name',
                'email'
            ],
        ];
        $this->avatarSettings += [
            'enabled' => true,
            'uploadSettings' => [
                'registration' => false,
                'profile' => true,
                'allowedTypes' => '.jpg, .gif, .png',
                'maxSize' => 2097152
            ],
            'linkSocial' => true
        ];
        $this->rbacSettings += [
            'enabled' => true,
            'type' => self::RBAC_SIMPLE,
            'config' => [
                'class' => '\communityii\rbac\SimpleRBAC',
            ]
        ];
        $this->widgetSettings += [
            self::UI_LOGIN => ['type' => 'vertical'],
            self::UI_REGISTER => ['type' => 'horizontal'],
            self::UI_ACTIVATE => ['type' => 'inline'],
            self::UI_RECOVERY => ['type' => 'inline'],
            self::UI_RESET => ['type' => 'vertical'],
            self::UI_PROFILE => ['type' => 'vertical'],
            self::UI_ADMIN => ['type' => 'vertical'],
        ];
        $this->messages += [
            self::MSG_REGISTRATION_ACTIVE => "You have been successfully registered and logged in as '{username}'",
            self::MSG_PENDING_ACTIVATION => "Your registration form has been received. Instructions for activating your account has been sent to your email '{email}'.",
            self::MSG_PENDING_ACTIVATION_ERR => "Your registration form has been received. Activation instructions could not be sent to your email '{email}'. Contact the system administrator.",
            self::MSG_PASSWORD_EXPIRED => "Your password has expired. You may reset your password by clicking {resetLink}.",
            self::MSG_ACCOUNT_LOCKED => "Your account has been locked due to multiple wrong password attempts. You may reset and activate your account by clicking {resetLink}."
        ];
    }
    /**
     * Validate the module configuration
     *
     * @param Module $module the user module object
     * @throws InvalidConfigException
     */
    public static function validateConfig(&$module)
    {
        $module = Yii::$app->getModule('user');
        if ($module === null) {
            throw new InvalidConfigException("The module 'user' was not found . Ensure you have setup the 'user' module in your Yii configuration file.");
        }
    }
}
