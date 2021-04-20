<?php

namespace modules\chat\actions;

use modules\chat\models\ChatMessage;
use Yii;
use yii\base\Action;
use yii\base\NotSupportedException;
use yii\web\NotFoundHttpException;

class ChatMarkReadAction extends Action
{
    public function run($messageId, $dialogId)
    {
        Yii::$app->response->format = Yii::$app->response::FORMAT_JSON;

        $messageModel = ChatMessage::findOne(['_id' => $messageId, 'chat_dialog_id' => $dialogId]);
        if (!$messageModel) {
            throw new NotFoundHttpException();
        }

        if (!$messageModel->messageRecipient) {
            throw new NotFoundHttpException();
        }

        $accountModel = Yii::$app->user->identity;
        if ($accountModel->isCrmAdmin()) {
            throw new NotFoundHttpException();
        } else {
            if ($messageModel->messageRecipient->id !== $accountModel->id) {
                throw new NotFoundHttpException();
            }
        }

        $messageModel->recipientStatusRead = true;
        $messageModel->markAsReadRecentMessages();
        return $messageModel->save(false);
    }

    protected function beforeRun()
    {
        if (!Yii::$app->request->isPost) {
            throw new NotSupportedException();
        }
        return parent::beforeRun();
    }
}
