<?php

namespace common\helpers;

use common\components\Formatter;
use modules\account\models\AccountEmail;
use modules\account\models\AccountPhone;
use modules\account\models\PhoneValidation;

class AdditionalDataHelper extends \stdClass
{
    protected static $emailTableName = '{{%account_email}}';
    protected static $phoneTableName = '{{%account_phone}}';
    protected static $accountTableName = '{{%account}}';
    protected static $phoneValidationTableName = '{{%phone_validation}}';
    public static function refillTables()
    {
        PhoneValidation::deleteAll();
        AccountPhone::deleteAll();
        AccountEmail::deleteAll();

        //client's data to insert
        $data = \modules\account\models\Account::findWithoutRestrictions()
            ->joinWith('profile')
            ->select([
                \modules\account\models\AccountWithDeleted::tableName() . '.id as userAccountId',
                \modules\account\models\AccountWithDeleted::tableName()  . '.email',
                \modules\account\models\Profile::tableName() . '.id',
                \modules\account\models\Profile::tableName() . '.phoneNumber'
            ])
            ->isPatient()
            ->asArray()
            ->all();

        /**
         * @var Formatter $formatter
         */
        $formatter = \Yii::$app->formatter;
        $time = date($formatter->MYSQL_DATETIME);
        $emailInsertValues = [];
        foreach ($data as $dataItem) {
            $insertValue = [
                'accountId' => $dataItem['userAccountId'],
                'email' => $dataItem['email'],
                'isPrimary' => true,
                'createdAt' => $time,
            ];
            $emailInsertValues[] = $insertValue;
        }
        \Yii::$app->db
            ->createCommand()
            ->batchInsert(static::$emailTableName, ['accountId', 'email', 'isPrimary', 'createdAt'], $emailInsertValues)
            ->execute();

        $phoneInsertValues = [];
        foreach ($data as $dataItem) {
            $insertValue = [
                'accountId' => $dataItem['userAccountId'],
                'phoneNumber' => $dataItem['phoneNumber'],
                'isPrimary' => true,
                'createdAt' => $time
            ];
            $phoneInsertValues[] = $insertValue;
        }

        \Yii::$app->db
            ->createCommand()
            ->batchInsert(static::$phoneTableName, ['accountId', 'phoneNUmber', 'isPrimary', 'createdAt'], $phoneInsertValues)
            ->execute();
    }

    public static function refillPhoneValidation()
    {
        $phonesQuery = \modules\account\models\AccountPhone::find();
        foreach ($phonesQuery->each() as $accountPhone) {
            /**
             * @var \modules\account\models\AccountPhone $accountPhone
             */
            $validationModel = new \modules\account\models\PhoneValidation();
            $validationModel->phoneId = $accountPhone->id;
            $validationModel->response = '';
            $validationModel->type = \modules\account\models\PhoneValidation::TYPE_MOBILE;
            $validationModel->status = \modules\account\models\PhoneValidation::STATUS_VALID;
            $validationModel->save(false);
        }
    }
}
