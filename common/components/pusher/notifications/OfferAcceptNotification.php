<?php

namespace common\components\pusher\notifications;

use modules\notification\models\Notification;

/**
 * Class OfferAcceptNotification
 * @package common\components\pusher\notifications
 */
class OfferAcceptNotification extends AbstractNotification
{
    /**
     *
     */
    public const MESSAGE = 'Congratulations! :tutorName just accepted your job offer for :jobName';

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
