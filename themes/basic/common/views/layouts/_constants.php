<?php
use modules\account\models\Profile;
use modules\account\models\Role;


return [
    'roles' => [
        'ROLE_HEALTH_PRO' => Role::ROLE_SPECIALIST,
        'ROLE_STUDENT' => Role::ROLE_PATIENT,
    ],
    'profile' => [
        'MAX_TITLE_LENGTH' => Profile::MAX_TITLE_LENGTH
    ],
];
