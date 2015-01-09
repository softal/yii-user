<?php
namespace softal\user;
use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    const VERSION = '0.1.1-dev';

    /** Email is changed right after user enter's new email address. */
    const STRATEGY_INSECURE = 0;
    /** Email is changed after user clicks confirmation link sent to his new email address. */
    const STRATEGY_DEFAULT = 1;
    /** Email is changed after user clicks both confirmation links sent to his old and new email addresses. */
    const STRATEGY_SECURE = 2;

    public $enableFlashMessages = true;
    public $enableRegistration = true;
    /** @var bool Whether to remove password field from registration form. */
    public $enableGeneratingPassword = false;
    /** @var bool Whether user has to confirm his account. */
    public $enableConfirmation = true;
    /** @var bool Whether to allow logging in without confirmation. */
    public $enableUnconfirmedLogin = false;
    public $enablePasswordRecovery = true;
    public $emailChangeStrategy = self::STRATEGY_DEFAULT;
    public $rememberFor = 1209600; // two weeks rememberMeDuration
    public $confirmWithin = 86400; // 24 hours
    public $recoverWithin = 21600; // 6 hours
    public $wrongAttempts = false; // integer|bool, the number of consecutive wrong logins attempts, defaults to `false`. If set to `0` or `false`, the account
    public $cost = 10;
    public $passwordTimeout = 30; // the timeout in days after which user is required to reset his password. If set to `0` or `false`, the pwd never expires.
    
    //loginRedirectUrl  string|array, the default url to redirect after login. Normally the last return url will be used. This setting will only be used if no return url is found.
    //logoutRedirectUrl string|array, the default url to redirect after logout. If not set, it will redirect to home page. 
    
    /**
     * @var array Set of rules to measure the password strength when choosing new password in the registration or recovery forms.
     * Rules should NOT include attribute name, it will be added when they are used.
     * If null, defaults to minimum 8 characters and at least one of each: lower and upper case character and a digit.
     * @see BasePasswordForm
     */
    public $passwordStrengthRules;

    
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
   

}
