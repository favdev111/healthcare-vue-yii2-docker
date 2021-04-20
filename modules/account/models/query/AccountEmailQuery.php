<?php

namespace modules\account\models\query;

use modules\account\models\AccountEmail;

/**
 * This is the ActiveQuery class for [[\modules\account\models\AccountEmail]].
 *
 * @see \modules\account\models\AccountEmail
 */
class AccountEmailQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return \modules\account\models\AccountEmail[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \modules\account\models\AccountEmail|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byEmail(string $email)
    {
        return $this->andWhere([AccountEmail::tableName() . '.email' => $email]);
    }
}
