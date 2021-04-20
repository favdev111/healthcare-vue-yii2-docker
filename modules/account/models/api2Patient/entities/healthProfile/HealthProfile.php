<?php

namespace modules\account\models\api2Patient\entities\healthProfile;

use modules\account\models\api2Patient\entities\healthProfile\health\HealthProfileHealth;
use modules\account\models\api2Patient\entities\healthProfile\health\medical\HealthProfileHealthMedical;
use modules\account\models\api2Patient\entities\healthProfile\insurance\HealthProfileInsurance;
use modules\account\responses\HealthProfile as ResponseHealthProfile;
use Yii;

/**
 * Class HealthProfile
 * @package modules\account\models\api2Patient\entities\healthProfile
 * @property-read \modules\account\responses\HealthProfile $response
 * @property int $deletedAt [timestamp]
 */
class HealthProfile extends \common\models\healthProfile\HealthProfile
{
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
     * Gets query for [[HealthProfileHealthMedicals]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\HealthProfileHealthMedicalQuery
     */
    public function getHealthProfileHealthMedicals()
    {
        return $this->hasMany(HealthProfileHealthMedical::className(), ['healthProfileId' => 'id']);
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

    /**
     * @return ResponseHealthProfile
     * @throws \yii\base\InvalidConfigException
     */
    public function getResponse(): ResponseHealthProfile
    {
        return Yii::createObject(ResponseHealthProfile::class, [$this]);
    }
}
