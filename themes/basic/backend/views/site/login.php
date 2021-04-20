<?php
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Sign In';
?>

<div id="app">
  <section class="section">
    <div class="container mt-5">
      <div class="row">
        <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
          <div class="login-brand">
            <img src="<?= $this->theme->getUrl('img/logo.png') ?>" alt="logo" height="50">
          </div>

          <div class="card card-primary">
            <div class="card-header"><h4>Login</h4></div>

            <div class="card-body">
              <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

              <?= $form
                  ->field($model, 'username')
                  ->textInput()
              ?>

              <?= $form
                  ->field($model, 'password')
                  ->passwordInput()
              ?>

              <div class="form-group">
                <?= Html::submitButton('Sign in', ['class' => 'btn btn-primary btn-lg btn-block', 'name' => 'login-button']) ?>
              </div>
              <?php ActiveForm::end(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
