<?php

namespace common\components\validators;

class EmailValidator extends \yii\validators\EmailValidator
{
    public $checkDNS = true;
}
