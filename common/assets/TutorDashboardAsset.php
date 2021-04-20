<?php

namespace common\assets;

use yii\web\AssetBundle;

class TutorDashboardAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/common/assets/dist/js/account';

    public $js = [
        'dashboard-tutor.js',
    ];
}
