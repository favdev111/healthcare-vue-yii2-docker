<?php

namespace common\components\pusher\notifications;

/**
 * Class AbstractNotification
 * @package common\components\pusher\notifications
 */
abstract class AbstractNotification implements NotificationInterface
{
    /**
     * @var
     */
    protected $message;
    /**
     * @var
     */
    protected $notification;

    /**
     * @return array
     */
    final public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
