<?php

namespace modules\account\events;

use yii\base\Event;

class DeletedEvent extends Event
{
    public $model;
}
