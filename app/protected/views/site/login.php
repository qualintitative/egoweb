<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use yii\captcha\Captcha;

?>
<div class="site-login">
    <h1 id="form-header">Log In</h1>

    <p>Please fill out the following fields to login:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <?= $form->field($model, 'username')->textInput(['autofocus' => true])->label('Email') ?>

                <?= $form->field($model, 'password')->passwordInput() ?>
                <?php if($failedCount > 3): ?>
                <?= $form->field($model, 'captcha')->widget(Captcha::className()) ?>
                <?php endif; ?>
                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div style="color:#999;margin:1em 0">
                    If you forgot your password you can <?= Html::a('reset it', ['site/request-password-reset']) ?>.
                </div>

                <div class="form-group">
                    <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
            <span class="text-white"><?php echo $failedCount; ?></span>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
      //Processing click refresh verification code
      $("#loginform-captcha-image").on("click", function () {
        $.get("<?php echo Url::toRoute('site/captcha') ?>?refresh", function (data) {
          $("#loginform-captcha-image").attr("src", data["url"]);
        }, "json");
      });
    });
  </script>
