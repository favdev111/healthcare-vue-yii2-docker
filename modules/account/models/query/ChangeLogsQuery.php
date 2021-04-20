<?php

namespace modules\account\models\query;

/**
 * This is the ActiveQuery class for [[\modules\account\models\ChangeLog]].
 *
 * @see \modules\account\models\ChangeLog
 */
class ChangeLogsQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return \modules\account\models\ChangeLog[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \modules\account\models\ChangeLog|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
