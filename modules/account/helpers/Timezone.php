<?php

namespace modules\account\helpers;

use DateTime;
use DateTimeZone;
use yii\helpers\ArrayHelper;

class Timezone
{
    const SERVER_TIMEZONE = 'UTC';
    /**
     * Get all of the time zones with the offsets sorted by their offset
     * @return array
     */
    public static function getAll()
    {
        $timezones = [];
        $identifiers = DateTimeZone::listIdentifiers();
        foreach ($identifiers as $identifier) {
            $date = new DateTime("now", new DateTimeZone($identifier));
            $offsetText = $date->format("P");
            $offsetInHours = $date->getOffset() / 60 / 60;
            $timezones[] = [
                "identifier" => $identifier,
                "name" => "(GMT{$offsetText}) $identifier",
                "offset" => $offsetInHours
            ];
        }

        ArrayHelper::multisort($timezones, "offset", SORT_ASC, SORT_NUMERIC);
        return $timezones;
    }

    /**
     * Convert user date to server timezone
     * @param $value string date to be converted
     * @param $format string the format of the converted date
     * @param $setHour int set time before convert to server timezone
     * @return string converted value
     */
    public function convertToServerTimeZone($value, $format = 'm/d/Y H:i', $setHour = null)
    {
        $dateTime = new \DateTime($value, new \DateTimeZone(\Yii::$app->formatter->timeZone));
        if (!empty($setHour)) {
            $dateTime->setTime($setHour, 0);
        }
        // TODO: Find a way to determine appropriate timezone instead of hardcoding it
        $dateTime->setTimezone(new \DateTimeZone(self::SERVER_TIMEZONE));
        return $dateTime->format($format);
    }

    public static function staticConvertToServerTimeZone($value, $format = 'm/d/Y H:i', $setHour = null)
    {
        return (new self())->convertToServerTimeZone($value, $format, $setHour);
    }

    public static function staticConvertFromServerTimeZone($value, $format = 'Y-m-d H:i:s')
    {
        $dateTime = new \DateTime($value, new \DateTimeZone(self::SERVER_TIMEZONE));
        // TODO: Find a way to determine appropriate timezone instead of hardcoding it
        $dateTime->setTimezone(new \DateTimeZone(\Yii::$app->formatter->timeZone));
        return $dateTime->format($format);
    }
}
