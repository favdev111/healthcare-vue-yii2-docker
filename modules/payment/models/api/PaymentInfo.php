<?php

namespace modules\payment\models\api;

use Yii;
use yii\base\Model;

/**
 * PaymentInfo is the model behind the login form.
 */
class PaymentInfo extends \modules\account\models\forms\PaymentInfo
{
    public function rules()
    {
        $rules = parent::rules();
        unset($rules['placeId']);
        return $rules;
    }
}
