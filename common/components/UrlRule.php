<?php

namespace common\components;

use Yii;

/**
 * @inheritdoc
 */
class UrlRule extends \yii\web\UrlRule
{
    public function getPlaceholders()
    {
        return $this->placeholders;
    }
}
