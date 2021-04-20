<?php
return [
    'components' => [
        'elasticsearch' => [
            'nodes' => [
                [
                    'http_address' => 'elasticsearch:9200',
                ],
            ],
        ],
    ],
];
