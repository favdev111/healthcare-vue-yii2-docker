<?php

namespace console\controllers;

use modules\account\models\Account;
use modules\account\models\PaymentAccount;
use modules\account\models\Profile;
use modules\chat\models\Chat;
use modules\chat\models\ChatMessage;
use modules\payment\components\Payment;
use modules\payment\models\BankAccount;
use modules\payment\models\CardInfo;
use modules\payment\models\PaymentAccountBalance;
use modules\payment\models\PaymentCustomer;
use modules\payment\models\Transaction;
use modules\payment\models\TransactionBalance;
use modules\payment\models\TransactionTransfer;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Expression;
use yii\helpers\Console;

class StagingController extends Controller
{
    public function actionUpdateFromLive()
    {
        if (YII_ENV_PROD) {
            Console::error('Can not run for prod');
            return;
        }

        $passwordHash = Account::generatePasswordHash('123456');
        Account::updateAll(
            [
                'email' => new Expression("CONCAT( 'qa+ht-" . YII_ENV . "-', CASE WHEN roleId = 1 THEN 'st' WHEN roleId = 2 THEN 'tu' WHEN roleId = 7 THEN 'co' ELSE 'hz' END, '-', id , '@eltexsoft.com')"),
                'passwordHash' => $passwordHash,
            ]
        );
        Profile::updateAll(
            [
                'phoneNumber' => '5005550006',
            ]
        );

        Yii::$app->db->createCommand()->truncateTable(ChatMessage::tableName());
        Yii::$app->db->createCommand()->truncateTable(Chat::tableName());

        Yii::$app->db->createCommand()->truncateTable('{{%queue}}');

        Yii::$app->db->createCommand()->truncateTable(BankAccount::tableName());
        Yii::$app->db->createCommand()->truncateTable(PaymentAccountBalance::tableName());
        Yii::$app->db->createCommand()->truncateTable(PaymentAccount::tableName());
        Yii::$app->db->createCommand()->truncateTable(CardInfo::tableName());
        Yii::$app->db->createCommand()->truncateTable(PaymentCustomer::tableName());

        Yii::$app->db->createCommand()->truncateTable(TransactionBalance::tableName());
        Yii::$app->db->createCommand()->truncateTable(TransactionTransfer::tableName());
        Transaction::deleteAll(['not in', 'status', [1, 2, 3, 4]]);

        // Add users to chat
        $this->run('chat/default/add-user');

        // Update elasticsearch
        $this->run('account/elasticsearch/recreate-all', ['confirm' => false]);
    }


    public function actionEnsureTutorPaymentAccount()
    {
        $tutors = Account::find()
            ->joinWith('paymentAccount')
            ->andWhere(['is', PaymentAccount::tableName() . '.accountId', null])
            ->tutor();
        /**
         * @var Payment $payment
         */
        $payment = Yii::$app->payment;
        foreach ($tutors->each() as $tutor) {
                $payment->createAccount($tutor->id);
        }

        return ExitCode::OK;
    }
}
