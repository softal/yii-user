<?php
namespace softal\user\controllers;

use softal\user\models\Account;
use softal\user\models\LoginForm;
use yii\base\Model;
use yii\helpers\Url;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\authclient\ClientInterface;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Controller that manages user authentication process.
 *
 * @property \softal\user\Module $module
 *
 */
class SecurityController extends Controller
{
    private $loginAttemptsVar = '__User_LoginAttemptsCount';

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'actions' => ['login', 'auth'], 'roles' => ['?']],
                    ['allow' => true, 'actions' => ['logout'], 'roles' => ['@']],
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post']
                ]
            ]
        ];
    }

    /** @inheritdoc */
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'authenticate'],
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays the login page.
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        $scenario = $this->isCaptchaRequired() ? 'captcha' : 'default';
        $model = \Yii::createObject([
                'class'    => LoginForm::className(),
                'scenario' => $scenario,
        ]);

        $this->performAjaxValidation($model);

        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
            //if login is successful, reset the attempts
            $this->setLoginAttempts(0); 
            
            return $this->goBack();
        }

        //if login is not successful, increase the attempts
        $this->setLoginAttempts($this->getLoginAttempts() + 1);		
        
        return $this->render('login', [
            'model'  => $model,
            'module' => $this->module,
        ]);
    }
    
    /**
     * @return boolean
     */
    private function isCaptchaRequired()
    {
        $allowedattepts = $this->module->failedLoginAttempts;
        return $allowedattepts > getLoginAttempts();
    }
    private function getLoginAttempts()
    {
        Yii::$app->getSession()->get($this->loginAttemptsVar, -1);
    }
    private function setLoginAttempts($value)
    {
        Yii::$app->getSession()->set($this->loginAttemptsVar, $value);
    }

    /**
     * Logs the user out and then redirects to the homepage.
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        \Yii::$app->getUser()->logout();
        return $this->goHome();
    }

    /**
     * Logs the user in if this social account has been already used. Otherwise shows registration form.
     * @param  ClientInterface $client
     * @return \yii\web\Response
     */
    public function authenticate(ClientInterface $client)
    {
        $attributes = $client->getUserAttributes();
        $provider   = $client->getId();
        $clientId   = $attributes['id'];

        $account = $this->finder->findAccountByProviderAndClientId($provider, $clientId);

        if ($account === null) {
            $account = \Yii::createObject([
                'class'      => Account::className(),
                'provider'   => $provider,
                'client_id'  => $clientId,
                'data'       => json_encode($attributes),
            ]);
            $account->save(false);
        }

        if (null === ($user = $account->user)) {
            $this->action->successUrl = Url::to(['/user/registration/connect', 'account_id' => $account->id]);
        } else {
            \Yii::$app->user->login($user, $this->module->rememberFor);
        }
    }

    /**
     * Performs ajax validation.
     * @param Model $model
     * @throws \yii\base\ExitException
     */
    protected function performAjaxValidation(Model $model)
    {
        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            echo json_encode(ActiveForm::validate($model));
            \Yii::$app->end();
        }
    }
}
