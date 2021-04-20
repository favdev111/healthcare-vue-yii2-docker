<?php

namespace modules\task\components;

use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

abstract class RetryableJob extends BaseObject implements RetryableJobInterface
{
    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getTtr()
    {
        return 1 * 60 * 60;
    }
}
