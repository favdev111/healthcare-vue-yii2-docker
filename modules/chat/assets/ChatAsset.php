<?php

namespace modules\chat\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class ChatAsset extends AssetBundle
{
    public $sourcePath = '@modules/chat/assets/src/dist';
    public $css = [];
    public $js = [];
    public $depends = [];

    public $publishOptions = [
        'except' => [
            '*.html',
        ],
    ];
}
