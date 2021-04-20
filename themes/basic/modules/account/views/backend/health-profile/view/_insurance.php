<?php

/* @var $this yii\web\View */

/* @var $model \common\models\healthProfile\insurance\HealthProfileInsurance */

use yii\widgets\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        'isPrimary:boolean:Primary',
        'firstName',
        'lastName',
        'insuranceCompany.name:text:Insurance Company',
        'groupNumber',
        'policyNumber',
        'address',
        'locationZipCode.city.name:text:City',
        'locationZipCode.id:text:ZipCode',
        'dateOfBirth:date',
        'socialSecurityNumber',
    ]
]) ?>
