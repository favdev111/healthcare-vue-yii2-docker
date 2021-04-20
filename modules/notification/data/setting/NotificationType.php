<?php

use modules\notification\models\entities\common\setting\NotificationCategory;

return [
    [
        'id' => 1,
        'name' => 'Payroll issued',
        'categoryId' => NotificationCategory::CATEGORY_EMAIL,
    ],
    [
        'id' => 2,
        'name' => 'Patient sent consultation request',
        'categoryId' => NotificationCategory::CATEGORY_EMAIL,
    ],
    [
        'id' => 3,
        'name' => 'Patient revised consultation schedule',
        'categoryId' => NotificationCategory::CATEGORY_EMAIL,
    ],
    [
        'id' => 4,
        'name' => 'Patient requested consultation reschedule',
        'categoryId' => NotificationCategory::CATEGORY_EMAIL,
    ],
    [
        'id' => 5,
        'name' => 'Patient declined revised consultation time request',
        'categoryId' => NotificationCategory::CATEGORY_EMAIL,
    ],
    [
        'id' => 6,
        'name' => 'Patient cancelled consultation',
        'categoryId' => NotificationCategory::CATEGORY_EMAIL,
    ],
    [
        'id' => 7,
        'name' => 'Consultation reminder 1 hour before',
        'categoryId' => NotificationCategory::CATEGORY_EMAIL,
    ],
    [
        'id' => 8,
        'name' => 'Patient sent consultation request',
        'categoryId' => NotificationCategory::CATEGORY_SMS,
    ],
    [
        'id' => 9,
        'name' => 'Patient revised consultation schedule',
        'categoryId' => NotificationCategory::CATEGORY_SMS,
    ],
    [
        'id' => 10,
        'name' => 'Patient requested consultation reschedule',
        'categoryId' => NotificationCategory::CATEGORY_SMS,
    ],
    [
        'id' => 11,
        'name' => 'Patient declined revised consultation time request',
        'categoryId' => NotificationCategory::CATEGORY_SMS,
    ],
    [
        'id' => 12,
        'name' => 'Patient cancelled consultation',
        'categoryId' => NotificationCategory::CATEGORY_SMS,
    ],
    [
        'id' => 13,
        'name' => 'Consultation reminder 1 hour before',
        'categoryId' => NotificationCategory::CATEGORY_SMS,
    ],
];
