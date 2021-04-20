<?php

namespace common\components\pusher\notifications;

use modules\notification\models\Notification;

/**
 * Class JobPostingOlder
 * @package common\components\pusher\notifications
 */
class JobPostingOlder extends AbstractNotification
{
    /**
     *
     */
    public const MESSAGE = 'Notice! :jobName is older than 3 days.';

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
