<?php

namespace modules\payment\models\api2Patient\forms;

use common\components\validators\PaymentCardsCountValidator;
use yii\base\Model;

class CreateCardForm extends Model
{
    public $paymentCardTokens;
    public $activeCardToken;
    public function rules()
    {
        return [
            ['paymentCardTokens', 'required'],
            ['activeCardToken', 'string'],
            [['paymentCardTokens'], 'each', 'rule' => ['string']],
            [
                ['paymentCardTokens'],
                PaymentCardsCountValidator::class,
                'account' => \Yii::$app->user->identity,
                'maxCount' => \Yii::$app->payment::PATIENT_MAX_CARD_COUNT
            ],
        ];
    }
}
