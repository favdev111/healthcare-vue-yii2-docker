<?php

namespace modules\account\models\api2Patient\entities\healthProfile\health;

use common\models\healthProfile\HealthProfile;

/**
 * This is the model class for table "health_profile_health".
 *
 * @property int $healthProfileId
 * @property int|null $smokeId
 * @property int|null $drinkId
 * @property int|null $isOtherSubstance
 * @property string|null $otherSubstanceText
 *
 * @property-read HealthDrink|null $drink
 * @property-read HealthSmoke|null $smoke
 * @property HealthProfile $healthProfile
 */
class HealthProfileHealth extends \common\models\healthProfile\health\HealthProfileHealth
{
    /**
     * @return string[]
     */
    public function fields()
    {
        return [
            'smoke',
            'drink',
            'isOtherSubstance',
            'otherSubstanceText',
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
     * @return \yii\db\ActiveQuery
     */
    public function getSmoke()
    {
        return $this->hasOne(HealthSmoke::class, ['id' => 'smokeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDrink()
    {
        return $this->hasOne(HealthDrink::class, ['id' => 'drinkId']);
    }
}
