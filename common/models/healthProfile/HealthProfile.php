<?php

namespace common\models\healthProfile;

use Carbon\Carbon;
use common\components\ActiveRecord;
use common\components\behaviors\TimestampCarbonBehavior;
use common\listeners\healthProfile\AfterInsert;
use common\listeners\healthProfile\AfterUpdate;
use common\models\healthProfile\health\HealthProfileHealth;
use common\models\healthProfile\health\medical\HealthMedicalType;
use common\models\healthProfile\health\medical\HealthProfileHealthMedical;
use common\models\healthProfile\insurance\HealthProfileInsurance;
use common\models\query\HealthProfileQuery;
use common\models\Zipcode;
use modules\account\models\Account;
use Yii;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%health_profile}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property boolean $isMain
 * @property integer $relationshipId
 * @property string $firstName
 * @property string $lastName
 * @property string $phoneNumber
 * @property string $email
 * @property Carbon $birthday
 * @property string $gender
 * @property float $height
 * @property float $weight
 * @property string $zipcode
 * @property string $address
 * @property string $country
 * @property string $googlePlaceId
 * @property float $latitude
 * @property float $longitude
 * @property int $maritalStatusId
 * @property int $childrenCount
 * @property int $educationLevelId
 * @property string $occupation
 * @property string $employer
 * @property int $smoke
 * @property int $drink
 * @property string $otherSubstances
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 *
 * @property Account $account
 * @property-read \common\models\query\HealthProfileInsuranceQuery|HealthProfileInsurance $primaryInsurance
 * @property-read \common\models\query\HealthProfileInsuranceQuery|HealthProfileInsurance[] $insurances
 * @property HealthProfileHealth $healthProfileHealth
 * @property-read Zipcode $locationZipCode
 * @property HealthProfileHealthMedical[] $healthProfileHealthMedicals
 */
class HealthProfile extends ActiveRecord
{
    public $casts = [
        'isMain' => 'boolean',
        'height' => 'decimal:3',
        'weight' => 'decimal:3',
        'birthday' => 'date',
        'createdAt' => 'timestamp',
        'updatedAt' => 'timestamp',
        'deletedAt' => 'timestamp',
    ];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->on(self::EVENT_AFTER_INSERT, [Yii::createObject(AfterInsert::class), 'run']);
        $this->on(self::EVENT_AFTER_UPDATE, [Yii::createObject(AfterUpdate::class), 'run']);
        parent::init();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampCarbonBehavior::class,
            ],
            'softDelete' => [
                'class' => SoftDeleteBehavior::class,
                'softDeleteAttributeValues' => [
                    'deletedAt' => Carbon::now(),
                ],
                'replaceRegularDelete' => true
            ],
        ];
    }

    /**
     * @inheritdoc
     * @return HealthProfileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new HealthProfileQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocationZipCode()
    {
        return $this->hasOne(Zipcode::class, ['code' => 'zipcode']);
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            if ($this->isMain === null) {
                $this->isMain = false;
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * Gets query for [[HealthProfileInsurances]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\HealthProfileInsuranceQuery
     */
    public function getInsurances()
    {
        return $this->hasMany(HealthProfileInsurance::class, ['healthProfileId' => 'id'])
            ->orderBy(['isPrimary' => SORT_DESC]);
    }

    /**
     * Gets query for [[HealthProfileInsurances]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\HealthProfileInsuranceQuery
     */
    public function getPrimaryInsurance()
    {
        return $this->hasOne(HealthProfileInsurance::class, ['healthProfileId' => 'id'])
            ->andOnCondition(['isPrimary' => true]);
    }

    /**
     * Gets query for [[HealthProfileHealthMedicals]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\HealthProfileHealthMedicalQuery
     */
    public function getHealthProfileHealthMedicals()
    {
        return $this->hasMany(HealthProfileHealthMedical::className(), ['healthProfileId' => 'id']);
    }

    /**
     * Gets query for [[HealthProfileHealthMedicals]].
     *
     * @param HealthMedicalType $healthMedicalType
     * @return \yii\db\ActiveQuery|\common\models\query\HealthProfileHealthMedicalQuery|HealthProfileHealthMedical
     */
    public function getHealthProfileHealthMedical(HealthMedicalType $healthMedicalType)
    {
        return $this->hasOne(HealthProfileHealthMedical::className(), ['healthProfileId' => 'id'])
            ->onCondition(['medicalTypeId' => $healthMedicalType->id]);
    }

    /**
     * Gets query for [[HealthProfileHealth]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\HealthProfileHealthQuery
     */
    public function getHealthProfileHealth()
    {
        return $this->hasOne(HealthProfileHealth::className(), ['healthProfileId' => 'id']);
    }
}
