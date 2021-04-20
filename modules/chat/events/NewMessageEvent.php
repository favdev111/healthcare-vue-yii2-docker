<?php

namespace modules\chat\events;

use Yii;
use yii\base\Event;

class NewMessageEvent extends Event
{
    public $message;
    public $account;
    public $messageModel;

    public function init()
    {
        parent::init();
    }
}
