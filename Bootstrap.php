<?php
namespace softal\user;

use yii\base\BootstrapInterface;
use yii\web\GroupUrlRule;
use yii\console\Application as ConsoleApplication;

class Bootstrap implements BootstrapInterface
{
    /** @var array Model's map */
    private $_modelMap = [
        'User'             => 'softal\user\models\User',
        'Provider'         => 'softal\user\models\Provider',
        'Profile'          => 'softal\user\models\Profile',
        'Token'            => 'softal\user\models\Token',
        'RegistrationForm' => 'softal\user\models\RegistrationForm',
        'ResendForm'       => 'softal\user\models\ResendForm',
        'LoginForm'        => 'softal\user\models\LoginForm',
        'SettingsForm'     => 'softal\user\models\SettingsForm',
        'RecoveryForm'     => 'softal\user\models\RecoveryForm',
        'UserSearch'       => 'softal\user\models\UserSearch',
        'UserPasswordHistory'    => 'softal\user\models\UserPasswordHistory',
        'UserLoginHistory'       => 'softal\user\models\UserLoginHistory',
    ];

    /** @inheritdoc */
    public function bootstrap($app)
    {
        /** @var $module Module */
        if ($app->hasModule('user') && ($module = $app->getModule('user')) instanceof Module) {
            $this->_modelMap = array_merge($this->_modelMap, $module->modelMap);
            foreach ($this->_modelMap as $name => $definition) {
                $class = "softal\\user\\models\\" . $name;
                \Yii::$container->set($class, $definition);
                $modelName = is_array($definition) ? $definition['class'] : $definition;
                $module->modelMap[$name] = $modelName;
                if (in_array($name, ['User', 'Profile', 'Token', 'Account'])) {
                    \Yii::$container->set($name . 'Query', function () use ($modelName) {
                        return $modelName::find();
                    });
                }
            }
            \Yii::$container->setSingleton(Finder::className(), [
                'userQuery'    => \Yii::$container->get('UserQuery'),
                'profileQuery' => \Yii::$container->get('ProfileQuery'),
                'tokenQuery'   => \Yii::$container->get('TokenQuery'),
                'accountQuery' => \Yii::$container->get('AccountQuery'),
            ]);
            \Yii::$container->set('yii\web\User', [
                'enableAutoLogin' => true,
                'loginUrl'        => ['/user/security/login'],
                'identityClass'   => $module->modelMap['User'],
            ]);

            if ($app instanceof ConsoleApplication) {
                $module->controllerNamespace = 'softal\user\commands';
            } else {
                $configUrlRule = [
                    'prefix' => $module->urlPrefix,
                    'rules'  => $module->urlRules
                ];

                if ($module->urlPrefix != 'user') {
                    $configUrlRule['routePrefix'] = 'user';
                }

                $app->get('urlManager')->rules[] = new GroupUrlRule($configUrlRule);

                if (!$app->has('authClientCollection')) {
                    $app->set('authClientCollection', [
                        'class' => 'yii\authclient\Collection',
                    ]);
                }
            }

            $app->get('i18n')->translations['user*'] = [
                'class'    => 'yii\i18n\PhpMessageSource',
                'basePath' => __DIR__ . '/messages',
            ];

            $defaults = [
                'welcomeSubject'        => \Yii::t('user', 'Welcome to {0}', \Yii::$app->name),
                'confirmationSubject'   => \Yii::t('user', 'Confirm account on {0}', \Yii::$app->name),
                'reconfirmationSubject' => \Yii::t('user', 'Confirm email change on {0}', \Yii::$app->name),
                'recoverySubject'       => \Yii::t('user', 'Complete password reset on {0}', \Yii::$app->name)
            ];

            \Yii::$container->set('softal\user\components\Mailer', array_merge($defaults, $module->mailer));
        }
        
    }
}