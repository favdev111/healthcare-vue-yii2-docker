<?php

namespace modules\payment\actions;

use api2\components\ActionTrait;
use modules\account\models\Account;
use modules\account\models\Lesson;
use modules\payment\models\BankAccount;
use modules\payment\models\Transaction;
use yii\base\Action;
use yii\base\Model;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class CalcAction extends Action
{
    use ActionTrait;

    /** @var Lesson */
    public $lessonModelClass;

    /** @var Account */
    public $accountModelClass;

    public function run()
    {
        $params = Yii::$app->request->get();
        $lessonModel = $this->lessonModelClass;
        $accountModel = $this->accountModelClass;
        $lesson = new $lessonModel();
        $lesson->load($params, '');
        $lesson->tutorId = Yii::$app->user->identity->id;

        if (!$lesson->validate(['fromDate', 'toDate', 'jobId', 'studentId', 'tutorId'])) {
            Yii::$app->response->setStatusCode(422);
            return $this->modelErrors($lesson);
        }

        /**
         * @var $student Account
         */
        $result = $lesson->getAmount();

        if (
            $this->isApi
            && !isset($params['studentId'])
        ) {
            throw new UnprocessableEntityHttpException('studentId is required');
        }
        $student = $accountModel::findOne($params['studentId']);
        if (
            $this->isApi
            && !$student
        ) {
            throw new NotFoundHttpException('Student not found.');
        }

        $paymentAccount = Yii::$app
            ->user
            ->identity
            ->paymentAccount;

        /**
         * @var BankAccount $bankAccount
         */
        $bankAccount = $paymentAccount ? $paymentAccount->activeBankAccount : null;
        $bankAccountNumber = $bankAccount ? $bankAccount->getStripeBankAccount()->last4 : null;

        $processDate = Transaction::calcExpectedPayoutDate($student, new \DateTime());

        $validationErrors = $this->validation($lesson, $student, $bankAccountNumber);
        if ($validationErrors) {
            return $validationErrors;
        }

        if ($this->isApi) {
            return [
                'studentAccount' => $student,
                'processDate' => $processDate,
                'result' => $result,
                'bankAccountNumber' => $bankAccountNumber,
            ];
        }

        $payerName = $student->isPatient() ? 'Winit clinic' : $student->profile->showName;

        return [
            'result' => $result,
            'bankAccountNumber' => $bankAccountNumber,
            'processDate' => $processDate,
            'studentVerify' => $student->isVerified(),
            'payerName' => $payerName,
            'studentName' => $student->profile->showName,
            'isCompanyClient' => $student->isPatient(),
        ];
    }

    protected function modelErrors(Model $model, $errors = [], $key = 'errors')
    {
        if (!$model->hasErrors()) {
            foreach ($errors as $attribute => $error) {
                $model->addError($attribute, $error);
            }
        }

        if ($this->isApi) {
            return $model;
        }

        return [
            $key => $model->getFirstErrors(),
        ];
    }

    protected function validation($model, $student, $bankAccountNumber)
    {
        if (!$bankAccountNumber) {
            return $this->modelErrors(
                $model,
                [
                    'bankAccountNumber' => 'Please add your payment method to proceed further.',
                ],
                'warnings'
            );
        }

        if (!$student->isVerified()) {
            $text = 'Student';
            if ($student->isCompanyClient()) {
                $text = 'Company';
            }

            return $this->modelErrors(
                $model,
                [
                    'bankAccountNumber' => 'This ' . $text . ' does not have a payment method on file ' .
                        'and has been notified. For your own safety, please request that the student adds a ' .
                        'payment method on file before confirming any lessons',
                ],
                'warnings'
            );
        }

        return null;
    }
}
