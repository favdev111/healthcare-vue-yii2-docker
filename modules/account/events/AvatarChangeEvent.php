<?php

namespace modules\account\events;

use yii\base\Event;

class AvatarChangeEvent extends Event
{
    public $model;
    public $avatarUrl;
}
