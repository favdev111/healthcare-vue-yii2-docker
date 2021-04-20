<?php

/* @var yii\web\View $this */
/* @var \modules\account\models\forms\professional\educationCertification\EducationCertificationForm $model */

/* @var yii\widgets\ActiveForm $form */

use backend\components\widgets\dynamicWidget\DynamicFormWidget;
use backend\components\widgets\inputs\Select2Ajax;

$items = $model->educationForms;

$name = 'education';
$widgetBody = 'container-' . $name;
$widgetItem = 'item-' . $name;
$insertButton = 'add-' . $name;
$deleteButton = 'delete-' . $name;

$widgetContainer = 'educationContainer';

?>

<?php DynamicFormWidget::begin([
        'widgetContainer' => $widgetContainer,
        'widgetBody' => ".{$widgetBody}",
        'widgetItem' => ".{$widgetItem}",
        'limit' => 30,
        'min' => 1,
        'insertButton' => ".{$insertButton}",
        'deleteButton' => ".{$deleteButton}",
        'model' => $items[0],
        'formId' => $form->id,
        'formFields' => [
                'educationCollageId',
                'educationDegreeId',
                'graduated',
        ]
]); ?>

<div class="row">
    <div class="col-sm-12">

        <div class="<?= $widgetBody ?>">

            <?php foreach ($items as $indexForm => $educationForm): ?>

                <div class="row <?= $widgetItem ?>">

                    <?php if (isset($educationForm->id) && $educationForm->id !== null) {
                        echo $form->field($educationForm, "[{$indexForm}]id")->hiddenInput()->label(false);
                    }
                    ?>

                    <div class="col-sm-12">
                        <?= $form->field($educationForm, "[{$indexForm}]educationCollageId")->widget(Select2Ajax::class, [
                                'pluginOptions' => [
                                        'minimumInputLength' => 1,
                                        'allowClear' => false,
                                ],
                                'options' => [
                                        'multiple' => false,
                                ],
                                'route' => '/education-collage/ajax-search',
                        ]) ?>
                    </div>

                    <div class="col-sm-12">
                        <?= $form->field($educationForm, "[{$indexForm}]educationDegreeId")->dropDownList($model->option->educationDegree, [
                                'prompt' => [
                                        'text' => 'Degree...',
                                        'options' => [
                                                'disabled' => true,
                                                'selected' => true,
                                        ],
                                ],
                        ]) ?>
                    </div>

                    <div class="col-sm-12">
                        <?= $form->field($educationForm, "[{$indexForm}]graduated") ?>
                    </div>

                    <div class="col-sm-12">
                        <button type="button" class="<?= $deleteButton ?> btn btn-danger btn-sm remove-btn">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
        <div class="row mt-3">
            <div class="col-sm-12">
                <button type="button" class="<?= $insertButton ?> btn btn-primary btn-sm pull-right">
                    <i class="fas fa-plus-circle"></i> Add education
                </button>
            </div>
        </div>
    </div>
</div>

<?php DynamicFormWidget::end(); ?>
