<?php

namespace common\components\behaviors;

use common\helpers\LandingPageHelper;

/**
 * Class SluggableBehavior
 * @package common\components\behaviors
 */
class SluggableBehavior extends \yii\behaviors\SluggableBehavior
{
    /**
     * @var string Contains value for generate slug
     */
    public $valueAttribute = 'name';
    /**
     * @var bool
     */
    public $ensureUnique = true;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->value = function () {
            if ($this->owner->{$this->slugAttribute}) {
                return $this->owner->{$this->slugAttribute};
            }

            return LandingPageHelper::slug($this->owner->{$this->valueAttribute});
        };
        parent::init();
    }
}
