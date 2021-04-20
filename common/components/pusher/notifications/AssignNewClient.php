<?php

namespace common\components\pusher\notifications;

use modules\notification\models\Notification;

/**
 * Class AssignNewClient
 * @package common\components\pusher\notifications
 */
class AssignNewClient extends AbstractNotification
{
    /**
     *
     */
    public const MESSAGE = ':clientName was assigned to you. Please take appropriate actions.';

    /**
     * ReplyToJob constructor.
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification->toArray();
        $this->message = $notification->message;
    }
}
