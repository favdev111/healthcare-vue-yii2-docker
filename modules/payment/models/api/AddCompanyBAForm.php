<?php

namespace modules\payment\models\api;

use modules\account\models\Account;
use Yii;
use yii\base\Model;

class AddCompanyBAForm extends Model
{
    public $bankToken;

    public function rules()
    {
        return array_merge(
            [
                [['bankToken'], 'required']
            ],
            $this->companyIdValidationRules()
        );
    }

    public function save($validate = true)
    {
        if ($validate && !$this->validate()) {
            return false;
        }

        if ($this->companyId) {
            $account = Account::findOne($this->companyId);
        } else {
            $account = \Yii::$app->user->identity;
        }

        $paymentBankAccount = Yii::$app->payment->ach->attachBankAccountToCustomer(
            $this->bankToken,
            $account
        );

        return $paymentBankAccount;
    }
}
