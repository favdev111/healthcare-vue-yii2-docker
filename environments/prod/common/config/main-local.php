<?php
return [
    'components' => [
        'elasticsearch' => [
            'defaultProtocol' => 'https',
            'auth' => [],
            'nodes' => [
                [
                    'http_address' => 'vpc-heytutor-sav33nk5et63apq44hnutr52kq.us-west-1.es.amazonaws.com',
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
