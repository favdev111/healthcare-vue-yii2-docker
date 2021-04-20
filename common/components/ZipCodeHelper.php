<?php

namespace common\components;

use common\models\State;
use Yii;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class ZipCodeHelper
{
    const DEV_DEFAULT_IP_CONFIGURATION = [
        'ip' => '72.229.28.185',
        'country_code' => 'US',
        'region_name' => 'New York',
        'city' => 'New York',
        'zip_code' => '10036',
        'time_zone' => 'America/New_York',
        'latitude' => 40.7605,
        'longitude' => -73.993300000000005,
    ];
    public static function getTimeZoneByUserIP($userIP = null)
    {
        return self::getDataByUserIP($userIP, 'time_zone');
    }

    /**
     * Get zip code by user ip
     * @param null $userIP
     * @return bool|mixed
     */
    public static function getZipCodeByUserId($userIP = null)
    {
        return self::getDataByUserIP($userIP, 'zip_code');
    }

    public static function getCityByUserIP($userIP = null)
    {
        return self::getDataByUserIP($userIP, 'city');
    }

    public static function getStateNameByUserIP($userIP = null)
    {
        return self::getDataByUserIP($userIP, 'region_name');
    }

    public static function getStateShortNameByUserIP($userIP = null)
    {
        $states = array_keys(\modules\account\models\ar\State::STATES_ARRAY, self::getStateNameByUserIP($userIP));
        return $states ? $states[0] : null;
    }

    public static function getCountryCodeByUserIP($userIP = null)
    {
        return self::getDataByUserIP($userIP, 'country_code');
    }

    private static function getDataFromCookie()
    {
        return $_COOKIE['configIp'] ?? null;
    }

    protected static function getUserIp()
    {
        return \Yii::$app->request->userIP ?? null;
    }

    public static function getUserIpOrFromCookie()
    {
        return static::getDataFromCookie()['ip'] ?? static::getUserIp();
    }

    /**
     * Get data by user ip
     * @param $userIP
     * @param $dataKey
     * @param bool $useCache
     * @return bool|mixed
     */
    protected static function getDataByUserIP($userIP, $dataKey, $useCache = true)
    {
        if (Yii::$app->request->isConsoleRequest) {
            return false;
        }

        $crawlerDetect = new CrawlerDetect();
        if ($crawlerDetect->isCrawler()) {
            return false;
        }

        $dataFormCookie = self::getDataFromCookie();
        if (
            empty($dataFormCookie)
            && defined('YII_ENV')
            && (YII_ENV === 'dev')
        ) {
            $dataFormCookie = self::DEV_DEFAULT_IP_CONFIGURATION;
        }

        if (!empty($dataFormCookie[$dataKey])) {
            return $dataFormCookie[$dataKey];
        }

        if ($userIP === null) {
            $userIP = $dataFormCookie['ip'] ?? static::getUserIp();
        }

        if ($userIP) {
            $data = Yii::$app->geoIp->getData($userIP, $useCache);
        }

        if (!empty($data[$dataKey])) {
            return $data[$dataKey];
        }

        return false;
    }

    public static function getZipCodeByUserIpRenderDynamic()
    {
        return Yii::$app->view->renderDynamic(
            'return \common\components\ZipCodeHelper::getZipCodeByUserId(\Yii::$app->request->userIP);'
        );
    }
}
