<?php

namespace common\helpers;

use common\models\Sms;
use console\components\Queue;
use console\components\queueJobs\CreateSpecialistAgreementPdfJob;
use console\components\queueJobs\PusherNotificationJob;
use modules\account\models\Account;
use modules\account\models\TutorBooking;
use modules\payment\models\Transaction;
use modules\task\queueJobs\AutomatchJob;
use modules\task\queueJobs\CalculateTutorProfileUniqueWordsJob;
use modules\task\queueJobs\ChargeProcessJob;
use modules\task\queueJobs\CheckBookingJob;
use modules\task\queueJobs\CreateAccountFromBookingJob;
use modules\task\queueJobs\MailerJob;
use modules\task\queueJobs\SendAccountInactiveEmailJob;
use modules\task\queueJobs\TestSendAccountInactiveEmailJob;
use UrbanIndo\Yii2\Queue\Job;
use Yii;

class QueueHelper
{
    public static function pushMessage(
        array $accountIds,
        string $key,
        string $message,
        array $data
    ) {
        $task = new Job([
            'route' => 'push/send',
            'data' => [
                'accountIds' => $accountIds,
                'key' => $key,
                'message' => $message,
                'data' => $data,
            ],
        ]);
        Yii::$app->queue->post($task);
    }

    public static function notificationNewMessageToTutor(
        string $message,
        Account $student,
        Account $tutor,
        array $messageObject
    ) {
        $messageEmail = nl2br($message);
        $route = 'notification/new-message-tutor';
        $task = new Job([
            'route' => $route,
            'data' => [
                'studentId' => $student->id,
                'tutorId' => $tutor->id,
                'message' => $messageEmail,
                'messageObject' => $messageObject,
            ],
        ]);
        Yii::$app->queue->post($task);
    }

    public static function notificationNewMessageToTutorSms(
        string $message,
        Account $student,
        Account $tutor
    ) {
        $task = new Job([
            'route' => 'notification/new-message-tutor-sms',
            'data' => [
                'studentId' => $student->id,
                'tutorId' => $tutor->id,
                'message' => $message,
            ],
        ]);
        Yii::$app->queue->post($task);
    }

    public static function checkBooking(int $bookingId): void
    {
        \Yii::$app->yiiQueue
            //delay 1 hour
            ->delay(60 * 60)
            ->push(
                new CheckBookingJob(['bookingId' => $bookingId,])
            );
    }

    public static function createAccountFromBooking(int $bookingId): void
    {
        \Yii::$app->yiiQueue
            ->priority(Queue::PRIORITY_HIGH)
            ->push(
                new CreateAccountFromBookingJob(['bookingId' => $bookingId,])
            );
    }


    /**
     * Posting tasks to queue about notification applicants about job hire
     * @param $jobId
     * @param $tutorId
     * @param $initiatorId
     */
    public static function notifyApplicants(int $jobId, int $tutorId, ?int $initiatorId): void
    {
        $route = 'job/notify-not-applied';
        $data = [
            'jobId' => $jobId,
            'tutorId' => $tutorId,
            'initiatorId' => $initiatorId,
        ];
        $task = new \UrbanIndo\Yii2\Queue\Job(['route' => $route, 'data' => $data]);
        Yii::$app->queue->post($task);
    }

    public static function createTutorAgreementPdf(int $specialistId, string $date)
    {
        return \Yii::$app->yiiQueue->push(new CreateSpecialistAgreementPdfJob([
            'specialistId' => $specialistId,
            'date' => $date
        ]));
    }

    public static function sendSmsDownloadApp(int $tutorId): void
    {
        $route = 'notification/send-sms-download-app';
        $data = [
            'tutorId' => $tutorId,
        ];
        $task = new \UrbanIndo\Yii2\Queue\Job(['route' => $route, 'data' => $data]);
        Yii::$app->queue->post($task);
    }

    public static function recalculateTutoringHours($jobHireId)
    {
        $route = 'job/update-job-hire-tutoring-hours';
        $data = [
            'jobHireId' => $jobHireId,
        ];
        $task = new \UrbanIndo\Yii2\Queue\Job(['route' => $route, 'data' => $data]);
        Yii::$app->queue->post($task);
    }

    /**
     * Queue task to update tutor rating
     * @param int $accountId
     */
    public static function setTutorRating(int $accountId)
    {
        $task = new Job([
            'route' => 'tutor/set-tutor-rating',
            'data' => [
                'accountId' => $accountId,
            ],
        ]);
        Yii::$app->queue->post($task);
    }

    /**
     * Queue task to update tutor score
     * @param int $accountId
     */
    public static function setTutorScore(int $accountId)
    {
        $task = new Job([
            'route' => 'tutor/set-tutor-score',
            'data' => [
                'accountId' => $accountId,
            ],
        ]);
        Yii::$app->queue->post($task);
    }

    public static function sendBookingConversationEmails(TutorBooking $booking): void
    {
        if (!empty(\Yii::$app->settings->get('book', 'notificationEmails'))) {
            $emails = \Yii::$app->settings->get('book', 'notificationEmails');
            $content = '';
            foreach ($booking->mailNotificationContentArray as $name => $value) {
                $content .= "$name : $value\n";
            }
            $firstMail = array_shift($emails);
            \Yii::$app->yiiQueue->push(
                new MailerJob(
                    [
                        'email' => $firstMail,
                        'subject' => 'Online landing page conversion',
                        'contentHtml' => nl2br($content),
                        'bccEmail' => $emails
                    ]
                )
            );
        }
    }

    public static function sendSms(Sms $sms): void
    {
        $task = new Job([
            'route' => 'sms/send',
            'data' => [
                'smsId' => $sms->id,
            ],
        ]);
        Yii::$app->queue->post($task);
        $sms->status = Sms::STATUS_ADDED_TO_QUEUE;
        $sms->save(false);
    }

    public static function sendNotificationToPusher($channel, $event, $data, $socketId)
    {
        \Yii::$app->yiiQueue->push(
            new PusherNotificationJob([
                'channel' => $channel,
                'event' => $event,
                'data' => $data,
                'socketId' => $socketId,
            ])
        );
    }
    public static function sendFollowUpReportEmail(int $smsId)
    {
        $task = new Job([
            'route' => 'notification/user-response-email-to-admin',
            'data' => [
                'smsId' => $smsId,
            ],
        ]);
        Yii::$app->queue->post($task);
    }

    public static function processCharge($transaction, $priority = Queue::PRIORITY_HIGH)
    {
        /**
         * @var Transaction $transaction
         */
        if (!$transaction->hasExternalId()) {
            \Yii::$app->yiiQueue
                ->priority($priority)
                ->push(
                    new ChargeProcessJob(['transactionId' => $transaction->id,])
                );
        } else {
            Yii::error(
                "Transaction {$transaction->id} has external id, new charge process job wasn't created.",
                'charge'
            );
        }
    }

    public static function automatchJob(int $jobId, $zeroDelay = false): int
    {
        return \Yii::$app->yiiQueue
            ->priority(Queue::PRIORITY_HIGH)
            //delay 3 hours
            ->delay($zeroDelay ? 0 : Automatch::QUEUE_DELAY)
            ->push(
                new AutomatchJob(['jobId' => $jobId])
            );
    }

    public static function sendLessonEmail(int $lessonId)
    {
        $task = new Job([
            'route' => 'notification/send-lesson-email',
            'data' => [
                'lessonId' => $lessonId,
            ],
        ]);
        Yii::$app->queue->post($task);
    }

    public static function sendAccountInactiveEmail(int $round, int $delay = null): void
    {
        Yii::$app->yiiQueue
            ->delay($delay)
            ->push(new SendAccountInactiveEmailJob(['round' => $round]));
    }

    public static function testAccountInactiveEmail(
        int $round,
        bool $isOneJob = false,
        array $emails = [],
        int $delay = null
    ): void {
        Yii::$app->yiiQueue
            ->delay($delay)
            ->push(
                new TestSendAccountInactiveEmailJob(
                    [
                        'round' => $round,
                        'emails' => $emails,
                        'isOneJob' => $isOneJob,
                    ]
                )
            );
    }

    /**
     * @param int $accountId
     */
    public static function calculateTutorProfileUniqueWordsCount(int $accountId): void
    {
        \Yii::$app->yiiQueue
            ->push(
                new CalculateTutorProfileUniqueWordsJob(['accountId' => $accountId])
            );
    }
}
