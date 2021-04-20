<?php

namespace console\components;

/**
 * Class Queue
 *
 * @package console\components
 */
class Queue extends \yii\queue\db\Queue
{
    const PRIORITY_REGULAR = 1024;
    const PRIORITY_HIGH = 512;
    const PRIORITY_HIGHEST = 1;
    /**
     * @inheritDoc
     *
     * @param boolean $execNowOnly Exec now only
     */
    public function push($job, $execNowOnly = false)
    {
        if ($execNowOnly) {
            return $job->execute($this);
        }

        return parent::push($job);
    }
}
