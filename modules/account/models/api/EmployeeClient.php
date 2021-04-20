<?php

namespace modules\account\models\api;

use modules\account\models\api\query\EmployeeClientsQuery;

class EmployeeClient extends \modules\account\models\EmployeeClient
{
    public static function find()
    {
        return (new EmployeeClientsQuery(static::class));
    }

    public function extraFields()
    {
        return [
          'client',
          'employee',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Account::className(), ['id' => 'clientId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Account::className(), ['id' => 'employeeId']);
    }
}
