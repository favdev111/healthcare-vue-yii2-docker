<?php

use api2\helpers\ProfessionalType;
use backend\components\widgets\ActiveForm;
use backend\components\widgets\content\Pjax;
use backend\components\widgets\inputs\Select2Ajax;
use yii\bootstrap4\Html;

/* @var $this yii\web\View */
/* @var $model \modules\account\models\forms\professional\role\ProfessionalRoleForm */
/* @var $form yii\widgets\ActiveForm */

$professionalTypeDoctor = ProfessionalType::DOCTOR;

?>

<?php $pjax = Pjax::begin([
        'id' => 'professional-role-pjax',
]); ?>

<?php $form = ActiveForm::begin([
        'id' => 'professional-role-form',
        'options' => ['data-pjax' => true, 'class' => 'row'],
]); ?>
<div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <div class="row">

                <div class="col-md-8">
                    <?= $form->field($model, 'professionalTypeId')->dropDownList($model->option->professionalTypes, [
                            'prompt' => [
                                    'text' => 'Professional type...',
                                    'options' => [
                                            'disabled' => true,
                                    ],
                            ],
                            'onchange' => "changedProfessionalType()",
                            'data-attribute' => "professionalTypeId",
                    ]) ?>
                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'yearsOfExperience') ?>
                </div>
            </div>
            <div id="doctorBlock" class="row">
                <div class="col-md-8">
                    <?= $form->field($model, 'doctorTypeId')->dropDownList($model->option->doctorTypes, [
                            'prompt' => [
                                    'text' => 'Doctor type...',
                                    'options' => [
                                            'selected' => true,
                                            'disabled' => true,
                                    ],
                            ],
                    ]) ?>
                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'npiNumber') ?>
                </div>
            </div>
            <?= $this->render('role/_licenseState', ['form' => $form, 'model' => $model]) ?>

            <div class="row mt-2">
                <div class="col-md-12">
                    <?= $form->field($model, 'telehealthStates')->widget(Select2Ajax::class, [
                            'data' => $model->getTelehealthStatesData(),
                            'route' => '/states/ajax-search',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'hasDisciplinaryAction')->checkbox([
                            'onchange' => "changedDisciplinaryAction()",
                            'data-attribute' => "hasDisciplinaryAction",
                    ]) ?>
                </div>
                <div id="disciplinaryActionText" class="col-md-12">
                    <?= $form->field($model, 'disciplinaryActionText')->textarea(['rows' => 3]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php Pjax::end(); ?>

<script type="text/javascript">
    function changedProfessionalType() {
        let doctorTypeId = <?=$professionalTypeDoctor?>;
        if ($('[data-attribute=professionalTypeId]').val() == doctorTypeId) {
            $('#doctorBlock').show()
        } else {
            $('#doctorBlock').hide()
        }
    }

    function changedDisciplinaryAction() {
        if ($('[data-attribute=hasDisciplinaryAction]').is(':checked')) {
            $('#disciplinaryActionText').show()
        } else {
            $('#disciplinaryActionText').hide()
        }
    }

    $(document).ready(function () {
        changedDisciplinaryAction();
        changedProfessionalType();
    });
</script>
