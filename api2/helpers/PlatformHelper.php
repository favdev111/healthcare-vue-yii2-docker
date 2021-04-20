<?php

namespace api2\helpers;

class PlatformHelper
{
    const PLATFORM_IOS = 1;
    const PLATFORM_ANDROID = 2;
    const PLATFORM_WEB = 3;

    /**
     * Platforms list
     * @return array
     */
    public static function asArray(): array
    {
        return [
            'ios' => self::PLATFORM_IOS,
            'android' => self::PLATFORM_ANDROID,
            'web' => self::PLATFORM_WEB,
        ];
    }

    /**
     * Validate device token
     * @param string $token
     * @param int $platformId
     * @return bool
     */
    public static function checkDeviceToken(string $token = null, int $platformId = null): bool
    {
        if (
            empty($token)
            || empty($platformId)
            || !in_array(strlen($token), [152, 163])
        ) {
            return false;
        }

        return true;
    }
}
