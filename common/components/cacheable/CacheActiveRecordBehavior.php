<?php

namespace common\components\cacheable;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Helper for yii\db\ActiveRecord models
 * Features:
 * - automatically invalidate cache based on unified tag names
 *
 * This behavior needs CacheActiveRecordTrait to be added to model!
 */
class CacheActiveRecordBehavior extends Behavior
{
    /**
     * Get events list.
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => [$this->owner, 'invalidateTags'],
            ActiveRecord::EVENT_AFTER_INSERT => [$this->owner, 'invalidateTags'],
            ActiveRecord::EVENT_AFTER_UPDATE => [$this->owner, 'invalidateTags'],
        ];
    }
}
