<?php

use backend\components\widgets\ActiveForm;
use backend\components\widgets\content\Pjax;
use kartik\select2\Select2;
use yii\bootstrap4\Html;

/* @var $this yii\web\View */
/* @var $model \modules\account\models\forms\professional\specifications\ProfessionalSpecificationsForm */
/* @var $form yii\widgets\ActiveForm */

$select2Config = [
        'theme' => Select2::THEME_DEFAULT,
        'showToggleAll' => false,
        'size' => Select2::SMALL,
        'options' => [
                'placeholder' => 'Select a value ...',
                'multiple' => true,
        ],
];

?>

<?php $pjax = Pjax::begin([
        'id' => 'professional-specifications-pjax',
]); ?>

<?php $form = ActiveForm::begin([
        'id' => 'professional-specifications-form',
        'options' => ['data-pjax' => true, 'class' => 'row'],
]); ?>

<div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <?= $form->field($model, 'healthTests')->widget(Select2::class, array_merge([
                    'data' => $model->option->healthTests,
            ], $select2Config)) ?>

            <?= $form->field($model, 'symptoms')->widget(Select2::class, array_merge([
                    'data' => $model->option->symptoms,
            ], $select2Config)) ?>

            <?= $form->field($model, 'medicalConditions')->widget(Select2::class, array_merge([
                    'data' => $model->option->symptoms,
            ], $select2Config)) ?>

            <?= $form->field($model, 'autoimmuneDiseases')->widget(Select2::class, array_merge([
                    'data' => $model->option->symptoms,
            ], $select2Config)) ?>

            <?= $form->field($model, 'healthGoals')->widget(Select2::class, array_merge([
                    'data' => $model->option->symptoms,
            ], $select2Config)) ?>
        </div>
    </div>
</div>

<div class="col-md-12">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php Pjax::end(); ?>
