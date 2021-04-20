<?php

namespace modules\account\models\query;

use modules\account\models\Team as Team;

/**
 * This is the ActiveQuery class for [[\modules\account\models\Team]].
 *
 * @see \modules\account\models\Team
 */
class TeamQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['active' => true]);
    }

    /**
     * @inheritdoc
     * @return Team[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Team|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
