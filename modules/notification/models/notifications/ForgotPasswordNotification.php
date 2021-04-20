<?php

namespace modules\notification\models\notifications;

use modules\notification\models\entities\common\NotificationType;
use tuyakhov\notifications\messages\MailMessage;
use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;

class ForgotPasswordNotification implements NotificationInterface
{
    use NotificationTrait;

    public $token;

    /**
     * @var NotificationType
     */
    protected $notificationType;

    public function __construct()
    {
        $this->notificationType = NotificationType::findForgotPassword();
    }

    /**
     * Prepares notification for 'mail' channel
     */
    public function exportForMail()
    {
        return \Yii::createObject([
            'class' => MailMessage::class,
            'subject' => 'Forgot password',
            'view' => 'forgot-password',
            'viewData' => [
                'subject' => 'Forgot password',
                'link' => \common\helpers\Url::getFrontendUrl('/auth/reset-password', ['token' => $this->token]),
            ],
        ]);
    }
}
