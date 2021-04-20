<?php

namespace modules\task\queueJobs;

use common\helpers\Automatch;
use common\helpers\EmailHelper;
use modules\account\models\Account;
use modules\account\models\Job;
use modules\account\models\JobHire;
use modules\task\components\RetryableJob;
use PharIo\Manifest\Email;
use yii\queue\Queue;

class AutomatchJob extends RetryableJob
{

    public $jobId;

    protected function log(string $message, string $method)
    {
        \Yii::$method($message, 'automatch');
    }

    protected function informOpsTeam(Job $job)
    {
        $clientName = $job->account->profile->fullName;
        EmailHelper::sendMessageToOpsTeam('NO APPLICANT', "No applicant found for automated match $clientName.");
    }

    protected function createJobHire(Job $job, Account $tutor)
    {
        $jobHire = new JobHire();
        $jobHire->jobId = $job->id;
        $jobHire->tutorId = $tutor->id;
        $jobHire->price = $job->getAutomatchRate();
        $jobHire->status = JobHire::STATUS_HIRED;
        $jobHire->shareContactInfo = true;
        $jobHire->responsibleId = null;
        $jobHire->save(false);
        $this->log('Created job hire id = ' . $jobHire->id, 'info');
    }

    protected function disableAutomatch(Job $job)
    {
        $job->isAutomatchEnabled = false;
        $job->save(false);
        $this->log('Automatch disabled', 'info');
    }

    public function checkJob(Job $job)
    {
        if (empty($job)) {
            $this->log('Job not found.', 'error');
            return false;
        }

        if ($job->close) {
            $this->log('Job is closed.', 'info');
            return false;
        }

        if (!$job->isAutomatchEnabled) {
            $this->log('Automatch was disabled for this job.', 'info');
            return false;
        }

        if ($job->getJobHires()->andWhere([JobHire::tableName() . '.status' => JobHire::STATUS_HIRED])->exists()) {
            $this->log('Job already has hire.', 'info');
            return false;
        }
        return true;
    }

    public function execute($queue)
    {
        try {
            $this->log('Process job with id ' . $this->jobId, 'info');

            $job = Job::findOne($this->jobId);

            if (!$this->checkJob($job)) {
                return;
            }

            $applies = $job->notDeclinedApplies;

            if (empty($applies)) {
                $this->log('Empty applicants list', 'info');
                $this->informOpsTeam($job);
                $this->disableAutomatch($job);
                return;
            }

            $matchedTutor = Automatch::findMatch($job);
            if ($matchedTutor) {
                $this->log('Matched tutor id = ' . $matchedTutor->id, 'info');
                $this->createJobHire($job, $matchedTutor);
                $job->close = true;
                $this->log('Job closed.', 'info');
                $job->save(false);
            } else {
                $this->log('Match not found', 'error');
            }
            $this->disableAutomatch($job);
        } catch (\Throwable $exception) {
            $this->log($exception->getMessage() . "\n" . $exception->getTraceAsString(), 'error');
        }
    }
}
