<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class ExifAsset extends AssetBundle
{
    public $sourcePath = '@bower/exif-js';
    public $css = [
    ];
    public $js = [
        'exif.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
