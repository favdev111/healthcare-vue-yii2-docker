<?php

namespace common\components\validators;

use yii\validators\Validator;

class PaymentCardsCountValidator extends Validator
{
    public $account;
    public $maxCount;
    public function validateAttribute($model, $attribute)
    {
        $account = $this->account ?? null;
        if (empty($account) || empty($account->paymentCustomer)) {
            $this->addError($model, $attribute, 'Failed to validate cards count.');
        }
        $count = 0;
        $paymentCustomerModel = $account->paymentCustomer;
        if ($paymentCustomerModel) {
            $count = $paymentCustomerModel->getCardInfo()->count();
        }

        if (is_array($model->$attribute)) {
            $count += count($model->$attribute);
        }

        if ($count > $this->maxCount) {
            $this->addError($model, $attribute, 'You can add only 10 credit cards');
        }
    }
}
