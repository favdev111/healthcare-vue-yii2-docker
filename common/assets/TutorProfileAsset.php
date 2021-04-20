<?php

namespace common\assets;

use yii\web\AssetBundle;

class TutorProfileAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/common/assets/dist/js/tutor-profile';

    public $js = [
        'profile-tutor-info.js',
    ];
}
