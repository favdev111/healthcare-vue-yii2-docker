<?php

namespace common\assets;

use yii\web\AssetBundle;

class ProfileAddressAutoComplete extends AssetBundle
{
    public $sourcePath = '@themes/basic/common/assets/dist/js/';

    public $js = [
        'profile-zip-autocomplete.js',
    ];
}
