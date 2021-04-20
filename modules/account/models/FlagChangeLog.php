<?php

namespace modules\account\models;

class FlagChangeLog extends ChangeLog
{
    public function getPreparedDescription(): string
    {
        $description = "Flag change\n";
        $newFlag = $this->newValue[0] ?: '(empty)';
        $oldFlag = $this->oldValue[0] ?: '(empty)';
        $description .= $this->comment ? ($this->comment . "\n") : '';
        $description .= 'From: ' . ($oldFlag ?? null) . ' to ' . ($newFlag ?? null) . "\n";
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
        $this->actionType = static::ACTION_TYPE_FLAG_CHANGE;
        $this->objectType = static::OBJECT_TYPE_ACCOUNT;
    }
}
