<?php

use backend\components\widgets\ActiveForm;
use backend\components\widgets\content\Pjax;
use backend\components\widgets\googlePlace\GooglePlacesAutoComplete;
use common\helpers\AccountStatusHelper;
use kartik\date\DatePicker;
use yii\bootstrap4\Html;

/* @var $this yii\web\View */
/* @var $model \modules\account\models\forms\professional\ProfessionalUpdateForm */
/* @var $form yii\widgets\ActiveForm */

$statuses = AccountStatusHelper::statuesDefaultListForDropdown();

$jsGooglePlace = <<<JS
function onPlaceChanged() {
  const place = autocomplete.getPlace();
  $('#zipCode').val('');
  $('[attribute=placeId]').val(place.place_id);

  for (var i = 0; i < place.address_components.length; i++) {
    for (var j = 0; j < place.address_components[i].types.length; j++) {
      if (place.address_components[i].types[j] == "postal_code") {
          $('#zipCode').val(place.address_components[i].long_name);
      }
    }
  }
}
JS;

?>

<?php $pjax = Pjax::begin([
        'id' => 'professional-pjax',
]); ?>

<?php $form = ActiveForm::begin([
        'id' => 'professional-form',
        'options' => ['data-pjax' => true, 'class' => 'row'],
]); ?>

<div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <div class="row">

                <div class="col-md-6">
                    <?= $form->field($model, 'firstName')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'lastName')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'phoneNumber')->widget(\yii\widgets\MaskedInput::classname(), [
                            'mask' => '(999) 999-9999',
                            'clientOptions' => [
                                    'removeMaskOnSubmit' => true,
                                    'autoUnmask' => true,
                                    'placeholder' => ' ',
                            ],
                            'options' => [
                                    'class' => 'form-control material-style',
                            ],
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'genderId')->dropDownList($model->option->gender, [
                            'prompt' => [
                                    'text' => 'Gender...',
                                    'options' => [
                                            'disabled' => true,
                                    ],
                            ],
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'dateOfBirth')->widget(DatePicker::class, [
                            'type' => DatePicker::TYPE_INPUT,
                            'pluginOptions' => [
                                    'ignoreReadonly' => true,
                                    'autoclose' => true,
                                    'removeButton' => true,
                                    'format' => 'mm/dd/yyyy',
                            ],
                    ]) ?>
                </div>

                <div class="col-md-9">
                    <?= $form->field($model, 'address')->widget(GooglePlacesAutoComplete::class, [
                            'onPlaceChanged' => $jsGooglePlace,
                            'autocompleteOptions' => [
                                    'types' => ['address'],
                                    'componentRestrictions' => [
                                            'country' => 'us',
                                    ]
                            ],
                    ]) ?>
                    <?= $form->field($model, 'placeId')->hiddenInput(['attribute' => 'placeId'])->label(false) ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'zipCode')->textInput(['maxlength' => true, 'disabled' => true, 'id' => 'zipCode']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">System information</h5>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'searchHide')->checkbox() ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'publicHide')->checkbox() ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'statusId')->dropDownList($statuses['items'], [
                            'prompt' => [
                                    'text' => 'Status...',
                                    'options' => [
                                            'value' => 'prompt',
                                            'class' => 'prompt-class',
                                            'disabled' => true,
                                    ],
                            ],
                            'options' => $statuses['options'],
                    ]) ?>
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

