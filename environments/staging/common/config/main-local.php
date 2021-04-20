<?php

return [
    'components' => [
        'elasticsearch' => [
            'nodes' => [
                [
                    'http_address' => env('ELASTICSEARCH_ADDRESS') ?? '127.0.0.1:9201',
                ],
            ],
        ],
        'db' => [
            'enableSchemaCache' => true,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@common/runtime/cache',
        ],
    ],
];
