<?php

namespace common\listeners;

use yii\base\BaseObject;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * Class BaseListener
 * @package common\listeners
 *
 * @property-read \yii\db\ActiveRecord|object $sender
 */
abstract class BaseListener extends BaseObject
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * @param Event $event
     * @return mixed
     */
    public function run(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return object|ActiveRecord
     */
    protected function getSender()
    {
        return $this->event->sender;
    }
}
