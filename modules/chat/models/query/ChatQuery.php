<?php

namespace modules\chat\models\query;

use common\components\ActiveQuery;
use Yii;

/**
 * Class ChatQuery
 * @package modules\chat\models
 * @property string $tableName
 */
class ChatQuery extends ActiveQuery
{
    /**
     * @param $chatUserId
     * @return $this
     */
    public function andWhereChatUserId($chatUserId)
    {
        return $this->andWhere([$this->tableName . '.chatUserId' => $chatUserId]);
    }
}
