<?php

namespace modules\payment\actions;

use api2\components\ActionTrait;
use common\helpers\QueueHelper;
use modules\account\models\Lesson;
use modules\payment\models\Transaction;
use yii\base\Action;
use yii\base\Model;
use Yii;
use yii\web\ServerErrorHttpException;

class CreateAction extends Action
{
    use ActionTrait;

    /** @var Lesson */
    public $lessonModelClass;

    /** @var Transaction */
    public $transactionModelClass;

    public function run()
    {
        $lessonModel = $this->lessonModelClass;
        $transactionModel = $this->transactionModelClass;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            /** @var Lesson $lesson */
            $lesson = new $lessonModel();
            $lesson->load(Yii::$app->request->post(), '');
            $lesson->tutorId = Yii::$app->user->identity->id;
            if (!$lesson->validate()) {
                return $this->modelErrors($lesson);
            }

            $amount = $lesson->getAmount();
            $lesson->hourlyRate = $amount['tutorRate'];
            $lesson->fee = $amount['fee'];
            $lesson->amount = $amount['amount'];
            $lesson->calculatedClientPrice = ($lesson->student->isPatient() ?? false) ? $lesson->calculateClientPrice() : '';
            if (!$lesson->save(false)) {
                $transaction->rollBack();
                return $this->modelErrors($lesson);
            }

            //define type of transaction
            $isNewPaymentProcess = true;

            /** @var Transaction $tr */
            $tr = new $transactionModel();
            $tr->studentId = $lesson->studentId;
            $tr->tutorId = $lesson->tutorId;
            $tr->objectId = $lesson->id;
            $tr->objectType = $tr->selectLessonTransactionObjectType();
            $tr->amount = $lesson->amount;
            $tr->fee = $lesson->fee;
            $tr->type = $isNewPaymentProcess ? $transactionModel::STRIPE_TRANSFER : $transactionModel::STRIPE_CHARGE;
            $tr->billingCycleStatus = $transactionModel::NEW_BILLING_CYCLE_STATUS;
            $tr->processDate = date('Y-m-d');

            if ($isNewPaymentProcess) {
                $tr->useMainPlatformPaymentProcess = true;
            }
            if (!$tr->save(false)) {
                $transaction->rollBack();
                return $this->modelErrors($tr);
            }

            if (!$tr->isNeedApprove) {
                QueueHelper::sendLessonEmail($lesson->id);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            if ($this->isApi) {
                throw new ServerErrorHttpException($e->getMessage());
            }

            return [
                'errors' => $e->getMessage(),
            ];
        }

        if ($tr->isWaitingForApprove) {
            $message = 'Your transaction is pending approval from a HeyTutor representative.';
        } else {
            $message = 'Your Payment will be processed shortly';
        }

        return $this->response($lesson, $message);
    }

    protected function modelErrors(Model $model)
    {
        if ($this->isApi) {
            return $model;
        }

        return [
            'errors' => $model->getFirstErrors(),
        ];
    }

    protected function response($model, $statusText)
    {
        if ($this->isApi) {
            Yii::$app->response->setStatusCode(201, $statusText);
            return $model;
        }

        Yii::$app->session->addFlash('success', $statusText);
        return [
            'lesson' => $model->id,
        ];
    }
}
