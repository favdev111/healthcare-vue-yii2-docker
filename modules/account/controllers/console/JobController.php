<?php

namespace modules\account\controllers\console;

use common\helpers\Automatch;
use common\models\ProcessedEvent;
use modules\account\models\Account;
use modules\account\models\IgnoredTutorsJob;
use modules\account\models\Job;
use modules\account\models\JobHire;
use modules\account\models\TutorSearch;
use modules\account\Module;
use modules\chat\models\Chat;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Class JobController
 * @package modules\account\controllers\console
 */
class JobController extends Controller
{
    const BATCH_TUTOR_LIMIT = 25;
    const NEW_TUTORS_LIMIT = 10;
    const APPLICANTS_LIMIT = 5;

    public function actionSuspendSuspiciousUsersJobs()
    {
        // Find all active jobs for suspicous users
        $suspiciousUsersJobsQuery = Job::find()
            ->joinWith('account.chat')
            ->andWhere([Chat::tableName() . '.status' => Chat::nonActiveStatuses()]);
        foreach ($suspiciousUsersJobsQuery->each(100) as $suspiciousUsersJob) {
            /**
             * @var $suspiciousUsersJob Job
             */
            $this->stdout('Suspending job #' . $suspiciousUsersJob->id . "\n\r");
            $suspiciousUsersJob->suspended = true;
            $suspiciousUsersJob->detachBehavior('timestamp');
            if (!$suspiciousUsersJob->save()) {
                $msg = 'Failed to suspend job #' .
                    $suspiciousUsersJob->id . ' Errors: ' . json_encode($suspiciousUsersJob->getErrors()) . "\n\r";
                $this->stderr($msg);
            }
        }
    }

    /**
     * @param string $message
     */
    protected function writeJobLog(string $message): void
    {
        Yii::info($message, 'job');
    }

    /**
     * @throws \yii\base\Exception
     */
    public function actionNewJob()
    {
        /**
         * @var Module $moduleAccount
         */
        $moduleAccount = Yii::$app->getModule('account');
        $jobs = Job::find()
            ->andWhere([
                'or',
                ['newJob' => Job::NEW_JOB],
                ['forceSendingNotification' => true]
            ])
            ->andWhere(['close' => 0])
            ->andWhere(['allTutorsNotified' => false])
            ->andWhere(['!=', 'block', 1])
            ->andWhere(['status' => Job::PUBLISH])
            ->all();


        foreach ($jobs as $job) {
            /**
             * @var Job $job
             */
            $this->writeJobLog("Start process job with id {$job->id}");
            $this->writeJobLog("Current job attributes:" . json_encode($job->attributes));

            $countApplicants = count($job->applicants);
            $limitApplicants = ($job->notificationCycle + 1) * static::APPLICANTS_LIMIT;
            //if count applicants more then limit
            if ($countApplicants >= $limitApplicants) {
                //stop sending
                $fields = [
                    'newJob' => false,
                ];
                if ($job->forceSendingNotification) {
                    $fields = array_merge(['forceSendingNotification' => false], $fields);
                    $this->writeJobLog("Disable force sending.");
                }
                //do not use model because there is no need to trigger all Job save events
                Job::updateAll($fields, ['id' => $job->id]);
                $msg = "Job {$job->id} has more that " . $limitApplicants . " applicants. Stop sending notifications.";
                $this->writeJobLog($msg);
                continue;
            }

            //get ids of tutors that were notified about this job
            $excludeTutors = $job->getIdsTutorsNotified();
            $excludeTutorsIdsFromRepost = $this->getExcludedTutorFromRepostJob($job);
            $excludeTutorsIds = array_unique(array_merge($excludeTutors, $excludeTutorsIdsFromRepost));
            $countNotifiedBefore = $job->countNotification ?? 0;
            if ($countNotifiedBefore) {
                $this->writeJobLog('Tutors notified before: ' . json_encode($excludeTutorsIds));
            }

            $newTutorsIds = [];

            //looking for new tutors (in 15 miles radius created in last 30 days, but no more then 10 ber batch)
            $newTutorSearch = $this->getNewTutorSearch($job, $excludeTutorsIds);
            $newTutorsProvider = $newTutorSearch->search(static::NEW_TUTORS_LIMIT);
            $tutorsScores = [];
            foreach ($newTutorsProvider->getModels() as $model) {
                $newTutorsIds[] = $model->getPrimaryKey();
                $tutorsScores[$model->getPrimaryKey()] = $model->getScore();
            }
            $newTutorsCount = count($newTutorsIds);
            $tutorsIds = $newTutorsIds;
            $this->writeJobLog('New tutors that should be notified: ' . json_encode($tutorsIds));

            //exclude tutors that have been already found
            $excludeTutorsIds = array_unique(array_merge($excludeTutorsIds, $tutorsIds));

            //looking for top tutors
            $topTutorsSearch = $this->getTopTutorsSearch($job, $excludeTutorsIds);
            $topTutorsProvider = $topTutorsSearch->search(static::BATCH_TUTOR_LIMIT - $newTutorsCount);
            $topTutorsIds = [];
            foreach ($topTutorsProvider->getModels() as $model) {
                $topTutorsIds[] = $model->getPrimaryKey();
                $tutorsScores[$model->getPrimaryKey()] = $model->getScore();
            }
            $this->writeJobLog('Top tutors that should be notified: ' . json_encode($topTutorsIds));

            $tutorsIds = array_merge($topTutorsIds, $tutorsIds);
            $notifiedTutors = [];
            if (empty($tutorsIds)) {
                Job::updateAll(
                    [
                        'allTutorsNotified' => true,
                        'newJob' => false,
                        'forceSendingNotification' => false,
                    ],
                    ['id' => $job->id]
                );
                $this->writeJobLog('There is no tutor to notify. All tutor notified.: ' . json_encode($topTutorsIds));
                continue;
            }
            foreach ($tutorsIds as $tutorsId) {
                /**
                 * @var Account $tutor
                 */
                $tutor = Account::findOne($tutorsId);
                if (empty($tutor)) {
                    $this->writeJobLog("Tutor with id $tutorsId wasn't found");
                    continue;
                }
                //if user can get sms or notification about new job
                if ($tutor->isCanGetNewJobPostedNotifications()) {
                    if (in_array($tutorsId, $newTutorsIds)) {
                        $data = $newTutorSearch->lastSearchParams;
                    } else {
                        $data = $topTutorsSearch->lastSearchParams;
                    }
                    $tutorData = [
                        'from' => 'Automatically',
                        'gender' => $tutor->profile->gender,
                        'subject' => $tutor->getIdsOfRelatedCategories(),
                        'zip' => $tutor->profile->zipCode,
                        'availability' => $tutor->availability->value ?? 0,
                        'rating' => $tutor->rating->totalRating ?? 0,
                        'createdAt' => $tutor->createdAt,
                        'request' => $data
                    ];

                    if ($job->tutorNotifiedAboutNewJob($tutorsId, $tutorData, $tutorsScores[$tutor->id])) {
                        $moduleAccount->eventNewJobPosted($job, $tutor->id);
                        $notifiedTutors[] = $tutorsId;
                    } else {
                        Yii::error('Failed to notify tutor with id ' . $tutorsId, 'job');
                    }
                }
            }

            $countNotifiedNow = count($notifiedTutors);
            $this->writeJobLog('Count tutors that was notified before : ' . $countNotifiedBefore);
            $this->writeJobLog('Count tutors that was notified now : ' . $countNotifiedNow);
            if ($countNotifiedNow) {
                $this->writeJobLog('List tutors that was notified now : ' . json_encode($notifiedTutors));
            }

            $totalNotified = $countNotifiedBefore + $countNotifiedNow;

            //index number of launched batch
            $batchIndex = ProcessedEvent::find()
                ->job($job->id)
                ->newJobPostedNotificationProcessed()
                ->count();
            $batchIndex += 1;
            //save data about event send new-job-posted notifications
            $processedEvent = new ProcessedEvent(['jobId' => $job->id]);
            $processedEvent->type = ProcessedEvent::TYPE_NEW_JOB_POSTED_NOTIFICATIONS_PROCESSED;
            $processedEvent->data = [
                'batchIndex' => $batchIndex,
                'notifiedNow' => $countNotifiedNow,
                'totalNotified' => $totalNotified,
            ];
            if (!$processedEvent->save(false)) {
                $this->writeJobLog("Can not save processed_event to job {$job->id}.");
            }

            $job->countNotification = $totalNotified;
            $job->detachBehavior('timestamp');
            if ($job->save(false)) {
                $this->writeJobLog("Job {$job->id} successfully processed and updated.");
            } else {
                $this->writeJobLog("Failed to update Job {$job->id}.");
            }
        }
    }

    /**
     * @param Job $job
     * @param array $excludeTutorsIds
     * @return TutorSearch
     */
    protected function getNewTutorSearch(Job $job, array $excludeTutorsIds): TutorSearch
    {
        $searchModel = new TutorSearch();
        $searchModel->compareWithJob($job);
        $searchModel->selectNewTutors = true;
        $searchModel->receiveNewJobPostedNotifications = true;
        $searchModel->excludedTutorsIds = $excludeTutorsIds;
        return $searchModel;
    }

    /**
     * @param Job $job
     * @param array $excludeTutorsIds
     * @return TutorSearch
     */
    protected function getTopTutorsSearch(Job $job, array $excludeTutorsIds): TutorSearch
    {
        $searchModel = new TutorSearch();
        $searchModel->receiveNewJobPostedNotifications = true;
        $searchModel->compareWithJob($job);
        $searchModel->excludedTutorsIds = $excludeTutorsIds;
        $searchModel->minimalRating = 3;
        return $searchModel;
    }

    /**
     * @param $jobId
     * @param $tutorId
     * @throws \yii\base\Exception
     */
    public function actionTestJob($jobId, $tutorId)
    {
        $job = Job::findOne($jobId);
        if (empty($job)) {
            Console::output('Job not found');
        }
        $topTutorsSearch = $this->getTopTutorsSearch($job, []);
        $topTutorsSearch->minimalRating = false;
        $topTutorsProvider = $topTutorsSearch->search();
        $topTutorsProvider->pagination = false;
        foreach ($topTutorsProvider->getModels() as $model) {
            if ($model->getPrimaryKey() == $tutorId) {
                Console::output("accountId $tutorId score: " . $model->getScore());
            }
        }
    }

    /**
     *
     */
    public function actionSetZipCode()
    {
        $jobs = Job::findWithoutRestrictions()->all();
        foreach ($jobs as $job) {
            $job->zipCode = $job->account->profile->zipCode;
            $job->save(false);
        }
    }

    /**
     * @param Job $job
     * @return array
     */
    private function getExcludedTutorFromRepostJob(Job $job): array
    {
        if (!$job->originJobId) {
            return [];
        }

        return IgnoredTutorsJob::find()
            ->select('tutorId')
            ->andWhere(['originJobId' => $job->originJobId])
            ->column();
    }

    public function actionSetAutomatchCompanies(...$ids)
    {
        Automatch::setCompanies($ids);
    }

    public function actionStartAutomatchJob($jobId, $zeroDelay)
    {
        $job = Job::findOne($jobId);
        Job::updateAll(['isAutomatchEnabled' => true], ['id' => $jobId]);
        foreach ($job->getJobHires()->andWhere([JobHire::tableName() . '.status' => JobHire::STATUS_HIRED])->all() as $jh) {
            $jh->delete();
        }
        $job->save(false);
        $job->autoMatch((bool)$zeroDelay);
    }
}
