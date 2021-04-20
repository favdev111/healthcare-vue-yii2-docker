<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model modules\account\models\backend\AccountClientSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="account-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'publicId') ?>

    <?= $form->field($model, 'email') ?>

    <?= $form->field($model, 'passwordHash') ?>

    <?= $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'banReason') ?>

    <?php // echo $form->field($model, 'isEmailConfirmed') ?>

    <?php // echo $form->field($model, 'roleId') ?>

    <?php // echo $form->field($model, 'createdAt') ?>

    <?php // echo $form->field($model, 'updatedAt') ?>

    <?php // echo $form->field($model, 'createdIp') ?>

    <?php // echo $form->field($model, 'searchHide') ?>

    <?php // echo $form->field($model, 'countSendNotification') ?>

    <?php // echo $form->field($model, 'countSendCardErrorNotification') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
