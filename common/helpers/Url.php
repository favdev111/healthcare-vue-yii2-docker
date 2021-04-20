<?php

namespace common\helpers;

use common\models\UrlShortener;
use Yii;
use yii\base\InvalidConfigException;

class Url extends \yii\helpers\Url
{
    const CONFIRM_CHANGE_MAIL_ROUTE_B2B = '/change-email/confirm';
    const CANCEL_CHANGE_MAIL_ROUTE_B2B = '/change-email/cancel';
    const CONFIRM_CHANGE_MAIL_ROUTE_FRONT = '/account/confirm';
    const CANCEL_CHANGE_MAIL_ROUTE_FRONT = '/account/cancel';

    /**
     * @inheritdoc
     */
    protected static function normalizeRoute($route)
    {
        $route = parent::normalizeRoute($route);
        return rtrim($route, '/') . '/';
    }

    public static function getFrontendUrl($path = null, $params = [])
    {
        $queryParams = http_build_query($params);

        return static::getUrlWithPath(Yii::getAlias('@frontendUrl'), $path)
            . ($queryParams ? '?' . $queryParams : '');
    }

    protected static function getUrlWithPath($url, $path)
    {
        $path = trim($path);
        $url = trim($url);
        $url = trim($url, '/') . '/';
        return $url . ltrim($path ?? '', '/');
    }

    public static function getBackendUrl()
    {
        return Yii::getAlias('@backendUrl');
    }

    public static function getB2bUrl()
    {
        return Yii::getAlias('@b2bUrl');
    }

    public static function toRoute($route, $scheme = false, $accountModel = null)
    {
        if (
            $accountModel
            && $accountModel->isCompany()
        ) {
            switch ($route[0]) {
                case '/account/reset':
                    return static::to(static::getB2bUrl() . '/reset/' . $route['token']);

                    break;
            }
        }

        return parent::toRoute(
            $route,
            $scheme
        );
    }

    public static function toB2bRoute($uri, $params)
    {
        $queryParams = http_build_query($params);
        return static::getB2bUrl() . $uri . ($queryParams ? '?' . $queryParams : '');
    }
}
