<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\assets\AppAsset;

$frontendAsset = AppAsset::register($this);

/* @var $this yii\web\View */
/* @var $model modules\account\models\backend\Account */
/* @var $form yii\widgets\ActiveForm */
\common\helpers\FormError::show($model);
?>

<div class="account-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php $form->errorSummary($model) ?>


    <?= $form->field($model, 'firstName')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'lastName')->textInput(['maxlength' => true]) ?>
    <?php foreach ($model->emails as $key => $email): ?>
        <?= $form->field($email, "[$key]email")->textInput(['maxlength' => true])->label($email->isPrimary ? 'Primary Email' : 'Email ' . ($key + 1)) ?>
        <?= $form->field($email, "[$key]isPrimary")->hiddenInput(['value' => $email->isPrimary])->label(false); ?>
    <?php endforeach; ?>
    <?= $this->render('@themes/basic/common/views/parts/_profile_place_id.php', ['profile' => $model]) ?>
    <?= $form->field($account->profile, 'zipCode')->textInput(['maxlength' => true, 'disabled' => true, 'id' => 'profile-zipcode']) ?>
    <?= $form->field($account->profile, 'address')->textInput(['maxlength' => true, 'id' => 'profile-address']) ?>

    <?php foreach ($model->phoneNumbers as $key => $phoneNumber): ?>
        <?= $form->field($phoneNumber, "[$key]isPrimary")->hiddenInput(['value' => $phoneNumber->isPrimary])->label(false); ?>
        <?=
        $form->field($phoneNumber, "[$key]phoneNumber")->widget(\yii\widgets\MaskedInput::class, [
            'mask' => '(999) 999-9999',
            'clientOptions' => [
                'removeMaskOnSubmit' => true,
                'autoUnmask' => true,
                'placeholder' => ' ',
            ],
            'options' => [
                'class' => 'form-control material-style',
            ],
        ])->label($phoneNumber->isPrimary ? 'Primary Phone number' : 'Phone number ' . ($key + 1));
        ?>
    <?php endforeach; ?>

    <div class="form-group">
        <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

