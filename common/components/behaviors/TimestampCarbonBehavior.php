<?php

namespace common\components\behaviors;

use Carbon\Carbon;

class TimestampCarbonBehavior extends \yii\behaviors\TimestampBehavior
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
            return Carbon::now();
        }

        return parent::getValue($event);
    }
}
