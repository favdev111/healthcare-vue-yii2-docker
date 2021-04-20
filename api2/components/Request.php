<?php

namespace api2\components;

use Yii;
use api2\helpers\PlatformHelper;
use yii\web\HttpException;

class Request extends \yii\web\Request
{
    protected $platform;
    protected $deviceToken;

    public function getPlatform()
    {
        if ($this->platform !== null) {
            return $this->platform;
        }

        $platformArray = PlatformHelper::asArray();
        $platform = Yii::$app->request->getHeaders()->get('x-platform');
        $this->platform = false;
        if (
            !empty($platform)
            && isset($platformArray[$platform])
        ) {
            $this->platform = $platformArray[$platform];
        }

        return $this->platform;
    }

    public function getDeviceToken()
    {
        if ($this->deviceToken !== null) {
            return $this->deviceToken;
        }

        $platformId = $this->getPlatform();
        $token = Yii::$app->request->getHeaders()->get('x-device-token');
        $this->deviceToken = $token;
        if (!PlatformHelper::checkDeviceToken($token, $platformId)) {
            $this->deviceToken = false;
        }

        return $this->deviceToken;
    }

    public function validateDeviceParams()
    {
        if ($this->getPlatform() === false) {
            throw new HttpException(418, 'Platform is invalid.');
        }

        if (
            in_array($this->getPlatform(), [PlatformHelper::PLATFORM_ANDROID, PlatformHelper::PLATFORM_IOS])
            && $this->getDeviceToken() === false
        ) {
            throw new HttpException(418, 'Device token is invalid.');
        }
    }
}
