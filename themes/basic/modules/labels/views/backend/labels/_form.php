<?php

use kartik\color\ColorInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

assert($this instanceof yii\web\View);
assert($model instanceof modules\labels\models\Labels);
?>

<div class="labels-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList($statusList, ['prompt' => 'Choose status']); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'color')->widget(ColorInput::class, [
                'options' => ['placeholder' => 'Select color ...']
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'categoryId')->dropDownList($categories, ['prompt' => 'Choose category']) ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ? 'Create' : 'Update',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<style>
    #labels-color-cont {
        width: 70px !important;
    }
    .spectrum-group.kv-type-text.is-bs3{
        width:100%;
    }
</style>
