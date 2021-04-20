<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class CroppieAsset extends AssetBundle
{
    public $sourcePath = '@npm/croppie';
    public $css = [
        'croppie.css',
    ];
    public $js = [
        'croppie.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
