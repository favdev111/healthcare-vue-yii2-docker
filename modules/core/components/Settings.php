<?php

namespace modules\core\components;

use modules\core\models\Setting as SettingAlias;
use Yii;
use yii\base\Component;
use yii\caching\Cache;
use yii\di\Instance;

/**
 * Class Settings
 */
class Settings extends Component
{
    /**
     * @var string setting model class name
     */
    public $modelClass = SettingAlias::class;
    /**
     * @var Cache|array|string the cache used to improve RBAC performance. This can be one of the followings:
     *
     * - an application component ID (e.g. `cache`)
     * - a configuration array
     * - a [[yii\caching\Cache]] object
     *
     * When this is not set, it means caching is not enabled
     */
    public $cache = 'cache';
    /**
     * @var string the key used to store settings data in cache
     */
    public $cacheKey = 'setting';
    /**
     * @var SettingAlias setting model
     */
    protected $model;
    /**
     * @var array list of settings
     */
    protected $items;

    /**
     * Initialize the component
     */
    public function init()
    {
        parent::init();

        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, Cache::class);
        }

        $this->model = Yii::createObject($this->modelClass);
    }

    /**
     * Get's all values in the specific section.
     *
     * @param string $section
     * @param null $default
     *
     * @return mixed
     */
    public function getAllBySection(string $section, $default = null)
    {
        $items = $this->getSettingsConfig();
        return $items[$section] ?? $default;
    }

    /**
     * Get's the value for the given section and key.
     *
     * @param string $section
     * @param string $key
     * @param null $default
     *
     * @return mixed
     */
    public function get(string $section, string $key, $default = null)
    {
        $items = $this->getSettingsConfig();
        return $items[$section][$key] ?? $default;
    }

    /**
     * Add a new setting or update an existing one.
     *
     * @param string $section
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public function set(string $section, string $key, $value): bool
    {
        if ($this->model->setSetting($section, $key, $value)) {
            if ($this->invalidateCache()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checking existence of setting
     *
     * @param string $section
     * @param string $key
     *
     * @return bool
     */
    public function has(string $section, string $key): bool
    {
        $setting = $this->get($section, $key);
        return !empty($setting);
    }

    /**
     * Remove setting by section and key
     *
     * @param string $section
     * @param string $key
     *
     * @return bool
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(string $section, string $key): bool
    {
        if ($this->model->removeSetting($section, $key)) {
            if ($this->invalidateCache()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the settings config
     *
     * @return array
     */
    protected function getSettingsConfig(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }

        if (!$this->cache instanceof Cache) {
            $this->items = $this->model->getSettings();
        } else {
            $cacheItems = $this->cache->get($this->cacheKey);
            if (!empty($cacheItems)) {
                $this->items = $cacheItems;
            } else {
                $this->items = $this->model->getSettings();
                $this->cache->set($this->cacheKey, $this->items);
            }
        }

        return $this->items;
    }

    /**
     * Invalidate the cache
     *
     * @return bool
     */
    public function invalidateCache(): bool
    {
        if ($this->cache !== null) {
            $this->cache->delete($this->cacheKey);
            $this->items = null;
        }

        return true;
    }
}
