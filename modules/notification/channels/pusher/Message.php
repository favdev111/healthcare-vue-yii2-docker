<?php

namespace modules\notification\channels\pusher;

use tuyakhov\notifications\messages\AbstractMessage;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * Class Message
 * @package modules\channels\pusher
 */
class Message extends AbstractMessage
{
    /**
     * @var string Pusher channel
     */
    public $channel;
    /**
     * @var string Pusher event
     */
    public $event;
    /**
     * @var NotificationModel|array|string NotificationModel configuration or itself object
     */
    public $notificationModel;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (!$this->notificationModel) {
            throw new InvalidConfigException('The "notificationModel" property must be set.');
        }

        $this->notificationModel = Instance::ensure($this->notificationModel, NotificationModel::class);
    }
}
