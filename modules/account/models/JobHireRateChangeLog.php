<?php

namespace modules\account\models;

class JobHireRateChangeLog extends JobRateChangeLog
{
    protected static $descriptionHead = "Job hire price change.\n";

    public function fillDefaults(): void
    {
        parent::fillDefaults();
        $this->objectType = static::OBJECT_TYPE_JOB_HIRE;
    }
}
