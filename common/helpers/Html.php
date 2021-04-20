<?php

namespace common\helpers;

use Yii;

class Html extends \yii\helpers\Html
{
    public static function tel(string $phoneNumber = null, array $options = [])
    {
        $options['href'] = 'tel:';
        if (is_null($phoneNumber)) {
            $phoneNumber = Yii::$app->phoneNumber->getPhoneNumberFormatted();
            $options['href'] .= Yii::$app->phoneNumber->getPhoneNumber();
        } else {
            $options['href'] .= preg_replace('[^0-9+]', '', $phoneNumber);
        }

        if (!isset($options['data-analytics'])) {
            $options['data-analytics'] = 'phone';
        }

        return static::tag('a', $phoneNumber, $options);
    }
}
