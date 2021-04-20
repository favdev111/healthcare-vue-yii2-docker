<?php

namespace modules\payment\components\parts;

use modules\payment\components\Payment;
use modules\payment\models\PaymentBankAccount;
use modules\payment\models\PaymentCustomer;
use Stripe\Customer;
use Stripe\Error\Card;
use Yii;

class Ach extends \yii\base\BaseObject
{
    /**
     * @var Payment
     */
    public $paymentComponent;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!$this->paymentComponent) {
            throw new \Exception('Payment component should be provided.');
        }
    }

    public function attachBankAccountToCustomer($bankToken, $account)
    {
        $paymentCustomerModel = PaymentCustomer::findOne(['accountId' => $account->id]);
        $paymentBankAccount = new PaymentBankAccount();

        if (empty($paymentCustomerModel->customerId)) {
            try {
                $paymentCustomerModel = $this->paymentComponent->createPaymentCustomer($account, $paymentCustomerModel);
            } catch (\Exception $e) {
                Yii::error('Failed to create payment customer. Exception: ' . $e->getTraceAsString(), 'payment');
                $paymentBankAccount->addError('', 'Failed to create payment account. Please try again or contact us.');
                return $paymentBankAccount;
            }
        }

        try {
            $stripeCustomer = Customer::retrieve($paymentCustomerModel->customerId);
            $bankAccount = $stripeCustomer->sources->create(['source' => $bankToken]);
        } catch (\Exception $e) {
            Yii::error('Failed to create payment bank account.' . $e->getMessage() . '  Stripe Customer ID: ' . $paymentCustomerModel->customerId . ' Bank Account Token: ' . $bankToken . ' Exception: ' . $e->getTraceAsString(), 'payment');
            $paymentBankAccount->addError('', 'Failed to create payment bank account. Please try again or contact us.');
            return $paymentBankAccount;
        }

        $paymentBankAccount->bank_name = $bankAccount->bank_name;
        $paymentBankAccount->last4 = $bankAccount->last4;
        $paymentBankAccount->paymentBankId = $bankAccount->id;
        $paymentBankAccount->paymentCustomerId = $paymentCustomerModel->id;
        // Bank account can not be set to Active unless it is verified.
        $paymentBankAccount->active = false;
        $paymentBankAccount->save();
        return $paymentBankAccount;
    }

    public function verifyBankAccount($stripeCustomerId, $bankAccountId, $deposit1, $deposit2, &$error = null)
    {
        $stripeCustomer = \Stripe\Customer::retrieve($stripeCustomerId);
        if (!$stripeCustomer) {
            Yii::error('No stripe customer found. Stripe Customer ID: ' . $stripeCustomerId);
            return false;
        }
        $bankAccount = $stripeCustomer->sources->retrieve($bankAccountId);
        if (!$bankAccount) {
            $error = 'No such stripe bank account found.';
            Yii::error('No stripe bank account found. Stripe Customer ID: ' . $stripeCustomerId . ' Bank Account ID: ' . $bankAccountId, 'payment');
            return false;
        }
        $deposit1StripeValue = $deposit1;
        $deposit2StripeValue = $deposit2;
        try {
            return $bankAccount->verify(['amounts' => [$deposit1StripeValue, $deposit2StripeValue]]);
        } catch (Card $e) {
            if ($e->getHttpStatus() === 402) {
                $error = $e->getMessage();
            }
            return false;
        } catch (\Exception $e) {
            Yii::error('Failed to verify stripe payment bank account: ' . $stripeCustomerId . ' Bank Account ID: ' . $bankAccountId . ' Exception: ' . $e->getTraceAsString(), 'payment');
            return false;
        }
    }
}
