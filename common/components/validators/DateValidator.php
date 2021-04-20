<?php

namespace common\components\validators;

class DateValidator extends \yii\validators\DateValidator
{
    public $format = 'php:m/d/Y';
}
