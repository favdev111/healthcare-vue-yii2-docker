<?php

namespace modules\yiiQueueHistory;

use common\components\JsonSerializer;
use modules\yiiQueueHistory\models\History;
use yii\base\Event;
use yii\helpers\Json;
use yii\queue\db\Queue;
use yii\queue\ExecEvent;
use yii\queue\PushEvent;

/**
 * Class Module
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Event::on(Queue::class, Queue::EVENT_AFTER_PUSH, [$this, 'doHistory']);
        Event::on(Queue::class, Queue::EVENT_AFTER_EXEC, [$this, 'updateJobStatus']);
        Event::on(Queue::class, Queue::EVENT_AFTER_ERROR, [$this, 'updateJobStatus']);
    }

    /**
     * Do history
     *
     * @param PushEvent $event Event
     */
    public function doHistory(PushEvent $event): void
    {
        $model = new History();
        $model->class = get_class($event->job);
        $model->job = (new JsonSerializer())->serialize($event->job);
        $model->jobId = $event->id;

        if (!$model->save()) {
            \Yii::error('Queue history save error: ' . Json::encode($model->errors));
        }
    }

    /**
     * Update job status
     *
     * @param PushEvent $event Event
     */
    public function updateJobStatus(ExecEvent $event): void
    {
        $model = History::findOne(['jobId' => $event->id]);
        if ($model) {
            if (is_null($event->error)) {
                $model->status = History::STATUS_SUCCESS;
                $model->error = null;
            } else {
                $model->status = History::STATUS_ERROR;
                $model->error = null;
                $model->error = $event->error->getMessage();
                \Yii::error($model->error . "\n" . $event->error->getTraceAsString(), 'yii-queue');
            }
            if (!$model->save(true, ['status', 'error'])) {
                \Yii::error('Queue history status save error: ' . Json::encode($model->errors));
            }
        }
    }
}
