<?php

namespace common\components\validators;

use yii\validators\RegularExpressionValidator;

/**
 * Class LicenseValidator
 * @package common\components\validators
 */
class LicenseValidator extends RegularExpressionValidator
{
    /**
     * @var string
     */
    public $message = 'Incorrect licence format for state';
    /**
     * @var string
     */
    public $pattern = '/^[a-zA-Z\s.\d-]+$/';
}
