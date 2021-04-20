<?php

namespace console\components\queueJobs;

use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

class PusherNotificationJob extends BaseObject implements RetryableJobInterface
{
    public $channel;
    public $event;
    public $data;
    public $socketId;

    public function getTtr()
    {
        return 900;
    }

    public function execute($queue)
    {
        \Yii::$app->pusher->sendEvent($this->channel, $this->event, $this->data, $this->socketId);
    }

    public function canRetry($attempt, $error)
    {
        return false;
    }
}
