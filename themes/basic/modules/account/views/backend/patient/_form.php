<?php

use common\helpers\AccountStatusHelper;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \modules\account\models\forms\patient\PatientUpdateForm */
/* @var $form yii\widgets\ActiveForm */

$statuses = AccountStatusHelper::statuesDefaultListForDropdown();

?>

<div class="account-form col-md-6">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'firstName')->textInput(['maxlength' => true]) ?>
  <?= $form->field($model, 'lastName')->textInput(['maxlength' => true]) ?>
  <?= $form->field($model, 'zipCode')->textInput(['maxlength' => true]) ?>

  <?= $form->field($model, 'statusId')->dropDownList($statuses['items'], [
      'prompt' => [
          'text' => 'Status...',
          'options' => [
              'value' => 'prompt',
              'class' => 'prompt-class',
              'disabled' => true,
          ],
      ],
      'options' => $statuses['options'],
  ]) ?>

  <?= $form->field($model, 'phoneNumber')->widget(\yii\widgets\MaskedInput::classname(), [
      'mask' => '(999) 999-9999',
      'clientOptions' => [
          'removeMaskOnSubmit' => true,
          'autoUnmask' => true,
          'placeholder' => ' ',
      ],
      'options' => [
          'class' => 'form-control material-style',
      ],
  ]) ?>

  <div class="">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
