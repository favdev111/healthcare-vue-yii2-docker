<?php

namespace modules\account\models\api2Patient\entities\healthProfile\insurance;

use common\models\healthProfile\HealthProfile;
use modules\account\models\api\ZipCode;
use modules\account\models\ar\InsuranceCompany;

/**
 * This is the model class for table "health_profile_insurance".
 *
 * @property int $id
 * @property int|null $insuranceCompanyId
 * @property string|null $groupNumber
 * @property string|null $policyNumber
 * @property int|null $locationZipCodeId
 * @property string|null $address
 * @property string|null $googlePlaceId
 * @property string $dateOfBirth
 * @property string $firstName
 * @property string $lastName
 * @property string|null $socialSecurityNumber
 * @property int $isPrimary
 * @property int $healthProfileId
 *
 * @property HealthProfile $healthProfile
 * @property InsuranceCompany $insuranceCompany
 * @property ZipCode $locationZipCode
 */
class HealthProfileInsurance extends \common\models\healthProfile\insurance\HealthProfileInsurance
{
    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'firstName',
            'lastName',
            'socialSecurityNumber',
            'isPrimary',
            'policyNumber',
            'groupNumber',
            'dateOfBirth',
            'insuranceCompany' => function () {
                if (!$this->insuranceCompany) {
                    return null;
                }
                return [
                    'id' => $this->insuranceCompany->id,
                    'name' => $this->insuranceCompany->name,
                ];
            },
            'location' => function () {
                if (!$this->googlePlaceId) {
                    return null;
                }
                return [
                    'zipCode' => $this->locationZipCode->code,
                    'city' => $this->locationZipCode->city->name,
                    'address' => $this->address,
                    'googlePlaceId' => $this->googlePlaceId,
                ];
            },
        ];
    }

    /**
     * Gets query for [[HealthProfile]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\HealthProfileQuery
     */
    public function getHealthProfile()
    {
        return $this->hasOne(HealthProfile::className(), ['id' => 'healthProfileId']);
    }

    /**
     * Gets query for [[InsuranceCompany]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\InsuranceCompanyQuery
     */
    public function getInsuranceCompany()
    {
        return $this->hasOne(InsuranceCompany::className(), ['id' => 'insuranceCompanyId']);
    }
}
