<?php

use modules\notification\models\entities\common\NotificationType;

return [
    [
        'id' => NotificationType::TYPE_CREDIT_CARD,
        'name' => 'Credit card',
    ],
    [
        'id' => NotificationType::TYPE_SPECIALIST_ACCOUNT_APPROVED,
        'name' => 'Specialist Account Approved',
    ],
    [
        'id' => NotificationType::TYPE_FORGOT_PASSWORD,
        'name' => 'Forgot Password',
    ],
    [
        'id' => NotificationType::TYPE_ACCOUNT_PATIENT_VERIFICATION,
        'name' => 'Account Patient Verification',
    ],
    [
        'id' => NotificationType::TYPE_ACCOUNT_SPECIALIST_VERIFICATION,
        'name' => 'Account Specialist Verification',
    ],
];
