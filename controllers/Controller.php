<?php
namespace softal\user\controllers;

use softal\user\components\Finder;
use yii\web\Controller as BaseController;

use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @property Module $module
 */
class Controller extends BaseController
{
    /** @var Finder */
    protected $finder;

    /**
     * @param string $id
     * @param \yii\base\Module $module
     * @param Finder $finder
     * @param array $config
     */
    public function __construct($id, $module, Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($id, $module, $config);
    }
    
    /**
     * Loads a token of a specific type.
     *
     * @param string $type token type.
     * @param string $token token string.
     * @return AccountToken
     */
    protected function loadToken($type, $token)
    {
        $model = $this->module->loadToken($type, $token);
        if ($model === null) {
            $this->accessDenied(Module::t('errors', 'Invalid authentication token.'));
        }
        return $model;
    }

    /**
     * @param string $message error message.
     * @throws HttpException when called.
     */
    public function accessDenied($message = null)
    {
        throw new ForbiddenHttpException($message === null ? Module::t('errors', 'Access denied.') : $message);
    }

    /**
     * @param string $message error message.
     * @throws HttpException when called.
     */
    public function pageNotFound($message = null)
    {
        throw new NotFoundHttpException($message === null ? Module::t('errors', 'Page not found.') : $message);
    }

    /**
     * @param string $message error message.
     * @throws HttpException when called.
     */
    public function fatalError($message = null)
    {
        throw new ServerErrorHttpException($message === null ? Module::t('errors', 'Something went wrong.') : $message);
    }
}