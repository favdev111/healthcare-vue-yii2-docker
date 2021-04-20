<?php

namespace common\components\validators;

class BirthdayValidator extends \yii\validators\DateValidator
{
    public $format = 'php:m/d/Y';

    public function init()
    {
        if ($this->min !== null && is_int($this->min)) {
            $this->min = date('m/d/Y', strtotime('-' . $this->min . ' year'));
        }

        if ($this->max !== null && is_int($this->max)) {
            $this->max = date('m/d/Y', strtotime('-' . $this->max . ' year'));
        }

        parent::init();
    }
}
