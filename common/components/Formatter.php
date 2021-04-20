<?php

namespace common\components;

/**
 * Class Formatter
 * @package common\components
 */
class Formatter extends \yii\i18n\Formatter
{
    /**
     * @var string
     */
    public $dateTimeWithSlashes = 'h:mma, MM/dd/y';
    /**
     * @var string
     */
    public $dateWithSlashes = 'MM/dd/y';
    /**
     * @var string
     */
    public $dateWithSlashesPhp = 'm/d/Y';
    /**
     * @var string
     */
    public $dateInputType = 'yyyy-MM-dd';
    /**
     * @var string
     */
    public $MYSQL_DATETIME = 'Y-m-d H:i:s';
    /**
     * @var string
     */
    public $MYSQL_DATE = 'Y-m-d';
    /**
     * @var string
     */
    public $MIDDAY_HOUR = '12:00:00';


    /**
     * @param $date
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @deprecated
     */
    public function getDatetimeWithSlashes($date)
    {
        return $this->asDatetimeWithSlashes($date);
    }


    /**
     * @param $date
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function asDatetimeWithSlashes($date)
    {
        return $this->asDatetime($date, $this->dateTimeWithSlashes);
    }


    /**
     * @param $date
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function asDateInputType($date)
    {
        return $this->asDate($date, $this->dateInputType);
    }

    /**
     * @param $date
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function as12HoursDatetime($date)
    {
        return $this->asDatetime($date, 'php:m/d/y  g:i A');
    }

    /**
     * @param $date
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function as12HoursTime($date)
    {
        return $this->asDatetime($date, 'php: g:i A');
    }


    /**
     * @param $date
     * @param bool $ifEmptyReturnNull
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function asDateWithSlashes($date, bool $ifEmptyReturnNull = false)
    {
        if ($ifEmptyReturnNull && $this->normalizeDatetimeValue($date) === null) {
            return null;
        }
        return $this->asDate($date, $this->dateWithSlashes);
    }

    /**
     * @param $duration
     * @return string
     */
    public function getTimestampAsHoursAndMinutes($duration)
    {
        $secondsInDay = (60 * 60 * 24);
        $secondsInHour = (60 * 60);
        $days = (int)($duration / $secondsInDay);
        $hours = (int)(($duration - ($days * $secondsInDay)) / $secondsInHour);
        $minutes = (int)(($duration - ($days * $secondsInDay) - ($hours * $secondsInHour)) / 60);
        if ($minutes < 10) {
            $minutes = "0$minutes";
        }
        $hours = (int)($duration / $secondsInHour);
        return "$hours:$minutes " . ($hours == 1 ? "hour" : "hours");
    }

    /**
     * @param string $value
     * @return string
     */
    public function asPhoneNumber(string $value): string
    {
        $value = trim($value);
        return '(' . substr($value, 0, 3) . ') ' . substr($value, 3, 3) . '-' . substr($value, 6);
    }

    /**
     * @param $n
     * @param $forms
     * @return string
     */
    public function pluralForm($n, array $forms): string
    {
        $n = (int)$n;
        switch ($n) {
            case 0:
                return $forms[2];
            case 1 === $n % 10 && 11 !== $n % 100:
                return $forms[0];
            case $n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20):
                return $forms[1];
            default:
                return $forms[2];
        }
    }

    /**
     * @param $price
     * @return string
     */
    public function priceFormat($price): string
    {
        return number_format($price, 2, '.', ' ');
    }
}
