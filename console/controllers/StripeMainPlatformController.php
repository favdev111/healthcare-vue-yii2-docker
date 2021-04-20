<?php

namespace console\controllers;

use modules\payment\models\PaymentProcess;
use Stripe\Account;
use Stripe\Stripe;
use yii\console\Controller;
use yii\helpers\Console;

class StripeMainPlatformController extends Controller
{
    public function actionCreateFirstPayment()
    {
        if (PaymentProcess::find()->exists()) {
            Console::output('First platform payout has already been created.');
            return;
        }

        if (\Yii::$app->stripePlatformAccount->createFirstPaymentProcess()) {
            Console::output('Created!');
        } else {
            Console::output('Failed! Something went wrong.');
        }
    }

    public function actionPaymentProcess()
    {
        \Yii::$app->stripePlatformAccount->runPaymentProcess();
    }

    public function actionBalance()
    {
        Console::output(\Yii::$app->payment->retrievePlatformBalanceObject()->toJSON());
    }

    public function actionShowConnectedAccountEntity($stripeAccountId)
    {
        Stripe::setApiKey(\Yii::$app->payment->privateKey);
        $account = Account::retrieve($stripeAccountId);
        Console::output(json_encode($account));
    }

    public function actionSetDebitNegativeBalance($stripeAccountId, $value)
    {
        Stripe::setApiKey(\Yii::$app->payment->privateKey);
        $value = (bool)$value;
        $account = Account::update($stripeAccountId, [
            'debit_negative_balances' => $value,
        ]);
        Console::output(json_encode($account));
    }
}
