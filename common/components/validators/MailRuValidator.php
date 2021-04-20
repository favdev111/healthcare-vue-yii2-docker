<?php

namespace common\components\validators;

use Yii;
use yii\validators\Validator;

class MailRuValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $rexp = '/.*@mail.ru\w*/';
        if (preg_match($rexp, $model->$attribute)) {
            $model->addError($attribute, "Email address is not a valid email address");
        }
    }
}
