<?php
namespace frontend\assets;

use yii\web\AssetBundle;
/**
 * Main frontend application asset bundle.
 */
class ToastrConfigurationAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/frontend/assets/src';

    public $js = [
        'js/toasterConfiguration.js',
    ];

    public $depends = [
        'common\assets\ToastrAsset',
    ];
}
