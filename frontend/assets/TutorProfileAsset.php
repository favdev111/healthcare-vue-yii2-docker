<?php
namespace frontend\assets;

use yii\web\AssetBundle;

class TutorProfileAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/frontend/assets/dist';

    public $js = [
        'js/profile-tutor-info.js',
    ];
    public $depends = [
        'common\assets\TutorProfileAsset',
    ];
}
