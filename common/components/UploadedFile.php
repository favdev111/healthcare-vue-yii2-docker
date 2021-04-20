<?php

namespace common\components;

class UploadedFile extends \yii\web\UploadedFile
{
    protected static $hash;

    public function hash()
    {
        if (!is_null(self::$hash)) {
            return self::$hash;
        }

        return (self::$hash = md5($this->tempName));
    }
}
