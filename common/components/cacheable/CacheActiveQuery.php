<?php

namespace common\components\cacheable;

use Yii;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;
use yii\db\Query;

/**
 * CacheActiveQuery represents a ActiveQuery with cache.
 */
class CacheActiveQuery extends \common\components\ActiveQuery
{
    /** @var CacheInterface */
    public $cache = 'cache';

    /**
     * @var int $duration the number of seconds that query results can remain valid in the cache. If this is
     * not set, the value of [[queryCacheDuration]] will be used instead.
     */
    public $duration = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (is_string($this->cache)) {
            $this->cache = Yii::$app->get($this->cache);
        }
    }

    /**
     * @inheritdoc
     */
    public function one($db = null)
    {
        $cache = $this->cache;
        $key = $this->cacheGenerateKey($db);
        $row = $cache->get($key);
        if ($row === false) {
            $row = Query::one($db);
            if ($row) {
                $this->cacheOneRecord($key, $row);
            }
        }

        if ($row !== false) {
            $models = $this->populate([$row]);
            return reset($models) ?: null;
        }

        return null;
    }

    protected function cacheGenerateKey($db)
    {
        $modelClass = $this->modelClass;
        return md5(
            $modelClass
            . $this->createCommand($db)->rawSql
        );
    }

    protected function cacheOneRecord($key, $row)
    {
        $cache = $this->cache;
        $modelClass = $this->modelClass;
        $tags = $modelClass::cacheTags($row);
        if (empty($tags)) {
            return false;
        }

        return $cache->set(
            $key,
            $row,
            $this->duration,
            new TagDependency([
                'tags' => $tags,
            ])
        );
    }
}
