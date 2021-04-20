<?php

namespace common\models\healthProfile\insurance;

use backend\models\InsuranceCompany;
use common\models\healthProfile\HealthProfile;
use common\models\query\HealthProfileInsuranceQuery;
use common\models\query\HealthProfileQuery;
use DateTime;
use modules\account\models\api\ZipCode;
use yii\behaviors\AttributeTypecastBehavior;

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
class HealthProfileInsurance extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'health_profile_insurance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['insuranceCompanyId', 'locationZipCodeId', 'healthProfileId'], 'integer'],
            [['firstName', 'lastName', 'isPrimary', 'healthProfileId'], 'required'],
            ['dateOfBirth', 'date', 'format' => 'php:Y-m-d'],
            [['groupNumber', 'policyNumber', 'socialSecurityNumber'], 'string', 'max' => 50],
            [['address', 'googlePlaceId', 'firstName', 'lastName'], 'string', 'max' => 255],
            [['healthProfileId', 'isPrimary'], 'unique', 'targetAttribute' => ['healthProfileId', 'isPrimary']],
            [['healthProfileId'], 'exist', 'skipOnError' => true, 'targetClass' => HealthProfile::className(), 'targetAttribute' => ['healthProfileId' => 'id']],
            [['insuranceCompanyId'], 'exist', 'skipOnError' => true, 'targetClass' => InsuranceCompany::className(), 'targetAttribute' => ['insuranceCompanyId' => 'id']],
            [['locationZipCodeId'], 'exist', 'skipOnError' => true, 'targetClass' => ZipCode::className(), 'targetAttribute' => ['locationZipCodeId' => 'id']],
            ['isPrimary', 'boolean']
        ];
    }

    /**
     * @return array|array[]
     */
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'isPrimary' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterValidate' => true,
                'typecastBeforeSave' => true,
                'typecastAfterSave' => true,
                'typecastAfterFind' => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'insuranceCompanyId' => 'Insurance Company ID',
            'groupNumber' => 'Group Number',
            'policyNumber' => 'Policy Number',
            'locationZipCodeId' => 'Location Zip Code ID',
            'address' => 'Address',
            'googlePlaceId' => 'Google Place ID',
            'dateOfBirth' => 'Date Of Birth',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'socialSecurityNumber' => 'Social Security Number',
            'isPrimary' => 'Is Primary',
            'healthProfileId' => 'Health Profile ID',
        ];
    }

    /**
     * Gets query for [[HealthProfile]].
     *
     * @return \yii\db\ActiveQuery|HealthProfileQuery
     */
    public function getHealthProfile()
    {
        return $this->hasOne(HealthProfile::className(), ['id' => 'healthProfileId']);
    }

    /**
     * Gets query for [[InsuranceCompany]].
     *
     * @return \yii\db\ActiveQuery|InsuranceCompanyQuery
     */
    public function getInsuranceCompany()
    {
        return $this->hasOne(InsuranceCompany::className(), ['id' => 'insuranceCompanyId']);
    }

    /**
     * Gets query for [[LocationZipCode]].
     *
     * @return \yii\db\ActiveQuery|LocationZipcodeQuery
     */
    public function getLocationZipCode()
    {
        return $this->hasOne(ZipCode::className(), ['id' => 'locationZipCodeId']);
    }

    /**
     * {@inheritdoc}
     * @return HealthProfileInsuranceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new HealthProfileInsuranceQuery(get_called_class());
    }

    /**
     * @param DateTime $dateTime
     */
    public function setDateOfBirth(DateTime $dateTime): void
    {
        $this->dateOfBirth = $dateTime->format('Y-m-d');
    }
}
