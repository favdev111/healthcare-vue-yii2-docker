<?php

namespace modules\task\queueJobs;

use common\models\Lead;
use modules\task\components\RetryableJob;
use Yii;

class SendLead extends RetryableJob
{
    public $leadModelId;
    public $force;

    public function execute($queue)
    {
        $status = Lead::QUEUE_STATUS_ERROR;
        $leadModel = Lead::findOne($this->leadModelId);
        if (empty($leadModel)) {
            return true;
        }

        if (
            !$this->force
            && $leadModel->status !== Lead::QUEUE_STATUS_PENDING
        ) {
            return true;
        }

        try {
            $result = Yii::$app->salesforce->sendLead($leadModel);
            if ($result) {
                $status = Lead::QUEUE_STATUS_OK;
                $leadModel->externalId = $result;
            }
        } catch (\Exception $exception) {
            Yii::error(
                $exception->getMessage(),
                'lead'
            );
        }

        $leadModel->status = $status;
        $leadModel->save(false);

        return $status === Lead::QUEUE_STATUS_OK;
    }
}
