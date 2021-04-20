<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \modules\account\models\backend\Review */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="review-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php if (!$model->lesson) : ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?php endif; ?>
    <?= $form->field($model, 'message')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'articulation')?>
    <?= $form->field($model, 'proficiency') ?>
    <?= $form->field($model, 'punctual') ?>

    <?= $form->field($model, 'hours')->textInput(['value' => 0]) ?>
    <?= $form->field($model, 'accounts')->textInput(['value' => 0]) ?>

    <?= $form->field($model, 'status')->dropDownList($model->statusTextList) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
