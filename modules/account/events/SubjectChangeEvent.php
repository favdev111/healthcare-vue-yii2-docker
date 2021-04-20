<?php

namespace modules\account\events;

use yii\base\Event;

class SubjectChangeEvent extends Event
{
    public $accountId;
    public $subjectAddIds;
    public $subjectRemoveIds;
}
