<?php

namespace common\components;

use yii\helpers\Html;

/**
 * Class HtmlPurifier
 * @package common\components
 */
class HtmlPurifier extends \yii\helpers\HtmlPurifier
{
    const EMPTY_STRING = '';

    /**
     * @inheritdoc
     * @param string $content
     * @param null $config
     * @return string
     */
    public static function process($content, $config = null)
    {
        return Html::decode(parent::process($content, $config));
    }

    /**
     * @param $content
     * @param null $config
     * @return string
     */
    public static function encodeStringWithTags($content, $config = null)
    {
        $processed = static::process(trim($content), $config);
        if ($processed === static::EMPTY_STRING) {
            return Html::encode($content);
        }
        return $processed;
    }
}
