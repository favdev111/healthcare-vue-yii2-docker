<?php

namespace common\models;

use modules\account\models\Account;
use modules\account\models\ListAddedAccount;
use modules\account\Module;
use yii\base\Model;
use yii\httpclient\Client;

/**
 * Class OnlineTutoring
 * @package common\models
 */
class OnlineTutoring extends Model
{
    const ID_DELIMITER = '_';
    const REQUEST_METHOD = 'post';
    const USER_STUDENT = 'student';
    const USER_TUTOR = 'tutor';
    const SCENARIO_IFRAME = 'iFrame';

    protected $fullUrl = null;

    protected $userName;
    protected $opponentName;
    public $opponentId;
    //need to display left panel on online-tutoring page
    protected $userRole;

    public function rules()
    {
        $compareAttribute = \Yii::$app->user->identity->isTutor() ? 'accountId' : 'ownerId';
        $filterAttribute = \Yii::$app->user->identity->isTutor() ? 'ownerId' : 'accountId';
        $rules = [
            [['opponentId'], 'integer'],
            [['opponentId'], 'required'],
            [['opponentId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['opponentId' => 'id']],
            [
                ['opponentId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ListAddedAccount::class,
                'targetAttribute' => ['opponentId' => $compareAttribute],
                'filter' => function ($query) use ($filterAttribute) {
                    $query->andWhere([$filterAttribute => \Yii::$app->user->id]);
                }
            ],
        ];

        return $rules;
    }

    /**
     * @return string
     */
    public function getUserRole()
    {
        return \Yii::$app->user->identity->isTutor() ? static::USER_TUTOR : static::USER_STUDENT;
    }

    /**
     * @return int|string
     */
    public function getStudentId()
    {
        return \Yii::$app->user->identity->isTutor() ? $this->opponentId : \Yii::$app->user->id;
    }

    /**
     * @return int|string
     */
    public function getTutorId()
    {
        return \Yii::$app->user->identity->isTutor() ? \Yii::$app->user->id : $this->opponentId;
    }

    public function getUserName()
    {
        if (empty($this->userName)) {
            $this->userName = \Yii::$app->user->identity->profile->fullName();
        }
        return $this->userName;
    }

    public function getOpponentName()
    {
        if (empty($this->opponentName)) {
            /**
             * @var Account $account
             */
            $account = Account::find()
                ->joinWith('profile')
                ->andWhere([Account::tableName() . '.id' => $this->opponentId])
                ->limit(1)
                ->one();
            if (!empty($account)) {
                $this->opponentName = $account->profile->fullName();
            }
        }
        return $this->opponentName;
    }

    /**
     * @return bool
     */
    public function isNeedShowIFrame()
    {
        return !$this->hasErrors() && !empty($this->opponentId);
    }

    public function getOppositeRole()
    {
        return $this->getUserRole() === static::USER_TUTOR ? static::USER_STUDENT : static::USER_TUTOR;
    }

    /**
     * @return string|null
     */
    public function getRoomCode()
    {
        return $this->roomCode;
    }

    /**
     * @return string|null
     */
    public function getRoomLink()
    {
        return $this->fullUrl;
    }

    /**
     * @return null|Module
     */
    public function getModule()
    {
        return \Yii::$app->getModule('account');
    }

    /**
     * @return array
     */
    protected function prepareRequestParams()
    {
        $requestParams = [];
        $requestParams['id'] = YII_ENV . static::ID_DELIMITER . $this->studentId . static::ID_DELIMITER . $this->tutorId;
        $requestParams['name'] = $this->getUserName();
        $requestParams['api_key'] = $this->getModule()->onlineTutoringApiKey;
        $requestParams['profile_picture'] = \Yii::$app->user->identity->getAvatarUrl();
        $requestParams['invite_show'] = false;
        return $requestParams;
    }

    /**
     * send request for filling model data
     */
    public function sendApiRequest()
    {
        //check current model state
        $isDataAlreadySetCondition = !empty($this->fullUrl);

        if ($isDataAlreadySetCondition) {
            return true;
        }

        $requestParams = $this->prepareRequestParams();

        $client = new Client();

        //send request to API
        $response = $client->createRequest()
            ->setFormat(Client::FORMAT_URLENCODED)
            ->setMethod(static::REQUEST_METHOD)
            ->setUrl($this->getModule()->onlineTutoringApiUrl)
            ->setData($requestParams)
            ->send();

        if (empty($response->data)) {
            return false;
        }

        //fill model data
        $this->fullUrl =  $response->data['full_url'];
        return true;
    }
}
