<?php

namespace modules\account\models\api2Patient\forms\healthProfile\insurance;

use common\models\healthProfile\HealthProfile;
use modules\account\models\api2Patient\entities\healthProfile\insurance\HealthProfileInsurance;

/**
 * Class UpdateInsuranceForm
 * @package modules\account\models\api2Patient\forms\healthProfile\insurance
 */
class UpdateInsuranceForm extends InsuranceForm
{
    /**
     * @var HealthProfileInsurance
     */
    protected $healthProfileInsurance;

    /**
     * UpdateInsuranceForm constructor.
     * @param HealthProfile $healthProfile
     * @param HealthProfileInsurance $healthProfileInsurance
     * @param array $config
     */
    public function __construct(
        HealthProfile $healthProfile,
        HealthProfileInsurance $healthProfileInsurance,
        array $config = []
    ) {
        $this->healthProfileInsurance = $healthProfileInsurance;
        parent::__construct($healthProfile, $config);
    }

    /**
     * @return array|array[]
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_DEFAULT] = [
            'insuranceCompanyId',
            'groupNumber',
            'policyNumber',
            'googlePlaceId',
            'dateOfBirth',
            'firstName',
            'lastName',
            'socialSecurityNumber',
        ];
        return $scenarios;
    }

    /**
     * @return HealthProfileInsurance|null
     * @throws \yii\base\InvalidConfigException|\yii\base\ErrorException
     */
    public function update(): ?HealthProfileInsurance
    {
        if (!$this->validate()) {
            return null;
        }

        return $this->buildHealthProfileInsurance($this->healthProfileInsurance);
    }
}
