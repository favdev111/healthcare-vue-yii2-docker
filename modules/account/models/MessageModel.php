<?php

namespace modules\account\models;

use common\helpers\Url;
use common\models\Zipcode;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use Yii;
use yii\base\Model;

class MessageModel extends Model
{
    const MESSAGE_DATA_SESSION_KEY = 'MessageToSendData';

    public $zipCode;
    public $message;
    public $subjects;
    public $tutor;

    /**
     * @var $chat \modules\chat\Module
     */
    protected $chatModule;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->chatModule = Yii::$app->getModule('chat');
    }

    public function rules()
    {
        return [
            ['message', 'filter', 'filter' => function ($value) {
                return $this->chatModule->clearMessage($value);
            }
            ],
            [['zipCode','message', 'subjects','tutor'], 'required'],
            [['zipCode','message', 'subjects'], 'safe'],
            ['zipCode','exist','targetClass' => Zipcode::class, 'targetAttribute' => 'code','message' => 'Your zip code is not in service area'],
            ['tutor','exist','targetClass' => Account::class, 'targetAttribute' => 'id','message' => 'Account does not exist'],
            [['message'], 'countMessagesFromProfileFormValidator']
        ];
    }

    public function countMessagesFromProfileFormValidator()
    {
        if (!$statistic = AccountClientStatistic::getUserStatistic()) {
            return;
        }
        if (!$statistic->isAllowedToSendMessagesInProfileForm()) {
            $this->addError('', 'Oops! Looks like you’ve contacted too many tutors today. To prevent spam, please try again later or contact us for more information.');
        }
    }

    public function sendMessage($account = null)
    {
        if (!$this->validate()) {
            return false;
        }

        /**
         * @var $account Account
         */
        if (is_null($account)) {
            $account = Yii::$app->user->identity;
        }

        $subjectModel = SubjectOrCategory::findById($this->subjects[0]);
        if (!$subjectModel) {
            return false;
        }
        $tutor = Account::findOne($this->tutor);
        $message = "<b>Subject:</b> {$subjectModel->getName()} \n <b>Message:</b> {$this->message}\n";
        $to = $tutor->chat;
        $from = $account->chat;
        $chatAccount = $account->chat;
        if ($account->isPatient() && $chatAccount->isHold()) {
            // Prevent sending messages in case chat account is blocked
            Yii::$app->response->statusCode = 403;
            return [
                'success' => false,
                'message' => $this->chatModule->getProhibitSendingText(),
            ];
        }

        Job::autogenerateJob(
            $this->subjects,
            $this->zipCode,
            $this->message,
            $account->id
        );

        // Adding From part for chat message (SMS and emails has own headers in template)
        $chatMessage = "<b>New Message from:</b> {$account->profile->showName}\n$message";
        $response = $this->chatModule->sendMessage($chatMessage, $from, $to, 'chat', false);
        if (!$response) {
            return null;
        }

        if ($message !== false) {
            $message = $this->chatModule->hideDataMessage($account, $tutor, $message);
            $messageObject = $response->response;

            /**
             * @var $moduleAccount \modules\account\Module
             */
            $moduleAccount = Yii::$app->getModule('account');
            $moduleAccount->eventNewMessageTutor(
                $account,
                $tutor,
                $message,
                $messageObject,
                $response->model
            );
            $url = Url::to(['/chat/default/index', '#' => $messageObject['chat_dialog_id'] ?? null]);

            //put data into statistic
            static::writeToStatistic($account->id);
            return $url;
        }

        return false;
    }

    public static function writeToStatistic($id = null)
    {
        $model = AccountClientStatistic::getUserStatistic($id);
        $model->{AccountClientStatistic::COUNTER_OF_MESSAGE_FROM_PROFILE_FORM} += 1;
        $model->lastMessageDate = date(Yii::$app->formatter->MYSQL_DATETIME, time());
        $model->save();
    }
}
