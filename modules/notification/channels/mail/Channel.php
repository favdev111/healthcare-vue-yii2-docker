<?php

namespace modules\notification\channels\mail;

use common\helpers\EmailHelper;
use tuyakhov\notifications\channels\ChannelInterface;
use tuyakhov\notifications\messages\MailMessage;
use tuyakhov\notifications\NotifiableInterface;
use tuyakhov\notifications\NotificationInterface;
use yii\base\Component;

class Channel extends Component implements ChannelInterface
{
    public function send(NotifiableInterface $recipient, NotificationInterface $notification)
    {
        /**
         * @var $message MailMessage
         */
        $message = $notification->exportFor('mail');

        return EmailHelper::send(
            $recipient->routeNotificationFor('mail'),
            $message->subject,
            $message->view,
            $message->viewData
        );
    }
}
