<?php

namespace common\models;

use common\models\query\ProcessedEventQuery;

class SentNewJobNotification extends ProcessedEvent
{
    public static function find(): ProcessedEventQuery
    {
        return parent::find()->type(static::TYPE_TUTOR_NOTIFIED_ABOUT_NEW_JOB);
    }

    public function getJobName()
    {
        return $this->job->getNameWithLocationAndSubject();
    }

    public function getTutorFirstName(): string
    {
        return $this->account->profile->firstName ?? '';
    }

    public function getTutorLastName(): string
    {
        return $this->account->profile->lastName ?? '';
    }
}
