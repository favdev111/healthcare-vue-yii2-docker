<?php

namespace modules\account\models\query;

/**
 * This is the ActiveQuery class for [[\modules\account\models\PhoneValidation]].
 *
 * @see \modules\account\models\PhoneValidation
 */
class PhoneValidationQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return \modules\account\models\PhoneValidation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \modules\account\models\PhoneValidation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
