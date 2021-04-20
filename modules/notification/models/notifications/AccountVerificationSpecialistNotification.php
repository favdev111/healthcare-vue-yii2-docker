<?php

namespace modules\notification\models\notifications;

use modules\notification\models\entities\common\NotificationType;
use tuyakhov\notifications\messages\MailMessage;
use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;

class AccountVerificationSpecialistNotification implements NotificationInterface
{
    use NotificationTrait;

    public $token;

    /**
     * @var NotificationType
     */
    protected $notificationType;

    public function __construct()
    {
        $this->notificationType = NotificationType::findAccountSpecialistVerification();
    }

    /**
     * Prepares notification for 'mail' channel
     */
    public function exportForMail()
    {
        return \Yii::createObject([
            'class' => MailMessage::class,
            'subject' => 'Email Confirmation',
            'view' => 'account-verification',
            'viewData' => [
                'subject' => 'Verify Your Email',
                'link' => \common\helpers\Url::getFrontendUrl('/health-pro-signup', ['token' => $this->token]),
            ],
        ]);
    }
}
