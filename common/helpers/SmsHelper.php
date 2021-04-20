<?php

namespace common\helpers;

use common\components\StringHelper;
use modules\sms\components\Sms;
use Yii;
use yii\base\Exception;

/**
 * Class SmsHelper
 * @package common\helpers
 */
class SmsHelper
{
    const DOTS = '...';
    const SMS_STRING_LIMIT = 157;

    public static function send($to, $message, $type, $accountId = null)
    {
        if (empty($to) || empty($message)) {
            return false;
        }
        /**
         * @var Sms $component
         */
        $component = \Yii::$app->sms;
        $sms = $component->createSms($message, $to, $type, $accountId);
        return $component->sendSms($sms);
    }

    public static function clear(string $phoneNumber): string
    {
        return preg_replace('/[^0-9]/', '', $phoneNumber);
    }

    public static function truncateMessage($parts, $replacements, $maximumLength = 160)
    {
        $dotStringLength = strlen(static::DOTS);
        if ($maximumLength < $dotStringLength) {
            throw new Exception('You can not truncate messages to less than 3 symbols');
        }
        $baseLength = strlen(join('', $parts));
        if ($baseLength > $maximumLength) {
            return StringHelper::truncate(join('', $parts), $maximumLength - $baseLength - $dotStringLength, static::DOTS);
        }

        foreach ($parts as $key => &$part) {
            if ($part != '') {
                continue;
            }
            $currentLength = strlen(join('', $parts));
            $replacement = array_shift($replacements);
            $leftLength = $maximumLength - $currentLength;
            if ($leftLength <= 0) {
                continue;
            }
            $replacementLength = strlen($replacement);
            $elipsis = static::DOTS;
            if ($replacementLength < $dotStringLength || $leftLength < $dotStringLength) {
                $elipsis = '';
            }
            $part = StringHelper::truncate($replacement, ($leftLength - strlen($elipsis)), $elipsis);
        }

        return join('', $parts);
    }
}
