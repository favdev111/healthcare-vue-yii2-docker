<?php

namespace common\components\pusher\notifications;

use modules\notification\models\Notification;

/**
 * Class ReplyToJob
 * @package common\components\pusher\notifications
 */
class ReplyToJobNotification extends AbstractNotification
{
    /**
     *
     */
    public const MESSAGE = 'You\'ve just received a new message from :tutorName';

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
