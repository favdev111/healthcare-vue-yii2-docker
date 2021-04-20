<?php

namespace common\listeners\healthProfile;

use common\listeners\BaseListener;
use common\models\healthProfile\HealthProfile;
use common\models\healthProfile\insurance\HealthProfileInsurance;
use Yii;
use yii\base\ErrorException;

/**
 * Class Listener
 * @package common\listeners\healthProfile
 *
 * @property-read HealthProfile $sender
 */
abstract class Listener extends BaseListener
{
    /**
     * @param HealthProfile $healthProfile
     * @return HealthProfileInsurance
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildHealthProfileInsurance(HealthProfile $healthProfile): ?HealthProfileInsurance
    {
        if (!($healthProfile->isMain && $healthProfile->firstName && $healthProfile->lastName)) {
            return null;
        }

        $insurance = $healthProfile->primaryInsurance ?? Yii::createObject(HealthProfileInsurance::class);
        $insurance->firstName = $insurance->firstName ?? $healthProfile->firstName;
        $insurance->lastName = $insurance->lastName ?? $healthProfile->lastName;
        $insurance->isPrimary = true;
        $insurance->healthProfileId = $insurance->healthProfileId ?? $healthProfile->id;

        if (!$insurance->dateOfBirth && $healthProfile->birthday) {
            $insurance->setDateOfBirth($healthProfile->birthday);
        }

        if (!$insurance->save()) {
            throw new ErrorException('HealthProfileInsurance was not saved');
        }

        return $insurance;
    }
}
