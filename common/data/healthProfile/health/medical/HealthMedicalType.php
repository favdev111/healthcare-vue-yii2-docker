<?php

use common\models\healthProfile\health\medical\HealthMedicalType;

return [
    [
        'id' => HealthMedicalType::TYPE_SYMPTOM,
        'name' => 'symptom',
        'stringId' => 'symptoms',
    ],
    [
        'id' => HealthMedicalType::TYPE_MEDICAL_CONDITIONS,
        'name' => 'medical conditions',
        'stringId' => 'medicalConditions',
    ],
    [
        'id' => HealthMedicalType::TYPE_AUTO_IMMUNE,
        'name' => 'auto-immune diseases',
        'stringId' => 'autoImmuneDiseases',
    ],
    [
        'id' => HealthMedicalType::TYPE_ALLERGIES,
        'name' => 'allergies',
        'stringId' => 'allergies',
    ],
    [
        'id' => HealthMedicalType::TYPE_FOOD_INTOLERANCES,
        'name' => 'food intolerances',
        'stringId' => 'foodIntolerances',
    ],
    [
        'id' => HealthMedicalType::TYPE_LIFESTYLE_DIET,
        'name' => 'lifestyle diet',
        'stringId' => 'lifestyleDiet',
    ],
    [
        'id' => HealthMedicalType::TYPE_CURRENT_MEDICATIONS,
        'name' => 'current medications',
        'stringId' => 'currentMedications',
    ],
    [
        'id' => HealthMedicalType::TYPE_OTHER,
        'name' => 'other products, supplements, herbals, homeopathics',
        'stringId' => 'other',
    ],
    [
        'id' => HealthMedicalType::TYPE_HEALTH_GOAL,
        'name' => 'health goals',
        'stringId' => 'healthGoals',
    ],
    [
        'id' => HealthMedicalType::TYPE_HEALTH_CONCERN,
        'name' => 'health concerns',
        'stringId' => 'healthConcerns',
    ],
    [
        'id' => HealthMedicalType::TYPE_ALLERGIES_CATEGORY,
        'name' => 'allergies category',
        'stringId' => 'allergiesCategory',
    ],
    [
        'id' => HealthMedicalType::TYPE_FOOD_INTOLERANCES_CATEGORY,
        'name' => 'food intolerances category',
        'stringId' => 'foodIntolerancesCategory',
    ],
];
