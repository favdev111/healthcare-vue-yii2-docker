<?php

namespace modules\payment\actions;

use api2\components\ActionTrait;
use common\helpers\EmailHelper;
use common\models\RefundData;
use modules\account\models\Lesson;
use yii\base\Action;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class RefundAction extends Action
{
    use ActionTrait;

    /** @var Lesson */
    public $lessonModelClass;

    public function init()
    {
        parent::init();

        if (
            $this->isApi
            && empty($this->lessonModelClass)
        ) {
            throw new InvalidArgumentException('`lessonModelClass` must be set for API');
        }
    }

    /**
     * @param integer $id if `isApi=true` then id is lesson ID, else id is transaction ID
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     */
    public function run($id)
    {
        $reason = Yii::$app->request->post('reason');
        if (empty($reason)) {
            $errorMessage = 'Reason of refund is required.';
            if ($this->isApi) {
                throw new UnprocessableEntityHttpException($errorMessage);
            }

            return [
                'error' => $errorMessage,
            ];
        }

        $transactionId = $id;
        if ($this->isApi) {
            $lessonModelClass = $this->lessonModelClass;
            $lessonModel = $lessonModelClass::find()
                ->andWhere([
                    'and',
                    ['id' => $id],
                    ['tutorId' => Yii::$app->user->id],
                ])->limit(1)->one();

            if (
                !$lessonModel
                || (
                    $lessonModel
                    && !$lessonModel->lastTransaction
                )
            ) {
                throw new NotFoundHttpException();
            }

            $transactionId = $lessonModel->lastTransaction->id;
        }

        $refundData = new RefundData();
        $refundData->transactionId = $transactionId;

        if (!$refundData->refund()) {
            $errors = $refundData->getFirstErrors();
            $errorMessage = array_shift($errors) ?? 'Refund could not be processed. Please contact us for more information.';
            EmailHelper::sendMessageToAdmin('Refund error', $errorMessage);

            if ($this->isApi) {
                throw new BadRequestHttpException($errorMessage);
            }

            return [
                'error' => $errorMessage,
            ];
        }

        $moduleTransaction = Yii::$app->getModule('payment');
        $moduleTransaction->eventRefundProcessed($refundData->transactionModel);
        $message = 'Student has successfully been refunded.';

        if ($this->isApi) {
            $lessonModel->refresh();
            Yii::$app->response->setStatusCode(200, $message);
            return $lessonModel;
        }

        return [
            'text' => $message,
        ];
    }
}
