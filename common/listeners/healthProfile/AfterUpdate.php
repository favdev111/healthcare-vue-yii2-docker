<?php

namespace common\listeners\healthProfile;

use yii\base\ErrorException;
use yii\base\Event;

/**
 * Class AfterUpdate
 * @package common\listeners\healthProfile
 */
class AfterUpdate extends Listener
{
    /**
     * @param Event $event
     * @return mixed|void
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public function run(Event $event)
    {
        parent::run($event);
        $this->buildHealthProfileInsurance($this->sender);
    }
}
