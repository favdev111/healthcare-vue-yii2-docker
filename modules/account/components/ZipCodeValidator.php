<?php

namespace modules\account\components;

use common\models\Zipcode;
use yii\validators\Validator;
use Yii;

class ZipCodeValidator extends Validator
{
    public $required = false;
    public $pattern = '/^\d{5}$/';
    public $message = 'Zip code is invalid. Please enter a valid zip code.';

    protected function validateValue($value)
    {
        if (
            (
                $this->required
                && $this->isEmpty($value)
            )
            || !preg_match($this->pattern, $value)
            || !Zipcode::find()->andWhere(['code' => $value])->exists()
        ) {
            return [$this->message, []];
        }

        return null;
    }
}
