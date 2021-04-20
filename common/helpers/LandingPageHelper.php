<?php

namespace common\helpers;

use yii\helpers\Inflector;
use Yii;

/**
 * Class LandingPageHelper
 * @package common\helpers
 */
class LandingPageHelper
{
    const SESSION_LANDING_URL_PARAM = 'sessionLandingUrlParams';

    const TYPE_LANDING = 1;
    const TYPE_BECOME_A_TUTOR = 2;

    const SLUG_REPLACE = [
        '#' => 'sharp',
        '/' => 'or',
        '&' => 'and',
        '.' => 'dot',
        '+' => 'p',
    ];

    public static function slugReplace($string)
    {
        return str_replace(
            array_keys(static::SLUG_REPLACE),
            array_values(static::SLUG_REPLACE),
            $string
        );
    }

    public static function slug($string)
    {
        return Inflector::slug(static::slugReplace($string), '-', true);
    }

    const LANDING_FRONTEND_TYPE_SUBJECT = 1;
    const LANDING_FRONTEND_TYPE_CITY = 2;
    const LANDING_FRONTEND_TYPE_STATE = 3;

    public static function getTypesList()
    {
        return [
            static::LANDING_FRONTEND_TYPE_SUBJECT => 'Subjects',
            static::LANDING_FRONTEND_TYPE_STATE => 'States',
            static::LANDING_FRONTEND_TYPE_CITY => 'Cities',
        ];
    }
}
