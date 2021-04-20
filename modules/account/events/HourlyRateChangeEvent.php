<?php

namespace modules\account\events;

use yii\base\Event;

class HourlyRateChangeEvent extends Event
{
    public $accountId;
    public $rate;
    public $rateOld;
    public $rateModel;
}
