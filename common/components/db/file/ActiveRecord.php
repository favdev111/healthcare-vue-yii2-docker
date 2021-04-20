<?php

namespace common\components\db\file;

/**
 * Class ActiveRecord
 * @package common\components\db\file
 */
class ActiveRecord extends \yii2tech\filedb\ActiveRecord
{
    /**
     * @param string $value
     * @return \yii2tech\filedb\Connection
     */
    protected function setPath(string $value): void
    {
        $db = self::getDb();
        $db->path = $value;
    }
}
