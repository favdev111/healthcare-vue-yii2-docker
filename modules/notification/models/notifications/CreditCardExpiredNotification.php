<?php

namespace modules\notification\models\notifications;

use modules\notification\channels\pusher\Message;
use modules\notification\models\entities\common\NotificationType;
use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;

/**
 * Class CreditCardExpiredNotification
 * @package modules\notification\models\notifications
 */
class CreditCardExpiredNotification implements NotificationInterface
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
        $this->notificationType = NotificationType::findCreditCard();
    }

    /**
     * @return Message|object
     * @throws \yii\base\InvalidConfigException
     */
    public function exportForPusher()
    {
        return \Yii::createObject([
            'class' => Message::class,
            'notificationModel' => [
                'message' => $this->getMessage(),
                'data' => [
                    'notificationTypeId' => $this->notificationType->id,
                ]
            ],
        ]);
    }

    /**
     * Prepares notification for 'database' channel
     */
    public function exportForDatabase()
    {
        return \Yii::createObject([
            'class' => '\tuyakhov\notifications\messages\DatabaseMessage',
            'body' => $this->getMessage(),
            'data' => [
                'notificationTypeId' => $this->notificationType->id,
            ]
        ]);
    }

    /**
     * @return string
     */
    protected function getMessage(): string
    {
        return 'Your credit card is expired. Please change payment method.';
    }
}
