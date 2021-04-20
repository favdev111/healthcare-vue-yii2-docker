<?php

namespace modules\account\models\backend;

use Yii;

/**
 * @inheritdoc
 */
class Subject extends \modules\account\models\Subject
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert || isset($changedAttributes['name']) || isset($changedAttributes['keywords'])) {
            $route = 'elastic/re-create-subject-index';
            $task = new \UrbanIndo\Yii2\Queue\Job(['route' => $route]);
            Yii::$app->queue->post($task);
        }
    }
}
