<?php

/* @var $this yii\web\View */
/* @var $model Account */

use modules\account\models\backend\Account;
use yii\widgets\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        'profile.firstName:text:First name',
        'profile.lastName:text:Last name',
        'profile.phoneNumber:text:Phone number',
        'profile.genderName:text:Gender',
        'profile.dateOfBirth:text:Date of birth',
        'profile.address:text:Address',
        'profile.zipCode:text:Zip code',
    ]
]) ?>

