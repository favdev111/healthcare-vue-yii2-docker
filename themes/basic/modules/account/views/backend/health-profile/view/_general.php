<?php

/* @var $this yii\web\View */

/* @var $model HealthProfile */

use common\models\healthProfile\HealthProfile;
use modules\account\helpers\ConstantsHelper;
use yii\helpers\ArrayHelper;
use yii\widgets\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        'isMain:boolean:Owner',
        'firstName',
        'lastName',
        [
            'label' => 'Relationship',
            'value' => static function (HealthProfile $healthProfile) {
                return ArrayHelper::getValue(ConstantsHelper::HEALTH_PROFILE_RELATIONSHIPS, $healthProfile->relationshipId);
            }
        ],
        'phoneNumber',
        'email:email',
        'birthday:date',
        'gender',
        'height',
        'weight',
        'zipcode',
        'address',
        'country',
        'locationZipCode.city.name:text:City',
        'latitude',
        'longitude',
        [
            'label' => 'Relationship',
            'value' => function (HealthProfile $healthProfile) {
                return ArrayHelper::getValue(ConstantsHelper::MATERIAL_STATUS, $healthProfile->maritalStatusId);
            }
        ],
        'childrenCount',
        [
            'label' => 'Relationship',
            'value' => function (HealthProfile $healthProfile) {
                return ArrayHelper::getValue(ConstantsHelper::EDUCATION_LEVEL, $healthProfile->educationLevelId);
            }
        ],
        'occupation',
        'employer',
        'createdAt',
        'updatedAt',
    ]
]) ?>
