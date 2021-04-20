<?php

namespace modules\notification\channels\pusher;

use common\components\pusher\notifications\NotificationInterface;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class NotificationModel
 * @package modules\channels\pusher
 *
 * @property string $message
 */
class NotificationModel extends BaseObject implements NotificationInterface
{
    /**
     * @var array Notification
     */
    public $data = [];
    /**
     * @var string Notification message
     */
    private $_message;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (!$this->message) {
            throw new InvalidConfigException('The "message" property must be set.');
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->data;
        $data['message'] = $this->getMessage();
        return $data;
    }

    /**
     * @param string $value
     * @return string
     */
    public function setMessage(string $value)
    {
        $this->_message = $value;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->_message;
    }
}
