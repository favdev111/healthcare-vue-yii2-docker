<?php

return [
    'components' => [
        'cacheBackend' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@common/runtime/cache',
        ],
    ],
];
