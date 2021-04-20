<?php

namespace common\components\behaviors;

use common\components\Formatter;
use yii\db\Expression;

class TimestampBehavior extends \yii\behaviors\TimestampBehavior
{
    /**
     * @inheritdoc
     */
    public $createdAtAttribute = 'createdAt';

    /**
     * @inheritdoc
     */
    public $updatedAtAttribute = 'updatedAt';

    /**
     * @inheritdoc
     *
     * In case, when the [[value]] is `null`,
     * the result of the PHP function [time()](http://php.net/manual/en/function.time.php)
     * will be used as value.
     */
    protected function getValue($event)
    {
        if ($this->value === null) {
            return new Expression('NOW()');
        }
        return parent::getValue($event);
    }

    public static function currentDate()
    {
        /**
         * @var Formatter $formatter
         */
        $formatter = \Yii::$app->formatter;
        return $formatter->asDate(new \DateTime(), 'php:' . $formatter->MYSQL_DATE);
    }
}
