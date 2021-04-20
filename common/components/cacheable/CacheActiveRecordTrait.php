<?php

namespace common\components\cacheable;

use Yii;
use yii\caching\TagDependency;

/**
 * CacheActiveRecordTrait is the ActiveRecord class with cache
 */
trait CacheActiveRecordTrait
{
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->attachBehavior(
            'CacheActiveRecordBehavior',
            CacheActiveRecordBehavior::class
        );
    }

    public static function cacheTagAttributes()
    {
        return ['id', 'accountId'];
    }

    /**
     * @inheritdoc
     * @return CacheActiveQuery the newly created [[CacheActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject([
            'class'      => CacheActiveQuery::className(),
            'duration'   => null,
        ], [get_called_class()]);
    }

    public static function cacheTags($data)
    {
        $attributes = static::cacheTagAttributes();
        $tags = [];
        foreach ($attributes as $attribute) {
            if (isset($data[$attribute])) {
                $tags[] = get_called_class() . ":{$attribute}:{$data[$attribute]}";
            }
        }

        return $tags;
    }

    /**
     * Invalidate model tags.
     * @param yii\db\AfterSaveEvent|null $event when called as an event handler.
     * @return bool
     */
    public function invalidateTags($event = null)
    {
        return TagDependency::invalidate(
            Yii::$app->cache,
            $this->cacheTags($this->attributes)
        );
    }
}
