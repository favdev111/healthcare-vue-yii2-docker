<?php

return [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'baseUrl' => '/api/v1',
    'suffix' => null,
    'rules' => [
        'GET constants' => 'account/default/constants',
        'GET configs' => 'account/default/config',
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'state' => 'state',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'static-page' => 'static-page',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'health-test' => 'health-test',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'health-test-category' => 'health-test-category',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'health-goal' => 'health-goal',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'symptom' => 'symptom',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'medical-condition' => 'medical-condition',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'autoimmune-disease' => 'autoimmune-disease',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'language' => 'language',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'education-college' => 'education-college',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'insurance-company' => 'insurance-company',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'registration-wizard' => 'account/registration-wizard',
            ],
            'patterns' => [
                'POST step1' => 'step1',
                'POST step2' => 'step2',
                'POST step3' => 'step3',
                'POST step4' => 'step4',
                'POST step5' => 'step5',
                'POST step6-upload-photo' => 'step6-upload-photo',
                'POST step6' => 'step6',
                'POST step7' => 'step7',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'accounts' => 'account/default',
            ],
            'patterns' => [
                'POST signin' => 'signin',
                'POST confirm' => 'confirm',
                'POST signup' => 'signup',
                'POST signout' => 'signout',
                'POST password-recovery' => 'password-recovery',
                'POST password-reset' => 'password-reset',
                'POST new-device-token' => 'new-device-token',
                'GET me' => 'me',
                'GET subjects' => 'subjects',
                'PATCH password' => 'password-update',
                'POST resend-confirmation' => 'resend-confirmation',
                'POST pusher-auth' => 'pusher-auth',
                'PUT ' => 'update',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'notifications' => 'notification/default',
            ],
            'patterns' => [
                'GET /' => 'index',
                'GET unread-count' => 'unread-count',
                'PUT read/<notificationId:\d+>' => 'read',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'allergy-category' => 'allergy-category',
            ],
            'patterns' => [
                'GET /' => 'index',
                'GET get-all' => 'get-all',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'allergy' => 'allergy',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'lifestyle-diet' => 'lifestyle-diet',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'social-info' => 'social-info',
            ],
            'patterns' => [
                'GET drink' => 'drink',
                'GET smoke' => 'smoke',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'notification/settings' => 'notification/setting',
            ],
            'patterns' => [
                'GET /' => 'index',
                'POST /' => 'create',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'health-pros' => 'account/health-pros',
            ],
            'patterns' => [
                'PATCH profile' => 'profile',
                'PATCH role' => 'role',
                'PATCH rate-and-policy' => 'rate-and-policy',
                'PATCH profile-setting' => 'profile-setting',
                'PATCH specification' => 'specification',
                'POST avatar' => 'avatar',
            ]
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'twillio' => 'account/twillio',
            ],
            'patterns' => [
                'POST roomcreate' => 'roomcreate',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'list-patient' => 'list-patient',
            ],
            'patterns' => [
                'GET /' => 'index',
            ],
        ],
    ],
];
