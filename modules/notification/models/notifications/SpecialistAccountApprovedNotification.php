<?php

namespace modules\notification\models\notifications;

use modules\notification\models\entities\common\NotificationType;
use tuyakhov\notifications\messages\MailMessage;
use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;

class SpecialistAccountApprovedNotification implements NotificationInterface
{
    use NotificationTrait;

    /**
     * @var NotificationType
     */
    protected $notificationType;

    /**
     * HealthTestReceivedNotification constructor.
     */
    public function __construct()
    {
        $this->notificationType = NotificationType::findSpecialistAccountApproved();
    }

    /**
     * Prepares notification for 'mail' channel
     */
    public function exportForMail()
    {
        return \Yii::createObject([
            'class' => MailMessage::class,
            'subject' => 'Welcome to WINIT | Your Account is Active!',
            'view' => 'specialist/account-approved',
            'viewData' => [],
        ]);
    }
}
