<?php

namespace modules\chat\models\query;

use common\components\ActiveQuery;
use Yii;

/**
 * Class ChatMessageQuery
 * @package modules\chat\models
 * @property string $tableName
 */
class ChatMessageQuery extends ActiveQuery
{

    /**
     * Add order by latest message
     * @return $this
     */
    public function addOrderByLatest()
    {
        return $this->orderBy([$this->tableName . '.createdAt' => SORT_DESC]);
    }

    /**
     * Add order by chat dialog message
     * @param $chatDialogId
     * @return $this
     */
    public function byChatDialogId($chatDialogId)
    {
        return $this->andWhere([$this->tableName . '.chat_dialog_id' => $chatDialogId]);
    }

    /**
     * by latest dialog message
     * @param $chatDialogId
     * @return $this
     */
    public function byLatestDialogMessage($chatDialogId)
    {
        return $this->byChatDialogId($chatDialogId)->addOrderByLatest();
    }
}
