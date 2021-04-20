<?php

namespace modules\account\models\query;

use modules\account\models\AccountTeam;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[AccountTeam]].
 *
 * @see AccountTeam
 */
class AccountTeamQuery extends \yii\db\ActiveQuery
{

    public function active(): ActiveQuery
    {
        return $this->andWhere(['deletedAt' => false]);
    }
    /**
     * @inheritdoc
     * @return AccountTeam[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AccountTeam|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
