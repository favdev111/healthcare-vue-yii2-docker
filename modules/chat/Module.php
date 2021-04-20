<?php

namespace modules\chat;

use ArrayObject;
use api\components\rbac\Rbac;
use common\components\app\ApiApplication;
use common\components\HtmlPurifier;
use common\components\Module as BaseModule;
use common\events\NotificationEvent;
use common\helpers\Role;
use modules\account\models\Account;
use modules\account\models\Job;
use modules\chat\events\NewMessageEvent;
use modules\chat\models\Chat;
use modules\chat\models\ChatMessage;
use modules\chat\models\ChatRestriction;
use phpDocumentor\Reflection\Types\Integer;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\db\Expression;
use yii\httpclient\Client;

class Module extends BaseModule implements BootstrapInterface
{
    const AVATAR = 'avatar';
    const FULL_NAME = 'fullName';
    const EMAIL = 'email';

    const EVENT_NEW_MESSAGE = 'new_message_event';

    const EVENT_CHAT_ACCOUNT_SUSPICIOUS = 'chat_account_suspicious';
    const EVENT_CHAT_ACCOUNT_HOLD = 'chat_account_hold';
    const EVENT_CHAT_ACCOUNT_UNBLOCKED = 'chat_account_unblocked';

    const NO_NOTIFICATIONS = 0;

    const ALLOWABLE_COUNT_FORBIDDEN_SYMBOLS_IN_MESSAGE = 10;
    const FORBIDDEN_SYMBOL_REPLACEMENT = '*';

    const DIALOG_TYPE_PRIVATE = 3;

    public $application_id;
    public $auth_key;
    public $secret_key;
    public $endpoint_api;
    public $endpoint_chat;
    public static $moduleName = 'chat';

    /**
     * @var Client
     */
    protected $client;
    protected $token;

    /**
     * @var integer number of tutors that consider suspicious
     */
    public $suspiciousTutorsCount;

    /**
     * @var integer number of seconds to check amount of tutors for
     */
    public $tutorsCountCheckPeriod;

    /**
     * @var integer number of seconds to check for previous message (prevent sending messages too often)
     */
    public $tutorsDoubleMessagesCountCheckPeriod;

    /**
     * @var integer number of tutors that consider suspicious during certain period
     */
    public $doubleMessagesSuspiciousTutorsCount;

    /**
     * @var array local list of mail domains (loaded from DB)
     */
    protected $mailDomainsList;

    public function init()
    {
        $this->client = new Client([
            'baseUrl' => $this->getApiUrl(),
            'formatters' => [
                Client::FORMAT_JSON => [
                    'class' => 'yii\httpclient\JsonFormatter',
                    'encodeOptions' => JSON_FORCE_OBJECT,
                ],
            ],
        ]);
        parent::init();

        if (!$this->suspiciousTutorsCount) {
            // 10 by default
            $this->suspiciousTutorsCount = 10;
        }

        if (!$this->tutorsCountCheckPeriod) {
            // 1 day by default
            $this->tutorsCountCheckPeriod = 86400;
        }

        if (!$this->tutorsDoubleMessagesCountCheckPeriod) {
            // 1 minute by default
            $this->tutorsDoubleMessagesCountCheckPeriod = 60;
        }

        if (!$this->doubleMessagesSuspiciousTutorsCount) {
            // 1 by default
            $this->doubleMessagesSuspiciousTutorsCount = 1;
        }
    }

    protected function getApiUrl()
    {
        return 'https://' . $this->endpoint_api;
    }

    protected function getToken($login = null, $password = null, $generateNewToken = false)
    {
        if (!$generateNewToken && !empty($this->token)) {
            return $this->token;
        }

        if (is_null($login)) {
            return $this->getPrivateToken();
        }

        $cacheComponent = Yii::$app->cache;
        $cacheKey = 'chatToken::' . $login . '::' . $password;

        $token = $cacheComponent->get($cacheKey);
        if (($token !== false) && is_string($token)) {
            return ($this->token = $token);
        }

        $token = $this->getPrivateToken($login, $password);
        if (!$token) {
            return false;
        }

        $cacheComponent->set($cacheKey, $token, 1.5 * 60 * 60);

        return ($this->token = $token);
    }

    protected function getPrivateToken($login = null, $password = null)
    {
        $credentials = [
            'application_id' => $this->application_id,
            'auth_key' => $this->auth_key,
            'nonce' => rand(),
            'timestamp' => time(),
        ];

        if (!is_null($login)) {
            $credentials['user'] = [
                'login' => $login,
                'password' => $password,
            ];
        }

        $signature = [];
        foreach ($credentials as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $signature[] = $key . '[' . $k . ']' . '=' . $v;
                }
            } else {
                $signature[] = $key . '=' . $value;
            }
        }

        $credentials['signature'] = hash_hmac('sha1', implode('&', $signature), $this->secret_key);

        $ansCredentials = $this->client->createRequest()
            ->setMethod('post')
            ->setUrl('session.json')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders(['content-type' => 'application/json'])
            ->setData($credentials)
            ->send();

        if (!$ansCredentials->isOk) {
            $errorMessage = 'getToken method:' . $ansCredentials->content . '.';
            if (!is_null($login)) {
                $errorMessage .= ' With login: ' . $login;
            }
            Yii::error($errorMessage, 'chat');
            return false;
        }

        return $ansCredentials->data['session']['token'];
    }

    public function setUserData(Account $model = null)
    {
        if (is_null($model)) {
            if (Yii::$app->user->isGuest) {
                throw new InvalidArgumentException();
            }

            $model = Yii::$app->user->identity;
        }

        if ($model->can(Rbac::PERMISSION_BASE_B2B_PERMISSIONS)) {
            return false;
        }

        if (!($chat = $model->chat)) {
            throw new InvalidArgumentException('Chat params not exist for this user');
        }

        if (!($token = $this->getToken($chat->login, $chat->password))) {
            return false;
        }

        $data = new \stdClass();
        $data->user = new \stdClass();
        $data->user->email = 'chatuser+' . $model->id . '@winitclinic.com';
        $data->user->full_name = $model->profile->getShowName(null, true, false);
        $customData = [
            'avatar_url' => $model->getAvatarUrl(null, false),
            'is_verified' => $model->isVerified(),
        ];

        if ($model->isPatient()) {
            $customData += [
                'client_avatar_url' => $model->getAvatarUrl(null, false, true),
                'client_full_name' => $model->profile->getShowName(null, false, false),
            ];
        }

        $data->user->custom_data = json_encode($customData);

        $request = $this->client->createRequest()
            ->setMethod('put')
            ->setUrl('users/' . $chat->chatUserId . '.json')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'content-type' => 'application/json',
                'QB-Token' => $token,
            ])
            ->setData($data);

        $response = $request->send();

        if ($response->isOk) {
            return true;
        }

        Yii::error('setUserData method: ' . $response->content, 'chat');
        return false;
    }

    public function addUserQueue(Account $model)
    {
        $route = 'chat/add-user-to-chat';
        $data = ['id' => $model->id];
        $task = new \UrbanIndo\Yii2\Queue\Job(['route' => $route, 'data' => $data]);
        Yii::$app->queue->post($task);
    }

    public function addUser(Account $model)
    {
        if (!($token = $this->getToken())) {
            return false;
        }
        $login = $model->id . Yii::$app->getSecurity()->generateRandomString(8);
        $password = Yii::$app->getSecurity()->generateRandomString(16);
        $data = new \stdClass();
        $data->user = new \stdClass();
        $data->user->login = $login;
        $data->user->password = $password;
        $data->user->email = 'chatuser+' . $model->id . '@winitclinic.com';
        $data->user->external_user_id = $model->id;
        if (isset($model->profile)) {
            $data->user->full_name = $model->profile->getShowName(null, true, false);
        } else {
            $data->user->full_name = 'Firstname L.';
        }

        $customData = [
            'avatar_url' => $model->getAvatarUrl(null, false),
            'is_verified' => $model->isVerified(),
        ];

        if ($model->isPatient()) {
            $customData += [
                'client_avatar_url' => $model->getAvatarUrl(null, false, true),
                'client_full_name' => $model->profile->getShowName(null, false, false),
            ];
        }

        $data->user->custom_data = json_encode($customData);

        $request = $this->client->createRequest()
            ->setMethod('post')
            ->setUrl('users.json')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'content-type' => 'application/json',
                'QB-Token' => $token,
            ])
            ->setData($data);

        $response = $request->send();

        if (!$response->isOk) {
            $error = json_decode($response->content);
            Yii::error('addUser method:' . $response->content, 'chat');
            if (isset($error->errors->email[0])) {
                $model->addError('newPassword', 'email ' . $error->errors->email[0]);
            }
            return false;
        }

        $userData = $response->data;
        $chat = Chat::findOne([
            'accountId' => $model->id,
        ]);
        if (!$chat) {
            $chat = new Chat();
        }

        $chat->accountId = $model->id;
        $chat->chatUserId = $userData['user']['id'];
        $chat->login = $login;
        $chat->password = $password;
        $chat->save(false);

        return true;
    }

    public function updateUser($id, Account $model)
    {
        if (!$model->profile) {
            return false;
        }

        $chat = $model->chat;

        if (!($token = $this->getToken($chat->login, $chat->password, true))) {
            return false;
        }

        $data = new \stdClass();
        $data->user = new \stdClass();

        $data = new \stdClass();
        $data->user = new \stdClass();
        $data->user->email = 'chatuser+' . $model->id . '@winitclinic.com';
        $data->user->full_name = $model->profile->getShowName(null, true, false);
        $customData = [
            'avatar_url' => $model->getAvatarUrl(null, false),
            'is_verified' => $model->isVerified(),
        ];

        if ($model->isPatient()) {
            $customData += [
                'client_avatar_url' => $model->getAvatarUrl(null, false, true),
                'client_full_name' => $model->profile->getShowName(null, false, false),
            ];
        }

        $data->user->custom_data = json_encode($customData);

        $request = $this->client->createRequest()
            ->setMethod('put')
            ->setUrl('users/' . $id . '.json')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'QB-Token' => $token,
            ])
            ->setData($data);

        $response = $request->send();

        if (!$response->isOk) {
            Yii::error('updateUser method: ' . $response->content, 'chat');
            return false;
        }

        return true;
    }

    public function getUser(Account $model)
    {
        if (!($token = $this->getToken())) {
            return false;
        }

        $request = $this->client->createRequest()
            ->setMethod('get')
            ->setUrl('users/external/' . $model->id . '.json')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'QB-Token' => $token,
            ]);

        $response = $request->send();

        if (!$response->isOk) {
            Yii::info('getUser method: ' . $response->content . ' Account ID ' . $model->id, 'chat');
            return false;
        }

        return $response->data;
    }

    public function getForbiddenSymbolsList()
    {
        return [
            1, 2, 3, 4, 5, 6, 7, 8, 9, 0,
            'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'zero', 'ten', 'hundred',
        ];
    }

    public function checkCountForbiddenNumbers($string)
    {
        $count = 0;
        foreach ($this->getForbiddenSymbolsList() as $forbiddenSymbol) {
            $count += substr_count($string, $forbiddenSymbol);
            if ($count >= static::ALLOWABLE_COUNT_FORBIDDEN_SYMBOLS_IN_MESSAGE) {
                return false;
            }
        }
        return true;
    }

    public function replaceForbiddenNumbers($string)
    {
        foreach ($this->getForbiddenSymbolsList() as $forbiddenSymbol) {
            $string = str_replace($forbiddenSymbol, static::FORBIDDEN_SYMBOL_REPLACEMENT, $string);
        }
        return $string;
    }

    /**
     * @param $from Account
     * @param $to Account Recipient account (or null if sent from student so there is no need to check recipient)
     * @param $message string
     * @return string
     *
     */
    public function hideDataMessage($from, $to, $message)
    {
        $hideUserData = false;
        if (
            // If message is from student without card
            ($from->isPatient() && !$from->isVerified())
            // or to student without card
            || ($to->isPatient() && !$to->isVerified())
        ) {
            $hideUserData = true;
        }
        if ($hideUserData) {
            // TODO: Check processUnverifiedMessage method in Chat model
            $email_regex = '/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/i';
            $message = preg_replace($email_regex, '*****', $message);

            /*hide forbidden symbols if their count equal or more than static::ALLOWABLE_COUNT_FORBIDDEN_SYMBOLS_IN_MESSAGE*/
            if (!$this->checkCountForbiddenNumbers($message)) {
                $message = $this->replaceForbiddenNumbers($message);
            }

            $mailDomains = $this->getMailDomains();
            $message = str_replace($mailDomains, '*****', $message);
        }
        return $message;
    }

    public function clearMessage($message, $removeHtmlTags = true)
    {
        if (empty($message)) {
            return '';
        }

        $message = html_entity_decode($message);
        $message = str_replace("\r\n", "\n", $message);
        $message = str_ireplace(['<br />', '<br>', '<br/>'], "\n", $message);
        if ($removeHtmlTags) {
            $message = HtmlPurifier::process($message, ['HTML.Allowed' => '']);
        }

        return trim($message);
    }

    public function sendMessageQueue($message, $from, $to, $type = 'chat', $removeHtmlTags = true, $isCompanyClient = false)
    {
        $from = $from->id;
        $to = $to->id;
        $route = 'chat/send-message';
        $data = compact('message', 'from', 'to', 'type', 'removeHtmlTags', 'isCompanyClient');
        $task = new \UrbanIndo\Yii2\Queue\Job(['route' => $route, 'data' => $data]);
        Yii::$app->queue->post($task);
    }

    /**
     * @param string $message
     * @param Chat $from
     * @param Chat $to
     * @param string $type
     * @param bool $removeHtmlTags
     * @param bool $isCompanyClient
     * @return bool|ArrayObject
     */
    public function sendMessage(
        string $message,
        Chat $from,
        Chat $to,
        string $type = 'chat',
        bool $removeHtmlTags = true,
        bool $isCompanyClient = false
    ) {
        if (!($token = $this->getToken($from->login, $from->password))) {
            Yii::error('Authorization failed. Login: ' . $from->login, 'chat');
            return false;
        }

        if ($to->account->isTutor() && $to->account->isUnderReview()) {
            Yii::$app->session->setFlash('error', 'You can not message this tutor. This tutor is under review. ');
            return false;
        }

        $data = new \stdClass();
        $data->send_to_chat = 1;
        $data->markable = 1;
        $data->recipient_id = $to->chatUserId;
        $data->isCompanyClient = $isCompanyClient;

        if ($type === 'chat') {
            $data->message = $this->clearMessage($message, $removeHtmlTags);
            if (empty($data->message)) {
                Yii::error('sendMessage method: empty message', 'chat');
                return false;
            }
        } else {
            if (empty($message)) {
                Yii::error('sendMessage method: empty message', 'chat');
                return false;
            }
            $data->attachments = new \stdClass();
            $data->attachments->{'0'} = [
                'type' => $type,
                'id' => $message,
            ];
        }

        $request = $this->client->createRequest()
            ->setMethod('post')
            ->setUrl('chat/Message.json')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'QB-Token' => $token,
            ])
            ->setData($data);

        $response = $request->send();

        if (!$response->isOk) {
            Yii::error('sendMessage method: ' . $response->content, 'chat');
            return false;
        }

        $messageModel = $this->saveToDatabase($response->data);
        if (false === $messageModel) {
            return false;
        }

        $this->eventNewMessage($response->data, $from->account, $messageModel);

        return new ArrayObject(
            [
                'response' => $response->data,
                'model' => $messageModel,
            ],
            ArrayObject::ARRAY_AS_PROPS
        );
    }

    protected function saveToDatabase(array $message)
    {
        $id = $message['id'] ?? $message['_id'] ?? null;
        if (!$id) {
            Yii::error('Invalid message object provided');
            return false;
        }

        $messageModel = ChatMessage::find()->andWhere(['_id' => $id])->limit(1)->one();
        if ($messageModel) {
            // Message is already saved and processed. No need to proceed
            return $messageModel;
        }

        $chatDialogId = $message['chat_dialog_id'] ?? $message['dialog_id'];

        $isFirstDialogMessage = !ChatMessage::find()->andWhere(['chat_dialog_id' => $chatDialogId])->exists();

        $messageModel = new ChatMessage([
            '_id' => $id,
            'message' => $message['message'],
            'chat_dialog_id' => $chatDialogId,
            'date_sent' => $message['date_sent'],
            'sender_id' => $message['sender_id'],
            'recipient_id' => $message['recipient_id'],
            'isCompanyClient' => $message['isCompanyClient'],
            'isFirstMessage' => $isFirstDialogMessage,
            'recipientStatusRead' => false,
            'chatAttachmentUid' => $message['attachments'][0]['id'] ?? null,
        ]);

        $messageModel->setMessageType($message['attachments'][0]['type'] ?? 'chat');
        if (!$messageModel->save()) {
            Yii::error('failed to save chat message. Errors:' . json_encode($messageModel->getErrors()), 'chat');
        }

        return $messageModel;
    }

    public function getCountUnreadMessages($dialogIds)
    {
        $dialogIds = array_filter($dialogIds);
        $dialogIds = $this->filterBlockedUsers($dialogIds);
        if (
            empty($dialogIds)
            || !is_array($dialogIds)
        ) {
            return [
                'total' => 0,
            ];
        }
        $result = $this->getResultByParams('chat/Message/unread.json', null, [
            'chat_dialog_ids' => implode(',', $dialogIds),
        ]);

        if (!is_array($result)) {
            return ['total' => self::NO_NOTIFICATIONS];
        }

        unset($result['total']);
        $result['total'] = array_sum($result);

        return $result;
    }

    public function getDialogsWithUnread($account = null, int $limit = 3)
    {
        if (is_null($account)) {
            $account = Yii::$app->user->identity;
        }

        $dialogs = [
            'items' => [],
            'unreadMessages' => self::NO_NOTIFICATIONS,
        ];
        $dialogModels = ChatMessage::find()
            ->select([
                'chat_dialog_id',
                new Expression('MAX(`' . ChatMessage::tableName() . '`.`id`) AS `id`'),
                new Expression('SUM(IF(`recipient_id` = :recipientId AND `recipientStatusRead` = 0, 1, 0)) as `recipientStatusRead`'),
            ])
            ->addParams([
                'recipientId' => $account->chat->chatUserId,
            ])
            ->joinWith([
                'messageSender' => function ($query) {
                    $query->active();
                },
            ], false)
            ->andWhere(['recipient_id' => $account->chat->chatUserId])
            ->groupBy('chat_dialog_id')
            ->having(['>', 'recipientStatusRead', 0])
            ->orderBy(['id' => SORT_DESC])
            ->limit($limit)
            ->indexBy('chat_dialog_id')
            ->asArray()
            ->all();

        $dialogIds = array_column($dialogModels, 'chat_dialog_id');
        $messageIds = array_column($dialogModels, 'id');
        $messages = ChatMessage::find()->andWhere(['id' => $messageIds])->indexBy('chat_dialog_id')->all();

        foreach ($dialogIds as $dialogId) {
            $dialog = $dialogModels[$dialogId];
            $message = $messages[$dialogId];
            $dialogs['items'][] = [
                '_id' => $dialogId,
                'occupants_ids' => [$message->recipient_id, $message->sender_id],
                'last_message' => $message->message,
                'last_message_date_sent' => strtotime($message->createdAt),
                'last_message_user_id' => $message->sender_id,
                'unread_messages_count' => $dialog['recipientStatusRead'],
            ];
        }

        $dialogs['unreadMessages'] = count($dialogModels);

        return $dialogs;
    }

    /**
     * Remove blocked users dialogs
     * @param $dialogIds
     * @return array
     */
    private function filterBlockedUsers($dialogIds)
    {
        $filteredDialogs = [];
        foreach ($dialogIds as $dialogId) {
            $chatMessage = ChatMessage::find()->byLatestDialogMessage($dialogId)->one();

            if ($chatMessage && $chatMessage->messageSender->isActive()) {
                $filteredDialogs[] = $dialogId;
            }
        }
        return $filteredDialogs;
    }

    public function getDialogs($user = null, $offset = null, $perPage = null)
    {
        $params = null;
        if ($offset !== null) {
            $params['skip'] = $offset;
        }
        if ($perPage !== null) {
            $params['limit'] = $perPage;
        }

        return $this->getDialogsByParams($user, $params);
    }

    public function getDialog($dialogId, $user = null)
    {
        return $this->getDialogsByParams($user, ['_id' => $dialogId]);
    }

    public function getDialogsCount($user = null)
    {
        return $this->getDialogsByParams($user, ['count' => 1]);
    }

    protected function getDialogsByParams($user = null, $params = null)
    {
        return $this->getResultByParams('chat/Dialog.json', $user, $params);
    }

    public function createDialogWith(int $chatUserId, Account $withAccount = null)
    {
        if (null === $withAccount) {
            $withAccount = Yii::$app->user->identity;
        }
        $chat = $withAccount->chat;
        if (!($token = $this->getToken($chat->login, $chat->password))) {
            return false;
        }

        $data = new \stdClass();
        $data->type = static::DIALOG_TYPE_PRIVATE;
        $data->occupants_ids = "$chatUserId";

        $request = $this->client->createRequest()
            ->setMethod('post')
            ->setUrl('chat/Dialog.json')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'QB-Token' => $token,
            ])
            ->setData($data);

        $response = $request->send();

        if (!$response->isOk) {
            $error = [
                'method: ' . self::class . '::createDialog',
                'accountId: ' . $withAccount->id,
                'accountChatId: ' . $chat->chatUserId,
                'response: ' . $response->content,
            ];
            Yii::error(
                implode("\n", $error),
                'chat'
            );
            return false;
        }
        return $response->data;
    }

    public function getMessages($dialogId, $user = null, $offset = null, $perPage = null)
    {
        $params = [
            'chat_dialog_id' => $dialogId,
            'sort_desc' => 'date_sent',
        ];
        if ($offset !== null) {
            $params['skip'] = $offset;
        }
        if ($perPage !== null) {
            $params['limit'] = $perPage;
        }

        return $this->getMessagesByParams($user, $params);
    }

    public function getMessage($dialogId, $messageId, $user = null)
    {
        $params = [
            'chat_dialog_id' => $dialogId,
            '_id' => $messageId,
        ];

        return $this->getMessagesByParams($user, $params);
    }

    public function getMessagesCount($dialogId, $user = null)
    {
        return $this->getMessagesByParams(
            $user,
            [
                'chat_dialog_id' => $dialogId,
                'count' => 1,
            ]
        );
    }

    protected function getMessagesByParams($user = null, $params = null)
    {
        return $this->getResultByParams('chat/Message.json', $user, $params);
    }

    protected function getResultByParams($url, $user = null, $params = null)
    {
        if (!$user) {
            $user = Yii::$app->user->identity;
        }
        $chat = $user->chat;

        if (!($token = $this->getToken($chat->login, $chat->password))) {
            return false;
        }

        $request = $this->client->createRequest()
            ->setMethod('get')
            ->setUrl($url)
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'QB-Token' => $token,
            ]);
        if ($params) {
            $request->setData($params);
        }

        $response = $request->send();


        if (!$response->isOk) {
            $error = [
                'method: ' . self::className() . '::getResultByParams',
                'url: ' . $url,
                'accountId: ' . $user->id,
                'accountChatId: ' . $chat->chatUserId,
                'response: ' . $response->content,
                'params: ' . var_export($params, true),
            ];
            Yii::error(
                implode("\n", $error),
                'chat'
            );
            return false;
        }
        return $response->data;
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        Event::on(
            \yii\base\Module::className(),
            \modules\account\Module::EVENT_TUTOR_HIRED,
            function (NotificationEvent $event) {
                /**
                 * @var Account $tutor ;
                 */
                $tutor = $event->tutor;

                /**
                 * @var Job $job
                 */
                $job = $event->job;

                /**
                 * @var Account $account
                 */
                $account = $job->account;

                $message =  'Congratulations! ' . ' decided you are the best fit to work with one of their clients named ' . $account->getDisplayName() . '. ' .
                    'Feel free to contact ' . ' directly for further details. Please remember to log in your sessions directly through the HeyTutor platform.';
                $to = $tutor->chat;
                $from = $account->chat;

                if (!empty($event->shareContactInfo) && !empty($job)) {
                    $message .= $account->getContactInfoHtml($job);
                    $removeHtmlTags = false;
                }

                /**
                 * @var $chat \modules\chat\Module
                 */
                $chat = Yii::$app->getModule('chat');

                $response = $chat->sendMessage($message, $from, $to, 'chat', $removeHtmlTags ?? true);
                if (!$response) {
                    return;
                }

                /**
                 * @var $moduleAccount \modules\account\Module
                 */
                $moduleAccount = Yii::$app->getModule('account');
                $moduleAccount->eventNewMessageTutor(
                    $account,
                    $tutor,
                    $message,
                    $response->response,
                    $response->model
                );
            }
        );

        Event::on(self::class, self::EVENT_NEW_MESSAGE, function ($event) {
            $message = $event->message;
            $account = $event->account;
            $messageModel = $event->messageModel;

            if ($account->roleId != Role::ROLE_PATIENT) {
                // No checks for roles other that Student required
                return;
            }

            /**
             * @var $chatAccount Chat
             */
            $chatAccount = $account->chat;
            if (!$chatAccount) {
                Yii::error('Failed to update chat account status. No chat account found. Message details: ' . json_encode($message), 'chat');
                return;
            }

            //$this->processChatRestrictions($chatAccount, $messageModel);
        });

        /**
         * @var $accountModule \modules\account\Module
         */
        $accountModule = Yii::$app->getModule('account');

        if ($app instanceof ApiApplication) {
            return;
        }
    }

    /**
     * @param $chatAccount Chat
     * @param $message ChatMessage
     */
    protected function processChatRestrictions($chatAccount, $message)
    {
        if ($chatAccount->isApproved()) {
            // No need to process approved accounts
            return;
        }

        if ($chatAccount->isHold()) {
            // No need to process hold accounts since they already blocked
            return;
        }

        $isFirstDialogMessage = $message->isFirstMessage;
        $doubleMessagesPeriodAgo = time() - $this->tutorsDoubleMessagesCountCheckPeriod;

        $lastDoublePeriodMessagedTutorsCount = ChatMessage::find()
            // From current sender
            ->andWhere(['sender_id' => $message->sender_id])
            // And not current dialog
            ->andWhere(['not', ['chat_dialog_id' => $message->chat_dialog_id]])
            // For the last doubleMessages period (1 minute by default)
            ->andWhere(['>', 'date_sent', $doubleMessagesPeriodAgo])
            ->count();

        if ($lastDoublePeriodMessagedTutorsCount >= $this->doubleMessagesSuspiciousTutorsCount) {
            // Double messages reason more important than spam one
            $chatAccount->statusReason = Chat::STATUS_REASON_TIME_LIMIT;
            $chatAccount->status = Chat::STATUS_SUSPICIOUS;
        } elseif ($isFirstDialogMessage) {
            // If first message in dialog - check for spam
            $checkPeriodAgo = time() - $this->tutorsCountCheckPeriod;

            $messagedTutorsCount = ChatMessage::find()
                // Find amount of tutors
                ->select(['recipient_id'])
                // This recipient sent messages to
                ->andWhere(['sender_id' => $message->sender_id])
                // During check period (1 day by default)
                ->andWhere(['>', 'date_sent', $checkPeriodAgo])
                // Only first messages
                ->andWhere(['isFirstMessage' => true])
                // Grouped by each tutor
                ->groupBy(['recipient_id'])
                ->count();
            if ($messagedTutorsCount >= $this->suspiciousTutorsCount) {
                // Everything is fine for double messages. Checking rate limit messages
                $chatAccount->statusReason = Chat::STATUS_REASON_SPAM;
                $chatAccount->status = Chat::STATUS_SUSPICIOUS;
            }
        }

        if (!$chatAccount->save()) {
            Yii::error('Failed to update chat account status. Errors: ' . json_encode($chatAccount->getErrors()), 'chat');
        }
    }

    public function eventNewMessage($message, $account, ChatMessage $messageModel)
    {
        $event = new NewMessageEvent([
            'message' => $message,
            'account' => $account,
            'messageModel' => $messageModel,
        ]);
        Event::trigger(self::class, self::EVENT_NEW_MESSAGE, $event);
    }

    // TODO: This should probably moved to some kind of helper etc.
    public function getProhibitSendingText()
    {
        return 'Your account is on hold, you can not send messages until the admin approves it. Please contact our support team.';
    }

    public function getMailDomains($reload = false)
    {
        if ($reload || !$this->mailDomainsList) {
            // Caching query for one day
            /**
             * @var $mailDomains ChatRestriction[]
             */
            $this->mailDomainsList = Yii::$app->cache->getOrSet(ChatRestriction::CACHE_KEY, function () {
                $mailDomainsList = [];
                $mailDomains = ChatRestriction::find()
                    ->andWhere(['type' => ChatRestriction::TYPE_MAIL_DOMAINS])
                    ->all();
                foreach ($mailDomains as $mailDomain) {
                    $mailDomainsList = array_merge($mailDomainsList, $mailDomain->getData());
                }
                return $mailDomainsList;
            }, 24 * 60 * 60);
        }
        return $this->mailDomainsList;
    }
}
