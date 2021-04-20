<?php

/* @var $this yii\web\View */
/* @var $model HealthProfile */

use common\models\healthProfile\health\medical\HealthMedicalType;
use common\models\healthProfile\HealthProfile;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\widgets\DetailView;

/**
 * Helper for print list of values
 * @param HealthMedicalType $healthMedicalType
 * @param string|array $columns
 * @param string|array $joinWith
 * @return Closure
 */
$value = static function (HealthMedicalType $healthMedicalType, string|array $columns, string|array $joinWith = []) {
    return static function (HealthProfile $healthProfile) use ($healthMedicalType, $joinWith, $columns) {
        $query = $healthProfile
            ->getHealthProfileHealthMedical($healthMedicalType)
            ->joinWith($joinWith);

        $provider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'sort' => false,
            'pagination' => false
        ]);

        if (is_string($columns)) {
            $columns = [$columns];
        }

        return GridView::widget([
            'dataProvider' => $provider,
            'tableOptions' => ['class' => 'table m-0'],
            'emptyText' => Yii::$app->formatter->nullDisplay,
            'pager' => false,
            'summary' => false,
            'showHeader' => false,
            'columns' => $columns
        ]);
    };
};

$attribute = static function ($label, $value) {
    return [
        'format' => 'html',
        'contentOptions' => ['class' => 'p-0'],
        'label' => $label,
        'value' => $value
    ];
};

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        $attribute(
            'Symptoms',
            $value(
                HealthMedicalType::findSymptom(),
                'symptom.name',
                'symptom',
            )
        ),
        $attribute(
            'Medical conditions',
            $value(
                HealthMedicalType::findMedicalConditions(),
                'medicalCondition.name',
                'medicalCondition',
            )
        ),
        $attribute(
            'Auto-immune diseases',
            $value(
                HealthMedicalType::findAutoImmune(),
                'autoimmuneDisease.name',
                'autoimmuneDisease',
            )
        ),
        $attribute(
            'Allergies',
            $value(
                HealthMedicalType::findAllergies(),
                'allergy.name',
                'allergy',
            )
        ),
        $attribute(
            'Food intolerances',
            $value(
                HealthMedicalType::findFoodIntolerances(),
                'foodIntolerance.name',
                'foodIntolerance',
            )
        ),
        $attribute(
            'Current medications',
            $value(
                HealthMedicalType::findCurrentMedications(),
                'text',
            )
        )
    ]
]) ?>
