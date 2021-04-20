<?php

namespace common\events;

use yii\base\Event;

/**
 * Class TutorLowBalanceEvent
 * @package common\events
 */
class TutorLowBalanceEvent extends Event
{
    const NAME = 'tutor-low-balance';
}
