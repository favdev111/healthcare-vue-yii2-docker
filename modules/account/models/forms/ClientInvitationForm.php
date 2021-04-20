<?php

namespace modules\account\models\forms;

use modules\account\models\Account;
use Yii;
use yii\base\Model;

class ClientInvitationForm extends Model
{
    public $email;
    public $accountId;
    public $hasBeenSend = false;

    const DUPLICATE_EMAIL_ERROR_MESSAGE = 'This email is already in use. Please enter a different email address.';
    const DUPLICATE_EMAIL_ERROR_MESSAGE_TUTOR = 'Email address is already taken by Tutor. Please use a different email address.';

    public function rules()
    {
        return [
            ['email', 'duplicateEmailValidator'],
        ];
    }

    /**
     * process client's account and send message
     * @param Account $account
     */
    public static function send($account)
    {
        $form = new self();
        $form->email = $account->email;
        $form->accountId = $account->id;
        if ($form->validate()) {
            if ($account->sendEmailClientInvitation()) {
                $form->hasBeenSend = true;
                $account->clientInvited = true;
                $account->save(false);
            } else {
                $form->addError('hasBeenSend', 'Error while sending message.');
            }
        }
        return $form;
    }

    /**
     * looking for user with the same email who was invited earlier
     */
    public function duplicateEmailValidator()
    {
        $duplicateAccounts = Account::find()
            ->byEmail($this->email)
            ->andWhere(['not', ['id' => $this->accountId]])
            ->all();

        /**
         * @var Account $duplicateAccount
         */
        $duplicateAccount = array_shift($duplicateAccounts);

        if (!empty($duplicateAccount)) {
            $this->addError(
                'email',
                $duplicateAccount->isTutor() ? static::DUPLICATE_EMAIL_ERROR_MESSAGE_TUTOR : static::DUPLICATE_EMAIL_ERROR_MESSAGE
            );
        }
    }
}
