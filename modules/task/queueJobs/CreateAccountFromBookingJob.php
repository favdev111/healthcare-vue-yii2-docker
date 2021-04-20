<?php

namespace modules\task\queueJobs;

use common\helpers\QueueHelper;
use console\components\Queue;
use modules\account\models\Account;
use modules\account\models\api\AccountClient;
use modules\account\models\forms\ProfileClientForm;
use modules\account\models\TutorBooking;
use modules\payment\models\Transaction;
use modules\task\components\RetryableJob;
use yii\base\Exception;

class CreateAccountFromBookingJob extends RetryableJob
{

    public $bookingId;
    public function execute($queue)
    {
        try {
            \Yii::info('Create account from booking:' . $this->bookingId, 'lead');
            if (empty($this->bookingId)) {
                throw new Exception('Booking id is empty');
            }

            $booking = TutorBooking::findOne($this->bookingId);
            if (empty($booking)) {
                throw new Exception("Booking with id {$this->bookingId} hasn\'t been found.");
            }

            $companyAccount = Account::findOne($booking->bookingCompanyId);
            if (empty($companyAccount)) {
                throw new Exception('Company account for booking wizard was not found.');
            }

            // in case of new client - create account, payment nethod will be added in ProfileClientForm()
            if (empty($booking->accountId)) {
                $client = new ProfileClientForm();
                $client->createdIp = $booking->ip;
                $client->setAttributes($booking->attributes, false);
                $client->setScenario('book');
                $client->accountCompanyModel =  $companyAccount;
                $client->note = $booking->fullNote;
                $client->phoneNumbers = [['phoneNumber' => $booking->phoneNumber, 'isPrimary' => true]];
                $client->emails = [['email' => $booking->email, 'isPrimary' => true]];
                $client->flag = '';
                $client->subjects = [$booking->subjects];
                $createdAccount = $client->create();
                if (empty($createdAccount)) {
                    throw new Exception('Failed to create account.Something went wrong.');
                }
                $booking->accountId = $createdAccount->id;

                /**
                 * @var $createdAccount AccountClient
                 */
                $createdAccount->paymentCustomer->autorenew = false;
                $createdAccount->paymentCustomer->save();

                \Yii::info('New account created with id: ' . $createdAccount->id, 'lead');
            //in case of client is already exists - just add payment method
            } elseif (!empty($booking->paymentAdd)) {
                \Yii::info('Account is already exists, trying to add payment method.', 'lead');
                $account = Account::findOne($booking->accountId);
                if (empty($account)) {
                    throw new Exception('Unable to update payment method. Account not found.');
                }
                foreach ($booking->paymentAdd as $cardToken) {
                    if (empty($cardToken)) {
                        continue;
                    }
                    \Yii::$app->payment->attachCardToCustomer($cardToken, $account);
                }
            }

            //chargeId returns from createCharge().
            // Charge creation disabled https://app.clubhouse.io/heytutor/story/336/adjustments-to-online-landing-page
            if (!empty($chargeId)) {
                \Yii::info(
                    "Charge with $chargeId created for booking with id {$booking->id}:",
                    'lead'
                );
            }
            return $booking->save(false);
        } catch (\Throwable $exception) {
            \Yii::error(
                'Error during account creation: '
                . $exception->getMessage() . "\n" . $exception->getTraceAsString(),
                'lead'
            );
            return false;
        }
    }

    private function createCharge($client, $amount)
    {
        $transaction = new Transaction([
            'studentId' => $client->id,
            'tutorId' => null,
            'objectId' => $client->id,
            'objectType' => Transaction::TYPE_CLIENT_BALANCE_MANUAL_CHARGE,
            'amount' => $amount,
            'type' => Transaction::STRIPE_CHARGE,
            'processDate' => date('Y-m-d'),
        ]);
        if (!$transaction->save(false)) {
            \Yii::error(
                'Error charge creation:',
                'lead'
            );
            return false;
        }

        QueueHelper::processCharge($transaction, Queue::PRIORITY_HIGHEST);
        return $transaction->id;
    }
}
