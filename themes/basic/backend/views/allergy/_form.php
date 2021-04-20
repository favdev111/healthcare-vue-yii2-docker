<?php

use backend\components\widgets\inputs\Select2Ajax;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\health\allergy\Allergy */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="allergy-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'allergyCategoryId')->widget(Select2Ajax::class, [
            'data' => $model->allergyCategory ? [$model->allergyCategory->id => $model->allergyCategory->name] : [],
            'route' => '/allergy-categories/ajax-search',
            'options' => [
                    'multiple' => false,
            ],
            'pluginOptions' => [
                    'minimumInputLength' => 1
            ]
    ])->label('Allergy category') ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
