<?php

namespace modules\account\models\query;

/**
 * This is the ActiveQuery class for [[\modules\account\models\AccountPhone]].
 *
 * @see \modules\account\models\AccountPhone
 */
class AccountPhoneQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return \modules\account\models\AccountPhone[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \modules\account\models\AccountPhone|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
