<?php

/* @var $this yii\web\View */

/* @var $model HealthProfile */

use common\models\healthProfile\health\medical\HealthMedicalType;
use common\models\healthProfile\HealthProfile;
use yii\helpers\ArrayHelper;
use yii\widgets\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        'healthProfileHealth.drink.name:text:Drink',
        'healthProfileHealth.smoke.name:text:Smoke',
        'healthProfileHealth.otherSubstanceText:text:Other substance',
    ]
]) ?>
