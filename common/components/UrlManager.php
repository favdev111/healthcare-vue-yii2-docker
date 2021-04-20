<?php

namespace common\components;

use Yii;

/**
 * @inheritdoc
 */
class UrlManager extends \yii\web\UrlManager
{
    public $b2bPath;

    public function init()
    {
        parent::init();

        if (!$this->b2bPath) {
            $this->b2bPath = '/enterprise-offering/';
        }
    }
}
