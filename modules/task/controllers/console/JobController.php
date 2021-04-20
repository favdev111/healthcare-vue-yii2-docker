<?php

namespace modules\task\controllers\console;

use common\models\ProcessedEvent;
use modules\account\models\Account;
use modules\account\models\Job;
use modules\account\models\JobHire;
use modules\chat\Module;
use modules\notification\helpers\NotificationHelper;
use modules\notification\models\Notification;
use UrbanIndo\Yii2\Queue\Worker\Controller;
use Yii;

class JobController extends Controller
{
    public function actionSuspendStudentJobs($accountId)
    {
        /**
         * @var $jobs Job[]
         */
        $jobs = Job::findWithoutRestrictions()->andWhere(['accountId' => $accountId])->all();
        Yii::info('Suspending Student #' . $accountId . ' jobs', 'chat');
        foreach ($jobs as $job) {
            Yii::info('Suspending job #' . $job->id, 'chat');
            $job->suspended = true;
            $job->detachBehavior('timestamp');
            if (!$job->save()) {
                Yii::error('Failed to suspend Student #' . $accountId . ' job #' . $job->id . ' Errors: ' . json_encode($job->getErrors()), 'chat');
            }
        }
    }

    public function actionUnSuspendStudentJobs($accountId)
    {
        /**
         * @var $jobs Job[]
         */
        $jobs = Job::findWithoutRestrictions()->andWhere(['accountId' => $accountId])->all();
        Yii::info('UnSuspending Student #' . $accountId . ' jobs', 'chat');
        foreach ($jobs as $job) {
            Yii::info('UnSuspending job #' . $job->id, 'chat');
            $job->suspended = false;
            $job->detachBehavior('timestamp');
            if (!$job->save()) {
                Yii::error('Failed to un-suspend Student #' . $accountId . ' job #' . $job->id . ' Errors: ' . json_encode($job->getErrors()), 'chat');
            }
        }
    }

    protected function createNotificationNotApplied(Job $job, Account $applicant, ?int $initiatorId): void
    {
        $notify = new Notification();
        $notify->type = NotificationHelper::TYPE__TUTOR__APPLICANT_NOT_HIRED;
        $notify->extraData = [
            'jobId' => $job->id,
        ];
        $notify->accountId = $applicant->id;
        $notify->initiatorId = $initiatorId;
        $notify->save(false);
    }

    protected function sendChatMessageNotApplied(string $message, Account $applicant, Account $jobAccount): void
    {
        $from = $jobAccount->chat;
        $to = $applicant->chat;
        /**
         * @var Module $module
         */
        $module = Yii::$app->getModule(Module::$moduleName);
        $response = $module->sendMessage($message, $from, $to, 'chat', false);
        if ($response) {
            /**
             * @var $moduleAccount \modules\account\Module
             */
            $moduleAccount = Yii::$app->getModule('account');
            $moduleAccount->eventNewMessageTutor(
                $jobAccount,
                $applicant,
                $message,
                $response->response,
                $response->model
            );
        } else {
            Yii::error('Failed to send message about filled position', 'chat');
        }
    }

    public function actionNotifyNotApplied($jobId, $tutorId, $initiatorId = null)
    {
        $job = Job::findOne($jobId);
        if (empty($job)) {
            return false;
        }
        //inform applicants
        $applicants = $job->getApplicants()->select('id')->andWhere(['not', [Account::tableName() . '.id' => $tutorId]]);
        $hiredTutorIds = JobHire::find()->select('tutorId')->andWhere(['jobId' => $jobId])->column();
        foreach ($applicants->all() as $applicant) {
            /**
             * @var Account $applicant
             */
            //if tutor has already received notification or was hired to job
            if ($applicant->isProcessedEvent(ProcessedEvent::TYPE_TUTOR_NOT_APPLIED, $jobId) || in_array($applicant->id, $hiredTutorIds)) {
                continue;
            }
            $applicant->addProcessedEvent(ProcessedEvent::TYPE_TUTOR_NOT_APPLIED, $jobId);
            //send notification that position is already filled
            $this->createNotificationNotApplied($job, $applicant, $initiatorId);
            $jobAccount = $job->account;
            $message = 'Unfortunately, this position has been filled. Please check out other opportunities both in your area and online.  ';
            $this->sendChatMessageNotApplied($message, $applicant, $jobAccount);
        }
        return true;
    }

    public function actionUpdateJobHireTutoringHours($jobHireId)
    {
        $jobHire = JobHire::findOne($jobHireId);
        if (!empty($jobHire)) {
            $jobHire->updateTutoringHours();
            $jobHire->save(false);
        }

        //recalculate average time per relation for tutor
        $statistic = $jobHire->tutor->clientStatistic;
        $statistic->hoursPerRelation = $statistic->calculateHoursPerRelation();
        $statistic->save();
    }
}
