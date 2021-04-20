<?php

namespace modules\account\models\backend;

/**
 * @inheritdoc
 */
class AccountStudentSearch extends AccountSearch
{
    /**
     * @inheritdoc
     */
    public function search($params)
    {
        $query = Account::findWithoutRestrictions()
            ->isPatient();

        return $this->dataProvider($query, $params);
    }
}
