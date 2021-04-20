<?php

namespace modules\chat\actions;

use api\components\rbac\Rbac;
use modules\account\helpers\EventHelper;
use modules\account\models\Job;
use modules\account\models\JobApply;
use modules\chat\models\Chat;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

class ChatSendAction extends Action
{
    public function run($chatUserId)
    {
        // For frontend
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        /**
         * @var $moduleAccount \modules\account\Module
         */
        $moduleAccount = Yii::$app->getModule('account');

        $accountSenderModel = Yii::$app->user->identity;
        $isCompanyClient = $accountSenderModel->isPatient();
        if ($accountSenderModel->can(Rbac::PERMISSION_BASE_B2B_PERMISSIONS)) {
            $fromChatUserId = $request->post('fromChatUserId');
            if (!$fromChatUserId) {
                throw new NotFoundHttpException();
            }
            $accountSenderChatModel = Chat::findOne(['chatUserId' => $fromChatUserId]);
            if (!$accountSenderChatModel) {
                throw new NotFoundHttpException();
            }
            $isCompanyClient = false;
            $accountSenderModel = $accountSenderChatModel->account;
            $accountSenderModel->populateRelation('chat', $accountSenderChatModel);
        }

        $chat = Chat::findOne(['chatUserId' => $chatUserId]);
        if (!$chat) {
            throw new NotFoundHttpException();
        }
        $accountRecipientModel = $chat->account;
        if (!$accountRecipientModel || !$accountRecipientModel->isActive()) {
            throw new NotFoundHttpException();
        }

        $isSendingAllowed = $this->isSendingAllowed($accountSenderModel);
        if ($isSendingAllowed !== true) {
            return $isSendingAllowed;
        }

        $message = $request->post('message');
        $type = $request->post('type');
        if (empty($message) || empty($type)) {
            throw new InvalidArgumentException('Message can\'t be blank.');
        }

        $jobId = $request->post('jobId');
        if ($jobId && is_numeric($jobId)) {
            /**
             * @var $job Job
             */
            $job = Job::findOneWithoutRestrictions($jobId);
            $ja = JobApply::findOne([
                'jobId' => $jobId,
                'accountId' => $accountRecipientModel->id,
            ]);

            if (!$ja->studentAnswer) {
                $ja->studentAnswer = true;
                $ja->save(false);
            }

            if ($accountSenderModel->isPatient()) {
                $moduleAccount->eventStudentRepliedToJob(
                    $job,
                    $accountRecipientModel,
                    $message,
                    $chatUserId
                );
            }
        }

        /**
         * @var $moduleChat \modules\chat\Module
         */
        $moduleChat = Yii::$app->getModule('chat');

        $response = $moduleChat->sendMessage(
            $message,
            $accountSenderModel->chat,
            $accountRecipientModel->chat,
            $type,
            true,
            $isCompanyClient
        );

        $messageChatObject = $response->response;
        $chatMessageModel = $response->model;

        if ($messageChatObject === false) {
            throw new InvalidArgumentException(
                'The message can\'t be sent right now. Please try again later.'
            );
        }

        // Filtering message before sending to email and sms (if needed)
        $message = $moduleChat->hideDataMessage(
            $accountSenderModel,
            $accountRecipientModel,
            $messageChatObject['message']
        );

        if ($accountSenderModel->isNotificationAllow()) {
            if ($accountSenderModel->isTutor()) {
                EventHelper::replyToJobEvent($accountSenderModel, $chatUserId, $accountRecipientModel);
            } else {
                $moduleAccount->eventNewMessageTutor(
                    $accountSenderModel,
                    $accountRecipientModel,
                    $message,
                    $messageChatObject,
                    $chatMessageModel
                );
            }
        }


        return $messageChatObject;
    }

    protected function isSendingAllowed($account)
    {
        /**
         * @var $moduleChat \modules\chat\Module
         * @var $account Account
         */
        $moduleChat = Yii::$app->getModule('chat');
        $chatAccount = $account->chat;
        if ($account->isPatient() && $chatAccount->isHold()) {
            // Prevent sending messages in case chat account is blocked
            Yii::$app->response->statusCode = 403;
            return [
                'success' => false,
                'message' => $moduleChat->getProhibitSendingText(),
            ];
        }

        return true;
    }

    protected function beforeRun()
    {
        if (!Yii::$app->request->isPost) {
            throw new NotSupportedException();
        }
        return parent::beforeRun();
    }
}
