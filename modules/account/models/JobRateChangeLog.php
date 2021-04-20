<?php

namespace modules\account\models;

class JobRateChangeLog extends ChangeLog
{
    protected static $descriptionHead = "Job bill rate change.\n";

    public function getPreparedDescription(): string
    {
        $description = static::$descriptionHead;
        $newRate = $this->newValue ?: '(empty)';
        $oldRate = $this->oldValue ?: '(empty)';
        $description .= $this->comment ? ($this->comment . "\n") : '';
        $description .= 'From: ' . ($oldRate ?? null) . ' to ' . ($newRate ?? null) . "\n";
        if ($this->madeBy) {
            $description .= 'Made by: ' . $this->author->profile->fullName . "\n";
        } else {
            $description .= "Made automatically.\n";
        }
        $description .= 'At: ' . $this->date;
        return $description;
    }

    public function fillDefaults(): void
    {
        parent::fillDefaults();
        $this->actionType = static::ACTION_TYPE_RATE_CHANGE;
        $this->objectType = static::OBJECT_TYPE_JOB;
    }

    public function beforeSave($insert)
    {
        $this->oldValue = (float)$this->oldValue[0];
        $this->newValue = (float)$this->newValue[0];
        return parent::beforeSave($insert);
    }
}
