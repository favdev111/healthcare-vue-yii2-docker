<?php

namespace modules\account\models\api\query;

use common\components\ActiveQuery;
use modules\account\models\api\AccountEmployee;
use modules\account\models\api\EmployeeClient;

class EmployeeClientsQuery extends ActiveQuery
{
    public function ownEmployees()
    {
        $ownEmployees = AccountEmployee::find()->select('id')->asArray()->column();
        return $this->andWhere([EmployeeClient::tableName() . '.employeeId' => $ownEmployees]);
    }
}
