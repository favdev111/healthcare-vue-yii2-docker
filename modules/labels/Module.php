<?php

namespace modules\labels;

use common\components\Module as BaseModule;

/**
 * label module definition class
 */
class Module extends BaseModule
{
    /**
     * @var string Alias for module
     */
    public $alias = "@labels";
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
}
