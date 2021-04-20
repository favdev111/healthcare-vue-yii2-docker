<?php

namespace console\components\queueJobs;

use modules\account\helpers\EventHelper;
use modules\account\models\Job;
use modules\account\models\NotificationSetting;
use modules\notification\Module as NotificationModule;
use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

/**
 * Class JobPostingOlderThan3DaysJob
 * @package console\components\queueJobs
 */
class JobPostingOlderThan3DaysJob extends BaseObject implements RetryableJobInterface
{
    /**
     * @var int $tutorId
     */
    public $jobId;

    /**
     * @inheritDoc
     */
    public function execute($queue): bool
    {
        if (!$this->jobId) {
            Yii::error('Job id is empty into JobPostingOlderThan3DaysJob.');
            return true;
        }

        $job = Job::findOne($this->jobId);

        if (!$job) {
            Yii::error('Job does not exist into JobPostingOlderThan3DaysJob.');
            return true;
        }

        if (!$job->hiredJobHires && !$job->isJobClose()) {
            EventHelper::jobPostingOlder($job);
        }
        return true;
    }


    /**
     * @return int
     */
    public function getTtr(): int
    {
        return 900;
    }


    /**
     * @param int $attempt
     * @param \Exception|\Throwable $error
     * @return bool
     */
    public function canRetry($attempt, $error): bool
    {
        return true;
    }
}
