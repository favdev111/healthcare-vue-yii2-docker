<?php

namespace common\assets;

use yii\web\AssetBundle;

class DashboardAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/common/assets/dist/js/account';

    public $js = [
        'dashboard.js',
    ];
}
