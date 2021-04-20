<?php

namespace modules\chat\controllers\console;

use modules\account\models\Account;
use modules\account\models\query\AccountQuery;
use modules\chat\models\ChatMessage;
use modules\chat\Module;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\Console;

class DefaultController extends Controller
{
    public function actionAddUser($fromAccountId = null)
    {
        /**
         * @var $accountsQuery AccountQuery
         */
        $accountsQuery = Account::find();
        if ($fromAccountId) {
            $accountsQuery->andWhere(['>=', 'id', $fromAccountId]);
        }

        $this->stdout("Accounts list:\n", Console::BOLD);

        /**
         * @var $chat Module
         */
        $chat = \Yii::$app->getModule('chat');
        foreach ($accountsQuery->each() as $account) {
            try {
                $chatAccount = $chat->getUser($account);
                if (false === $chatAccount) {
                    $this->stdout("{$account->id}\n", Console::BOLD);
                    if (!$chat->addUser($account)) {
                        $this->stdout("miss\n", Console::BOLD);
                    }
                } else {
                    $chat->updateUser($chatAccount['user']['id'], $account);
                    $this->stdout("{$account->id} updated\n", Console::BOLD);
                }
            } catch (\Exception $exception) {
                $this->stdout("{$exception->getMessage()}\n", Console::BOLD);
                $this->stdout("{$exception->getTraceAsString()}\n", Console::BOLD);
            }
        }
    }

    public function actionMarkAsReadAllRecentMessages()
    {

        Console::output('Looking for dialogs with unread messages...');
        $dialogs = ChatMessage::find()->select([
            'chat_dialog_id',
            'recipient_id',
            'sender_id',
            new Expression('MAX(`' . ChatMessage::tableName() . '`.`id`) AS `lastMessageId`'),
            new Expression('SUM(IF(`recipientStatusRead` = 0, 1, 0)) as `countUnreadMessages`'),
        ])
            ->groupBy(['chat_dialog_id', 'recipient_id', 'sender_id'])
            ->having(['>', 'countUnreadMessages', 0])
            ->asArray()
            ->all();

        if (empty($dialogs)) {
            Console::output('Nothing is found.');
        }

        foreach ($dialogs as $dialog) {
            $dialogMembers = [$dialog['recipient_id'], $dialog['sender_id']];

            foreach ($dialogMembers as $dialogMember) {
                //looking for last sent message
                $lastSentMessage = ChatMessage::find()
                    ->select(['date_sent'])
                    ->andWhere(['chat_dialog_id' => $dialog['chat_dialog_id']])
                    ->andWhere(['sender_id' => $dialogMember])
                    ->orderBy('date_sent DESC')
                    ->asArray()
                    ->limit(1)
                    ->one();

                if (empty($lastSentMessage)) {
                    continue;
                }

                //mark all user messages that user received before as read
                $count = ChatMessage::markAllUserMessagesAsRead($dialog['chat_dialog_id'], $dialogMember, $lastSentMessage['date_sent']);
                if ($count) {
                    Console::output('Done for dialog ' . $dialog['chat_dialog_id'] . " for user $dialogMember");
                }
            }
        }

        Console::output('Looking for last read message for each user...');
        foreach ($dialogs as $dialog) {
            $dialogMembers = [$dialog['recipient_id'], $dialog['sender_id']];

            foreach ($dialogMembers as $dialogMember) {
                //looking for last read message
                $messageModel = ChatMessage::find()
                    ->andWhere(['chat_dialog_id' => $dialog['chat_dialog_id']])
                    ->andWhere(['recipientStatusRead' => 1])
                    ->andWhere(['recipient_id' => $dialogMember])
                    ->orderBy('date_sent DESC')
                    ->limit(1)
                    ->one();

                if (empty($messageModel)) {
                    continue;
                }

                //mark recent user messages as read
                $count = $messageModel->markAsReadRecentMessages();
                if ($count) {
                    Console::output('Done for dialog ' . $dialog['chat_dialog_id']);
                }
            }
        }
    }
}
