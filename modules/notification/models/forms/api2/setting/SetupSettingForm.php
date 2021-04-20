<?php

namespace modules\notification\models\forms\api2\setting;

use api2\components\models\forms\ApiBaseForm;
use Exception;
use modules\account\models\api2\Account;
use modules\notification\models\entities\common\setting\NotificationSetting;
use modules\notification\models\entities\common\setting\NotificationType;
use Yii;
use yii\validators\ExistValidator;

/**
 * Class SetupSettingForm
 * @package modules\notification\models\forms\api2\setting
 */
class SetupSettingForm extends ApiBaseForm
{
    /**
     * @var array
     */
    public $notificationTypes = [];
    /**
     * @var Account
     */
    protected Account $account;

    /**
     * SetupSettingForm constructor.
     * @param Account $account
     * @param array $config
     */
    public function __construct(Account $account, $config = [])
    {
        $this->account = $account;
        parent::__construct($config);
    }

    /**
     * @return array[]
     */
    public function rules()
    {
        return [
            ['notificationTypes', 'validateNotificationTypes', 'skipOnEmpty' => true],
        ];
    }

    /**
     * @param $attribute
     * @throws \yii\base\InvalidConfigException
     */
    public function validateNotificationTypes($attribute)
    {
        $notificationTypes = $this->{$attribute};

        // validate type
        $message = Yii::t('yii', '{attribute} is invalid.', ['attribute' => $attribute]);
        if (!is_array($notificationTypes)) {
            $this->addError($attribute, $message . " Is not array");
            return;
        }

        // validate unique values
        if (count(array_unique($notificationTypes)) < count($notificationTypes)) {
            $this->addError($attribute, $message . " Unique values");
            return;
        }

        // validate existing values
        $existValidator = Yii::createObject([
            'class' => ExistValidator::class,
            'targetClass' => NotificationType::class,
            'targetAttribute' => 'id',
        ]);

        foreach ($notificationTypes as $notificationType) {
            if (!$existValidator->validate($notificationType)) {
                $this->addError($attribute, $message . " Value ({$notificationType}) is invalid");
                return;
            }
        }
    }

    /**
     * @return array|null
     * @throws \yii\db\Exception
     */
    public function setup(): ?bool
    {
        if (!$this->validate()) {
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // clear configuration
            NotificationSetting::deleteAll(['accountId' => $this->account->id]);

            // add new configuration
            $this->buildNotificationSettings();

            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        return true;
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function buildNotificationSettings(): void
    {
        if (!$this->notificationTypes) {
            return;
        }

        // prepare data rows
        $dataRows = [];
        foreach ($this->notificationTypes as $notificationTypeId) {
            $dataRows[] = [
                'accountId' => $this->account->id,
                'notificationTypeId' => $notificationTypeId,
            ];
        }
        $columns = array_keys($dataRows[0]);

        // save to database
        Yii::$app->db->createCommand()
            ->batchInsert(NotificationSetting::tableName(), $columns, $dataRows)
            ->execute();
    }
}
