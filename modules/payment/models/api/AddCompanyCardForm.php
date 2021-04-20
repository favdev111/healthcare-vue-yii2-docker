<?php

namespace modules\payment\models\api;

use modules\account\models\Account;
use Yii;
use yii\base\Model;
use yii\web\HttpException;

class AddCompanyCardForm extends Model
{
    public $cardToken;

    public function rules()
    {
        return array_merge(
            [
                [['cardToken'], 'required']
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

        $card = Yii::$app->payment->attachCardToCustomer(
            $this->cardToken,
            $account
        );

        return $card;
    }
}
