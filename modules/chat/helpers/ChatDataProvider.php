<?php

namespace modules\chat\helpers;

use modules\account\models\Account;
use modules\chat\Module;
use Yii;
use yii\base\Exception;
use yii\data\BaseDataProvider;

class ChatDataProvider extends BaseDataProvider
{
    const TYPE_DIALOGS = 'Dialogs';
    const TYPE_MESSAGES = 'Messages';

    public $key = '_id';

    /**
     * @var Account
     */
    public $user;

    public $type;
    public $dialogId;

    /**
     * @var Module
     */
    protected $chat;

    protected $result;
    protected $totalCount;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!$this->user) {
            throw new Exception('User attribute is required');
        }
        if (!$this->type) {
            throw new Exception('Type attribute is required');
        }
        if ($this->type == self::TYPE_MESSAGES && !$this->dialogId) {
            throw new Exception('Dialog ID is required for this type');
        }
        $this->chat = Yii::$app->getModule('chat');
    }

    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        $models = $this->getResult()['items'];
        if ($models !== null) {
            return $models;
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];

            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } else {
            return array_keys($models);
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        if (!$this->totalCount) {
            $resultCount = $this->getTotalCountFromAPI();
            $this->getPagination()->totalCount = $resultCount['items']['count'];
            $this->totalCount = $resultCount['items']['count'];
        }
        return $this->totalCount;
    }

    protected function getResult()
    {
        if ($this->result) {
            return $this->result;
        }

        $pagination = $this->getPagination();

        if ($pagination === false) {
            $this->result = $this->getListFromApi();
        } else {
            $this->prepareTotalCount();
            $this->result = $this->getListFromApi($pagination->getOffset(), $pagination->getLimit());
        }
        return $this->result;
    }

    protected function getListFromApi($offset = null, $limit = null)
    {
        if ($this->type == self::TYPE_DIALOGS) {
            return $this->chat->getDialogs($this->user, $offset, $limit);
        }
        return $this->chat->getMessages($this->dialogId, $this->user, $offset, $limit);
    }

    protected function getTotalCountFromAPI()
    {
        if ($this->type == self::TYPE_DIALOGS) {
            return $this->chat->getDialogsCount($this->user);
        }
        return $this->chat->getMessagesCount($this->dialogId, $this->user);
    }
}
