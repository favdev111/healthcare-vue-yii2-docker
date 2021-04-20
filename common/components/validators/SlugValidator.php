<?php

namespace common\components\validators;

use yii\validators\RegularExpressionValidator;

/**
 * Class SlugValidator
 * @package common\components\validators
 */
class SlugValidator extends RegularExpressionValidator
{
    /**
     * @var string
     */
    public $pattern = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
}
