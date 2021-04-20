<?php

use modules\notification\models\entities\common\setting\NotificationCategory;

return [
    [
        'id' => NotificationCategory::CATEGORY_EMAIL,
        'name' => 'Email',
    ],
    [
        'id' => NotificationCategory::CATEGORY_SMS,
        'name' => 'SMS',
    ],
];
