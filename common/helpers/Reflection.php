<?php

namespace common\helpers;

class Reflection
{
    /**
     * Get class constants by token.
     * If you set constants with same prefix, like:
     * MY_STATUS_1
     * MY_STATUS_2
     * MY_STATUS_3
     * you can get it by calling
     * Class::getConstants('MY');
     * or
     * Class::getConstants('MY_STATUS');
     *
     * @param string $class
     * @param string $token
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function getConstants(string $class, string $token): array
    {
        $tokenLen = strlen($token);

        $reflection = new \ReflectionClass($class);
        $allConstants = $reflection->getConstants();

        $tokenConstants = [];
        foreach ($allConstants as $name => $val) {
            if (substr($name, 0, $tokenLen) != $token) {
                continue;
            }
            $tokenConstants[$val] = ucwords(strtolower(\Yii::t('app', str_replace('-', ' ', $val))));
        }

        return $tokenConstants;
    }
}
