<?php

namespace modules\task\queueJobs;

use common\helpers\QueueHelper;
use modules\account\models\Account;
use modules\task\components\RetryableJob;
use yii\db\Expression;

class SendAccountInactiveEmailJob extends RetryableJob
{
    const LAST_ROUND = 4;
    const FIRST_ROUND = 1;
    public $round;

    /**
     * Select condition for search tutors.
     * In case first round select all inactive tutors for last 30 days (using +1 day)
     * Otherwise look for tutors who received (round - 1) letters
     * @return \modules\account\models\query\AccountQuery
     */
    public function getSearchCondition(): \modules\account\models\query\AccountQuery
    {
        $query = Account::find()
            ->tutor()
            ->byActiveStatus()
            ->andWhere(['inactiveEmailAnswer' => null])
            ->joinWith('clientStatistic');
        if ($this->isFirstRound()) {
            return $query
                ->andWhere(new Expression('lastVisit < DATE_SUB(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 30 DAY)'));
        } else {
            return $query->andWhere(['countAccountInactiveEmails' => ($this->round - 1)]);
        }
    }

    public function createNextJob()
    {
        if (!$this->isLastRound()) {
            $nextRound = $this->round + 1;
            //delay - 2 days
            QueueHelper::sendAccountInactiveEmail($nextRound, 60 * 60 * 24 * 2);
        }
    }

    /**
     * @return bool
     */
    protected function isLastRound(): bool
    {
        return $this->round === static::LAST_ROUND;
    }

    /**
     * @return bool
     */
    protected function isFirstRound(): bool
    {
        return $this->round === static::FIRST_ROUND;
    }

    public function execute($queue)
    {
        try {
            \Yii::info('Round: ' . $this->round, 'inactiveAccountEmails');
            $query = $this->getSearchCondition();

            $count = (clone($query))->count();
            \Yii::info('Total tutors found: ' . $count, 'inactiveAccountEmails');
            $disabled = [];
            $sentTo = [];
            /**
             * @var Account $tutor
             */
            foreach ($query->each() as $tutor) {
                $statistic = $tutor->clientStatistic;

                // if the tutor does not respond to any of the 3 emails, the account should be set to “inactive”
                if (
                    $this->isLastRound()
                    || (
                        $statistic->countAccountInactiveEmails == (static::LAST_ROUND - 1)
                        && is_null($statistic->inactiveEmailAnswer)
                    )
                ) {
                    $tutor->status = Account::STATUS_BLOCKED;
                    $tutor->setBlockedReasonInactive();
                    $tutor->save(false);
                    $disabled[] = $tutor->id;
                } else {
                    $tutor->sendInactiveAccountEmail($this->round);
                    $statistic->countAccountInactiveEmails++;
                    $statistic->save(false);
                    $sentTo[] = $tutor->id;
                }
            }
            \Yii::info('Total emails sent: ' . count($sentTo), 'inactiveAccountEmails');
            \Yii::info('Account list: ' . json_encode($sentTo), 'inactiveAccountEmails');
            \Yii::info('Total account disabled: ' . count($disabled), 'inactiveAccountEmails');
            \Yii::info('Account list: ' . json_encode($disabled), 'inactiveAccountEmails');

            $this->createNextJob();
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage() . "\n" . $exception->getTraceAsString(), 'inactiveAccountEmails');
        }
    }
}
