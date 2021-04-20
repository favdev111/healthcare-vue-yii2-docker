<?php

namespace common\components\pusher;

use common\components\pusher\notifications\NotificationInterface;
use common\helpers\QueueHelper;
use Pusher\Pusher as PusherSdk;
use Pusher\PusherException;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class Pusher
 * @package common\components\pusher
 */
class Pusher extends Component
{
    /**
     * @var PusherSdk
     */
    private $pusher = null;
    /**
     * @var null
     */
    public $appId = null;
    /**
     * @var null
     */
    public $appKey = null;
    /**
     * @var null
     */
    public $appSecret = null;
    /**
     * @var array
     */
    private $selectableOptions = ['host', 'port', 'timeout', 'encrypted', 'cluster'];
    /**
     * @var array
     */
    public $options = [];
    /**
     * @var string
     */
    public $defaultEventName = 'notification';
    /**
     * @var int
     */
    private $cacheDuration = 600; // 10 minute


    /**
     * @throws InvalidConfigException
     * @throws PusherException
     */
    public function init()
    {
        parent::init();

        foreach (['appId', 'appKey', 'appSecret'] as $attribute) {
            if (!$this->{$attribute}) {
                throw new InvalidConfigException($attribute . ' cannot be empty!');
            }
        }

        foreach (array_keys($this->options) as $key) {
            if (in_array($key, $this->selectableOptions) === false) {
                throw new InvalidConfigException($key . ' is not a valid option!');
            }
        }

        if (!$this->pusher) {
            $this->pusher = new PusherSdk(
                $this->appKey,
                $this->appSecret,
                $this->appId,
                $this->options
            );
        }
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        if (method_exists($this->pusher, $name)) {
            return call_user_func_array([$this->pusher, $name], $params);
        }

        return parent::__call($name, $params);
    }

    /**
     * @param string $channel
     * @param NotificationInterface $data
     * @param string|null $event
     * @param string|null $socket_id
     */
    public function push(
        string $channel,
        NotificationInterface $data,
        ?string $event,
        ?string $socket_id = null
    ): void {
        $channelName = 'private-' . $channel;
        if ($this->isChannelOpen($channelName)) {
            $event = $event ?? $this->defaultEventName;
            QueueHelper::sendNotificationToPusher($channelName, $event, $data->toArray(), $socket_id);
        }
    }

    public function sendEvent($channel, $event, $data, $socketId)
    {
        $this->pusher->trigger(
            $channel,
            $event,
            $data,
            $socketId,
            false,
            false
        );
    }

    /**
     * @param string $channel
     * @return bool
     */
    public function isChannelOpen(string $channel): bool
    {
        try {
            $pusherChannel = $this->pusher->get_channel_info($channel);
            if (!empty($pusherChannel->occupied)) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * @param string $channel
     * @param string $socketId
     * @return string
     * @throws PusherException
     */
    public function auth(string $channel, string $socketId)
    {
        return $this->pusher->socket_auth($channel, $socketId);
    }
}
