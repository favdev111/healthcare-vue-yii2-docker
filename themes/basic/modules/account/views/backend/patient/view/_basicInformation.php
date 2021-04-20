<?php

/* @var $this yii\web\View */
/* @var $model \common\models\healthProfile\HealthProfile */

use yii\widgets\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        'firstName',
        'lastName',
        'phoneNumber',
        'zipcode',
        'email:email:Email',
        'birthday:date',
        'gender',
        'height',
        'weight',
        'address',
        'country',
        'city',
    ]
]) ?>
