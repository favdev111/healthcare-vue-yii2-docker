<?php

namespace common\components\pusher\notifications;

use modules\notification\models\Notification;

/**
 * Class NewTutorJobNotification
 * @package common\components\pusher\notifications
 */
class NewTutorJobNotification extends AbstractNotification
{
    /**
     *
     */
    public const MESSAGE = 'New profile created! :tutorName created profile in :jobName area.';

    /**
     * OfferAcceptNotification constructor.
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification->toArray();
        $this->message = $notification->message;
    }
}
