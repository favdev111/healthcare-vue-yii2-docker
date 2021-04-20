<?php

/* @var $this yii\web\View */
/* @var $model \modules\account\models\backend\ChangePasswordForm */
/* @var bool $passwordChanged */

use yii\bootstrap\Alert;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

?>

<?php $pjax = Pjax::begin([
    'id' => 'change-password-pjax',
    'timeout' => false,
    'enablePushState' => false,
    'enableReplaceState' => false,
    'options' => [
        'data-pjax-push-state' => false
    ]
]); ?>

<?php $form = ActiveForm::begin([
    'id' => 'change-password-form',
    'options' => [
        'data-pjax' => true,
        'autocomplete' => 'off'
    ],
]); ?>

<?= $form->field($model, 'newPassword')->textInput(['type' => 'password', 'autocomplete' => 'off']) ?>
<?= $form->field($model, 'newPasswordRepeat')->textInput(['type' => 'password', 'autocomplete' => 'off']) ?>

<?php if ($passwordChanged): ?>
  <?= Alert::widget(['body' => 'Password changed successfully.', 'options' => ['class' => 'alert-primary show']]) ?>
<?php endif; ?>

<div class="form-group">
  <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php Pjax::end(); ?>

