<?php

namespace modules\notification\channels\pusher;

use common\components\pusher\Pusher;
use tuyakhov\notifications\channels\ChannelInterface;
use tuyakhov\notifications\NotifiableInterface;
use tuyakhov\notifications\NotificationInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * Class Channel
 * @package modules\channels\pusher
 */
class Channel extends Component implements ChannelInterface
{
    /**
     * @var $mailer Pusher|array|string the pusher object or the application component ID of the pusher object.
     */
    public $pusher = 'pusher';
    /**
     * @var string Pusher channel
     */
    public $channel;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->pusher = Instance::ensure($this->pusher, Pusher::class);
    }

    /**
     * @param NotifiableInterface $recipient
     * @param NotificationInterface $notification
     * @return mixed|void
     * @throws \Pusher\PusherException
     * @throws \yii\base\InvalidConfigException
     */
    public function send(NotifiableInterface $recipient, NotificationInterface $notification)
    {
        /** @var Message $message */
        $message = $notification->exportFor('pusher');
        if (!($message instanceof Message)) {
            throw new InvalidConfigException('Invalid notification message');
        }

        $recipientId = $recipient->routeNotificationFor('pusher');

        $channel = $message->channel ?? $this->channel;
        $channel = str_replace('{accountId}', $recipientId, $channel);
        return $this->pusher->push($channel, $message->notificationModel, $message->event);
    }
}
