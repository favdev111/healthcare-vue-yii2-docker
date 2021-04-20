<?php

namespace modules\account\events;

use yii\base\Event;

class RatingChangeEvent extends Event
{
    public $accountId;
    public $rating;
    public $ratingOld;
}
