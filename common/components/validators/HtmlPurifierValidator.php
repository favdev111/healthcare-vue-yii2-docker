<?php

namespace common\components\validators;

use common\components\HtmlPurifier;
use yii\validators\Validator;

class HtmlPurifierValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $value = trim($model->$attribute);
        $value = HtmlPurifier::process($value, ['HTML.Allowed' => '']);
        $model->$attribute = $value;
    }
}
