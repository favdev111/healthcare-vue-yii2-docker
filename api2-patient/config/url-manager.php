<?php

return [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'baseUrl' => '/api/v1/patient',
    'suffix' => null,
    'rules' => [
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'accounts' => 'account/default',
            ],
            'patterns' => [
                'POST signup' => 'signup',
                'PATCH me' => 'me-update',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'leads' => 'account/lead',
            ],
            'patterns' => [
                'POST signup' => 'signup',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'payment/card' => 'payment/card',
            ],
            'patterns' => [
                'POST' => 'create',
                'POST set-active' => 'set-active',
                'GET' => 'index',
                'DELETE <id:\d+>' => 'delete',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'health-profiles' => 'account/health-profile',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'health-profile-insurances' => 'account/health-profile-insurance',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'health-profile-health' => 'account/health-profile-health',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'professionals' => 'account/professionals',
            ],
            'patterns' => [
                'GET /' => 'index',
                'POST ratings' => 'ratings',
                'GET <id:\d+>' => 'professional',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'notes' => 'account/note',
            ],
            'patterns' => [
                'POST' => 'create',
                'GET <accountId:\d+>' => 'notes-list',
            ],
        ],
    ],
];
