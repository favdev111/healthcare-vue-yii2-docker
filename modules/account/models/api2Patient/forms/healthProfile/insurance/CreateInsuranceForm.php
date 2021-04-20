<?php

namespace modules\account\models\api2Patient\forms\healthProfile\insurance;

use modules\account\models\api2Patient\entities\healthProfile\insurance\HealthProfileInsurance;
use Yii;

/**
 * Class CreateInsuranceForm
 * @package modules\account\models\api2Patient\forms\healthProfile\insurance
 */
class CreateInsuranceForm extends InsuranceForm
{
    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            [
                [
                    'insuranceCompanyId',
                    'groupNumber',
                    'policyNumber',
                    'googlePlaceId',
                    'dateOfBirth',
                    'firstName',
                    'lastName',
                    'socialSecurityNumber',
                    'isPrimary'
                ],
                'required'
            ],
        ];

        return array_merge($rules, parent::rules());
    }

    /**
     * @return HealthProfileInsurance|null |null
     * @throws \yii\base\InvalidConfigException
     */
    public function create(): ?HealthProfileInsurance
    {
        if (!$this->validate()) {
            return null;
        }

        return $this->buildHealthProfileInsurance(Yii::createObject(HealthProfileInsurance::class));
    }
}
