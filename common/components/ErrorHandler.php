<?php

namespace common\components;

use Yii;

/**
 * Class ErrorHandler
 * @package common\components
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
    protected function renderException($exception)
    {
        Yii::$app->isResponsive = true;
        parent::renderException($exception);
    }
}
