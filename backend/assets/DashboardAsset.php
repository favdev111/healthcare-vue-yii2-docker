<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Dashboard backend application asset bundle.
 */
class DashboardAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/backend/assets/dist';
    public $css = [
    ];
    public $js = [
        'js/dashboard.js',
    ];
    public $depends = [
    ];
}
