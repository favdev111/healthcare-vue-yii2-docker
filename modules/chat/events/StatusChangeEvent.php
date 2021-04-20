<?php

namespace modules\chat\events;

use Yii;
use yii\base\Event;

class StatusChangeEvent extends Event
{
    public $message;

    public $accountId;
    public $status;

    public function init()
    {
        parent::init();
    }
}
