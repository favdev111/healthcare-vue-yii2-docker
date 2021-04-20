<?php

use api2\helpers\DoctorType;
use api2\helpers\EnrolledTypes;
use api2\helpers\ProfessionalType;
use backend\components\widgets\inputs\Select2Ajax;
use common\helpers\AccountStatusHelper;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use modules\account\helpers\ConstantsHelper;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use yii\helpers\Html;

/**
 * @var $model modules\account\models\backend\AccountProfessionalSearch
 * @var $this \yii\web\View
 */

$selectData = SubjectOrCategory::getSelectizeData(null, true);

$promptOptions = [
        'prompt' => [
                'text' => 'Select...',
                'options' => [
                        'selected' => true,
                ],
        ],
]

?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Search parameters</h4>
                <button
                        class="btn btn-primary"
                        type="button"
                        data-toggle="collapse"
                        data-target="#collapse"
                        aria-expanded="false">
                    Open
                </button>
            </div>
            <div class="card-body collapse in" id="collapse">
                <?php $form = ActiveForm::begin([
                        'type' => ActiveForm::TYPE_VERTICAL,
                        'enableClientScript' => false,
                        'method' => 'get',
                ]); ?>

                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'professionalTypeId')
                                ->dropDownList(ProfessionalType::LABELS, $promptOptions) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'doctorTypeId')
                                ->dropDownList(DoctorType::DOCTORS_SPECIALIZATION_LABELS, $promptOptions) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'phoneNumber')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'status')
                                ->dropDownList(AccountStatusHelper::getAllStatuses(), $promptOptions) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'firstName')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'lastName')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'gender')
                                ->dropDownList(ConstantsHelper::gender(), $promptOptions) ?>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="bd-callout border-top border-bottom">
                            <div class="card-body px-0">
                                <h5 class="card-title">Specialties</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?= $form->field($model, 'healthTests')->widget(Select2Ajax::class, [
                                                'data' => $model->getSelectedItems('healthTests'),
                                                'route' => '/health-tests/ajax-search'
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?= $form->field($model, 'symptoms')->widget(Select2Ajax::class, [
                                                'data' => $model->getSelectedItems('symptoms'),
                                                'route' => '/symptoms/ajax-search'
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?= $form->field($model, 'medicalConditions')->widget(Select2Ajax::class, [
                                                'data' => $model->getSelectedItems('medicalConditions'),
                                                'route' => '/medical-conditions/ajax-search',
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?= $form->field($model, 'autoimmuneDiseases')->widget(Select2Ajax::class, [
                                                'data' => $model->getSelectedItems('autoimmuneDiseases'),
                                                'route' => '/autoimmune-diseases/ajax-search',
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?= $form->field($model, 'healthGoals')->widget(Select2Ajax::class, [
                                                'data' => $model->getSelectedItems('healthGoals'),
                                                'route' => '/health-goals/ajax-search',
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <?= $form->field($model, 'telehealthStates')->widget(Select2Ajax::class, [
                                'data' => $model->getSelectedItems('telehealthStates'),
                                'route' => '/states/ajax-search',
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'currentlyEnrolled')
                                ->dropDownList(EnrolledTypes::LABELS, [
                                        'prompt' => [
                                                'text' => 'Medicare / Medicaid...',
                                                'options' => [
                                                        'selected' => true,
                                                ],
                                        ],
                                ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'zipCode')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'createdAt')->label('Created after')->widget(DatePicker::class, [
                                'type' => DatePicker::TYPE_INPUT,
                                'pluginOptions' => [
                                        'ignoreReadonly' => true,
                                        'autoclose' => true,
                                        'removeButton' => true,
                                        'format' => 'mm/dd/yyyy'
                                ]
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'keyword')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-form-label text-md-right"></label>
                    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
