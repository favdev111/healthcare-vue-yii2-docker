<?php

namespace modules\task;

use console\components\Queue;
use Yii;
use yii\log\FileTarget;

class Module extends \common\components\Module
{

    public function bootstrap($app)
    {
        Yii::$app->queue->on(Queue::EVENT_AFTER_ERROR, function ($event) {
            \Yii::$app->log->getLogger()->flush();
        });

        Yii::$app->queue->on(Queue::EVENT_AFTER_EXEC, function ($event) {
            \Yii::$app->log->getLogger()->flush();
        });
    }

    public function init()
    {
        parent::init();
    }
}
