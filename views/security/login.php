<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use softal\user\widgets\Connect;
use yii\captcha\Captcha;

/**
 * @var yii\web\View                    $this
 * @var softal\user\models\LoginForm    $model
 * @var softal\user\Module              $module
 */

$this->title = Yii::t('user', 'Sign in');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id'                     => 'login-form',
                    // 'enableAjaxValidation'   => true,
                    // 'enableClientValidation' => false,
                    // 'validateOnBlur'         => false,
                    // 'validateOnType'         => false,
                    // 'validateOnChange'       => false, hh
                ]) ?>

                <?= $form->field($model, 'login', ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => '1']]) ?>

                <?= $form->field($model, 'password', ['inputOptions' => ['class' => 'form-control', 'tabindex' => '2']])->passwordInput()->label(Yii::t('user', 'Password') . ' (' . Html::a(Yii::t('user', 'Forgot password?'), ['/user/recovery/request'], ['tabindex' => '5']) . ')') ?>

                <?= $form->field($model, 'rememberMe')->checkbox(['tabindex' => '4']) ?>

                <?php if ($model->scenario == 'captcha'): ?>
                        <?= $form->field($model, 'captcha')->widget(Captcha::className(), [
							'captchaAction' => 'captcha',
							//'template' => '<div class="row"><div class="col-lg-3">{image}</div><div class="col-lg-9">{input}</div></div>',
							'imageOptions' => ['height' => 35],
							'options' => ['class' => 'form-control'],
						]) ?>
                <?php endif; ?>
                
                <?= Html::submitButton(Yii::t('user', 'Sign in'), ['class' => 'btn btn-primary btn-block', 'tabindex' => '3']) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?php if ($module->enableConfirmation): ?>
            <p class="text-center">
                <?= Html::a(Yii::t('user', 'Didn\'t receive confirmation message?'), ['/user/registration/resend']) ?>
            </p>
        <?php endif ?>
        <?= Connect::widget([
            'baseAuthUrl' => ['/user/security/auth']
        ]) ?>
    </div>
</div>
