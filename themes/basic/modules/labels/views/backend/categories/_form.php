<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

assert($this instanceof yii\web\View);
assert($model instanceof modules\labels\models\LabelsCategory);
?>

<div class="labels-category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ? 'Create' : 'Update',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
