<?php

namespace modules\account\events;

use modules\account\models\Account;
use yii\base\Event;

/**
 * Class RequiredFieldsEdited
 * @package modules\account\events
 */
class RequiredFieldsEdited extends Event
{
    const NAME = 'required-fields-edited';

    /**
     * @var Account
     */
    private $account;

    /**
     * @param Account $account
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }
}
