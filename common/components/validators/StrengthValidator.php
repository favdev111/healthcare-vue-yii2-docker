<?php

namespace common\components\validators;

class StrengthValidator extends \kartik\password\StrengthValidator
{
    public $min = 8;
    public $upper = 1;
    public $lower = 1;
    public $digit = 1;
    public $special = 1;
    public $hasUser = true;
    public $hasEmail = true;
}
