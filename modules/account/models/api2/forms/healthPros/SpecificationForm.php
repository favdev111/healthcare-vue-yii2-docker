<?php

namespace modules\account\models\api2\forms\healthPros;

use api2\components\models\forms\ApiBaseForm;
use modules\account\models\api2\AccountAutoimmuneDisease;
use modules\account\models\api2\AccountHealthGoal;
use modules\account\models\api2\AccountHealthTest;
use modules\account\models\api2\AccountMedicalCondition;
use modules\account\models\api2\AccountSymptom;
use modules\account\models\api2\AutoimmuneDisease;
use modules\account\models\api2\HealthGoal;
use modules\account\models\api2\HealthTest;
use modules\account\models\api2\MedicalCondition;
use modules\account\models\api2\Symptom;

class SpecificationForm extends ApiBaseForm
{
    public $healthTests;
    public $symptoms;
    public $medicalConditions;
    public $autoimmuneDiseases;
    public $healthGoals;

    protected $atLeastOneRequiredResult;

    public const ATTR_CLASSES = [
        'healthTests' => HealthTest::class,
        'symptoms' => Symptom::class,
        'medicalConditions' => MedicalCondition::class,
        'autoimmuneDiseases' => AutoimmuneDisease::class,
        'healthGoals' => HealthGoal::class
    ];

    public const ATTR_RELATION_CLASSES = [
        'healthTests' => AccountHealthTest::class,
        'symptoms' => AccountSymptom::class,
        'medicalConditions' => AccountMedicalCondition::class,
        'autoimmuneDiseases' => AccountAutoimmuneDisease::class,
        'healthGoals' => AccountHealthGoal::class
    ];

    public const ATTR_ID_FIELDS = [
        'healthTests' => 'healthTestId',
        'symptoms' => 'symptomId',
        'medicalConditions' => 'medicalConditionId',
        'autoimmuneDiseases' => 'autoimmuneDiseaseId',
        'healthGoals' => 'healthGoalId'
    ];

    public function attributeLabels()
    {
        return [
            'healthTests' => 'Health Test',
            'symptoms' => 'Symptom',
            'medicalConditions' => 'Medical Condition',
            'autoimmuneDiseases' => 'Autoimmune Disease',
            'healthGoals' => 'Health Goal'
        ];
    }


    protected function isEntityExists(string $class, int $id): bool
    {
        return $class::find()->andWhere(['id' => $id])->exists();
    }

    public function rules()
    {
        return [
            [
                [
                    'healthTests',
                    'symptoms',
                    'medicalConditions',
                    'autoimmuneDiseases',
                    'healthGoals'
                ],
                'entityExistsValidator',
                'skipOnEmpty' => 'false'
            ]
        ];
    }

    public function beforeValidate()
    {
        $this->atLeastOneRequiredValidator();

        return parent::beforeValidate();
    }

    public function atLeastOneRequiredValidator()
    {
        if (! empty($this->atLeastOneRequiredResult)) {
            return true;
        }

        if (
            empty($this->healthTests)
            && empty($this->symptoms)
            && empty($this->medicalConditions)
            && empty($this->autoimmuneDiseases)
            && empty($this->healthGoals)
        ) {
            $this->addError(
                'healthTests, symptoms, medicalConditions, autoimmuneDiseases, healthGoals',
                'At least one of the fields should be provided.'
            );
            $this->atLeastOneRequiredResult = false;
        }
    }

    public function entityExistsValidator($attribute)
    {
        foreach ($this->$attribute as $item) {
            if (
                ! $this->isEntityExists(
                    self::ATTR_CLASSES[$attribute],
                    $item[self::ATTR_ID_FIELDS[$attribute]]
                )
            ) {
                $this->addError($attribute, $this->getAttributeLabel($attribute) . ' is invalid');
            }
        }
    }
}
