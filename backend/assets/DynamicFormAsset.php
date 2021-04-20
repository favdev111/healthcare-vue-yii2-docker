<?php

namespace backend\assets;

/**
 * Class DynamicFormAsset
 * @package backend\assets
 *
 * Class for fix kartik\select2 widget at dynamic form
 */
class DynamicFormAsset extends \wbraganca\dynamicform\DynamicFormAsset
{
    /**
     * @var string
     */
    public $sourcePath = '@webroot/components/dynamicForm';

    public $js = [
        'yii2-dynamic-form.js'
    ];

    /**
     * @param string $type
     * @param array $files
     */
    protected function setupAssets($type, $files = [])
    {
    }

    public function init()
    {
    }
}
