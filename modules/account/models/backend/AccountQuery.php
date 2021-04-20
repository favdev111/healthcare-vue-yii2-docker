<?php

namespace modules\account\models\backend;

use common\helpers\Role;
use yii\db\ActiveQuery;
use modules\account\models\query\AccountQuery as CommonAccountQuery;

class AccountQuery extends CommonAccountQuery
{
    public function isPatient()
    {
        return $this->andOnCondition(['roleId' => Role::ROLE_PATIENT]);
    }

    public function isSpecialist()
    {
        return $this->andOnCondition(['roleId' => Role::ROLE_SPECIALIST]);
    }
}
