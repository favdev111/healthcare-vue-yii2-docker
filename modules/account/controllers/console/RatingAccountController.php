<?php

namespace modules\account\controllers\console;

use common\models\Review;
use modules\account\helpers\TutorHelper;
use modules\account\models\AccountRating;
use modules\chat\models\ChatMessage;
use Yii;
use common\helpers\Role;
use modules\account\models\Account;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class RatingAccountController extends Controller
{
    public function actionSetTutorRating()
    {
        $tutorsQuery = Account::find()
            ->select(['id'])
            ->andWhere(['roleId' => Role::ROLE_SPECIALIST, 'status' => Account::STATUS_ACTIVE])
            ->asArray()
        ;
        $count = 0;
        $countTotal = $tutorsQuery->count();

        Console::startProgress(0, $countTotal);

        foreach ($tutorsQuery->each(1000) as $data) {
            TutorHelper::setTutorRating($data['id']);
            Console::updateProgress(++$count, $countTotal);
        }

        Console::endProgress(true);
    }

    public function actionSetStudentHours()
    {
        $students = Account::findAll(['roleId' => Role::ROLE_PATIENT, 'status' => Account::STATUS_ACTIVE]);
        foreach ($students as $student) {
            $accRating = AccountRating::findOne(['accountId' => $student->id]);
            if (!$accRating) {
                $accRating = new AccountRating();
                $accRating->accountId = $student->id;
            }

            $totalTime = Yii::$app->db->createCommand('SELECT ROUND(SUM(TIME_TO_SEC(TIMEDIFF(toDate, fromDate))/3600)) as totalHours FROM lesson WHERE studentId=' . $student->id)
                ->queryOne();
            $totalStudent = Yii::$app->db->createCommand('SELECT count(DISTINCT tutorId) as count FROM lesson WHERE studentId=' . $student->id)
                ->queryOne();
            $accRating->totalAccounts = $totalStudent['count'];
            $accRating->totalHours = $totalTime['totalHours'];
            $accRating->save();
        }

        return ExitCode::OK;
    }

    /**
     * Activating reviews with New status
     */
    public function actionActivatePendingReviews()
    {
        $twoDaysEarlier = date('Y-m-d H:i:s', strtotime('-2 days'));
        $reviews = Review::find()->andWhere([
            'and',
            ['status' => Review::NEW],
            ['<', 'createdAt', $twoDaysEarlier]
        ])->all();
        $moduleAccount = Yii::$app->getModule('account');
        foreach ($reviews as $review) {
            $moduleAccount->eventLeavedReview($review);
        }
        Review::updateAll(
            [
                'status' => Review::ACTIVE,
            ],
            [
                'and',
                ['status' => Review::NEW],
                ['<', 'createdAt', $twoDaysEarlier]
            ]
        );
    }

    /**
     * Caclulate tutors average response time for the previous week
     */
    public function actionWeeklyResponseTime()
    {
        // TODO: Find a more strait forward way to get beginning and end
        $beginningOfWeek = strtotime("midnight", strtotime('-7 days'));
        $endOfYesterday = strtotime("midnight") - 1;

        Yii::info('Starting Response time calculation for a period ' . Yii::$app->formatter->asDatetime($beginningOfWeek) . ' - ' . Yii::$app->formatter->asDatetime($endOfYesterday), 'response-time');

        $tutors = Account::findAll(['roleId' => Role::ROLE_SPECIALIST, 'status' => Account::STATUS_ACTIVE]);
        foreach ($tutors as $tutor) {
            $accRating = AccountRating::findOne(['accountId' => $tutor->id]);
            if (!$accRating) {
                // Waiting for account rating item to be created first
                continue;
            }

            $responseTime = $this->calculateResponseTime($tutor->chat->chatUserId, $beginningOfWeek, $endOfYesterday);
            Yii::info('Tutor #' . $tutor->id . ' response time for the week is ' . $responseTime, 'response-time');
            if ($responseTime !== null) {
                if ($accRating->avgResponseTime !== null) {
                    // In case we have a previous value - calculate the AVG between them
                    $accRating->avgResponseTime = ($accRating->avgResponseTime + $responseTime) / 2;
                } else {
                    // Otherwise just set a new value
                    $accRating->avgResponseTime = $responseTime;
                }
                if (!$accRating->save()) {
                    Yii::error(
                        'Failed to save calculated rating totals for tutor #'
                        . $tutor->id
                        . ' Errors: '
                        . json_encode($accRating->getErrors()),
                        'rating'
                    );
                    continue;
                }
                Yii::info('New Tutor #' . $tutor->id . ' response time is ' . $accRating->avgResponseTime, 'response-time');
                Yii::$app->getModule('account')->updateTutorSearchIndex($tutor->id, ['responseTime' => $accRating->avgResponseTime]);
            }
        }
        return ExitCode::OK;
    }

    /**
     * Calculate average response time by tutor Chat ID for provided time period
     * @param $tutorChatUserId
     * @param $from integer From timestamp
     * @param $to integer To timestamp
     * @return float|null
     */
    protected function calculateResponseTime($tutorChatUserId, $from, $to)
    {
        $responseTime = null;

        // TODO: find a better way instead of fetching all the chats
        $tutorChatMessages = ChatMessage::find()
            ->andWhere([
                'or',
                ['sender_id' => $tutorChatUserId],
                ['recipient_id' => $tutorChatUserId],
            ])
            ->orderBy(['chat_dialog_id' => SORT_ASC, 'date_sent' => SORT_ASC])
            ->andWhere(['between', 'date_sent', $from, $to]);

        $filteredMessages = [];

        foreach ($tutorChatMessages->each(100) as $message) {
            /**
             * @var $message ChatMessage
             */
            if (!isset($filteredMessages[$message->chat_dialog_id])) {
                if ($message->sender_id == $tutorChatUserId) {
                    // Do not use messages if tutor sent it first
                    continue;
                }
                // In case this is a first message in dialog from student
                $filteredMessages[$message->chat_dialog_id] = $message->date_sent;
            } else {
                // In case it is not the first message in dialog
                if ($message->sender_id == $tutorChatUserId) {
                    // Calculate response time based on 2 latest messages in dialog
                    $currentResponseTime = $message->date_sent - $filteredMessages[$message->chat_dialog_id];
                    unset($filteredMessages[$message->chat_dialog_id]);

                    // Re-calculate / Fill response time using newly calculated value
                    if ($responseTime !== null) {
                        $responseTime = ($responseTime + $currentResponseTime) / 2;
                    } else {
                        $responseTime = $currentResponseTime;
                    }
                } else {
                    // Update time to the latest message
                    $filteredMessages[$message->chat_dialog_id] = $message->date_sent;
                }
            }
        }
        // Calculate response time for all non-answered messages too
        foreach ($filteredMessages as $dialogId => $date_sent) {
            $passedTime = $to - $date_sent;
            // Re-calculate avg time only if passed time is greater than calculated one (in order not to increase Avg time)
            if ($responseTime !== null) {
                if ($responseTime < $passedTime) {
                    $responseTime = ($responseTime + $passedTime) / 2;
                }
            } else {
                $responseTime = $passedTime;
            }
        }
        return $responseTime;
    }
}
