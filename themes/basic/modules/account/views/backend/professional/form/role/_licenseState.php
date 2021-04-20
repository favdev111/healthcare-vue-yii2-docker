<?php

/* @var yii\web\View $this */
/* @var \modules\account\models\forms\professional\role\ProfessionalRoleForm $model */
/* @var yii\widgets\ActiveForm $form */

use backend\components\widgets\dynamicWidget\DynamicFormWidget;

$items = $model->licenceStateForms;

$name = 'license-state';
$widgetBody = 'container-' . $name;
$widgetItem = 'item-' . $name;
$insertButton = 'add-' . $name;
$deleteButton = 'delete-' . $name;

$widgetContainer = 'licenseStateContainer';

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
                'stateId',
                'license',
        ]
]); ?>

<div class="row">
    <div class="col-sm-12">

        <div class="<?= $widgetBody ?>">

            <?php foreach ($items as $indexForm => $licenceStateForm): ?>

                <div class="row <?= $widgetItem ?>">

                    <?php if (isset($licenceStateForm->id) && $licenceStateForm->id !== null) {
                        echo $form->field($licenceStateForm, "[{$indexForm}]id")->hiddenInput()->label(false);
                    }
                    ?>

                    <div class="col-sm-5">
                        <?= $form->field($licenceStateForm, "[{$indexForm}]stateId")->dropDownList($model->option->states, [
                                'prompt' => [
                                        'text' => 'State...',
                                        'options' => [
                                                'disabled' => true,
                                                'selected' => true,
                                        ],
                                ],
                        ]) ?>
                    </div>

                    <div class="col-sm-5">
                        <?= $form->field($licenceStateForm, "[{$indexForm}]license") ?>
                    </div>

                    <div class="col-sm-2 mt-5">
                        <button type="button" class="<?= $deleteButton ?> btn btn-danger btn-sm remove-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="<?= $insertButton ?> btn btn-primary btn-sm pull-right">
            <i class="fas fa-plus-circle"></i> Add state
        </button>
    </div>
</div>

<?php DynamicFormWidget::end(); ?>
