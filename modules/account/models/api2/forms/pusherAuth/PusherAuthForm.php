<?php

namespace modules\account\models\api2\forms\pusherAuth;

use api2\components\models\forms\ApiBaseForm;
use common\components\pusher\Pusher;
use modules\account\models\api2\Account;
use Pusher\PusherException;
use yii\helpers\Json;

/**
 * Class PusherAuthForm
 * @package modules\account\models\api2\forms\pusherAuth
 */
class PusherAuthForm extends ApiBaseForm
{
    /**
     * @var string
     */
    public $channelName;
    /**
     * @var string
     */
    public $socketId;
    /**
     * @var Pusher
     */
    protected $pusher;
    /**
     * @var Account
     */
    protected $account;

    protected $allowChannels = [
        'private-notification-{accountId}'
    ];

    /**
     * PusherAuthForm constructor.
     * @param Account $account
     * @param Pusher $pusher
     * @param array $config
     */
    public function __construct(Account $account, Pusher $pusher, $config = [])
    {
        $this->pusher = $pusher;
        $this->account = $account;
        parent::__construct($config);
    }

    public function init(): void
    {
        parent::init();
        $this->initAllowChannels();
    }

    protected function initAllowChannels(): void
    {
        foreach ($this->allowChannels as &$channel) {
            $channel = str_replace('{accountId}', $this->account->id, $channel);
        }
    }

    /**
     * @return array|array[]
     */
    public function rules()
    {
        return [
            [['channelName', 'socketId'], 'required'],
            ['channelName', 'in', 'range' => $this->allowChannels],
        ];
    }

    /**
     * @return array|null
     */
    public function auth(): ?array
    {
        if (!$this->validate()) {
            return null;
        }

        try {
            $authResult = $this->pusher->auth($this->channelName, $this->socketId);
            return Json::decode($authResult);
        } catch (PusherException $pusherException) {
            return null;
        }
    }
}
