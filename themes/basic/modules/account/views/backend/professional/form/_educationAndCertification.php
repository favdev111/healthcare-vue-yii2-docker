<?php

use backend\components\widgets\ActiveForm;
use backend\components\widgets\content\Pjax;
use backend\components\widgets\googlePlace\GooglePlacesAutoComplete;
use common\helpers\AccountStatusHelper;
use kartik\date\DatePicker;
use yii\bootstrap4\Html;

/* @var $this yii\web\View */
/* @var $model \modules\account\models\forms\professional\educationCertification\EducationCertificationForm */
/* @var $form yii\widgets\ActiveForm */

?>

<?php $pjax = Pjax::begin([
        'id' => 'education-certification-pjax',
]); ?>

<?php $form = ActiveForm::begin([
        'id' => 'education-certification-form',
        'options' => ['data-pjax' => true, 'class' => 'row'],
]); ?>

<div class="col-md-6">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <h4 class="card-title">Education</h4>
                <?= $this->render('educationAndSertification/_education', ['form' => $form, 'model' => $model]) ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php Pjax::end(); ?>

