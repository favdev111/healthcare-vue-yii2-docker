<?php

use common\helpers\Role;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Account */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="account-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'firstName')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'lastName')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'roleId')->dropDownList($model->getDropDownRoles()) ?>
    <?= $form->field($model, 'newPassword')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'newPasswordConfirm')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'isActive')->checkbox(['checked ' => '0']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
