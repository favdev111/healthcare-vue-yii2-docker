<?php

return [
    'components' => [
        'cacheConsole' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@common/runtime/cache',
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '7cJka}rNv?sdtUUrsyJRJpA0ByMxhD~CAb17FR2HjqIfo3Me|f#7qgtw*7~I|CMa',
        ],
    ],
];
