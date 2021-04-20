<?php

namespace modules\account\models\backend;

use common\helpers\AccountStatusHelper;
use Yii;
use modules\account\models\AccountClientStatistic;
use modules\account\models\AccountNote;
use modules\account\models\AccountWithDeleted;
use modules\account\models\Token;
use modules\notification\models\notifications\SpecialistAccountApprovedNotification;
use yii\helpers\ArrayHelper;

/**
 * Class Account
 * @property $notes
 * @package modules\account\models\backend
 */
class Account extends AccountWithDeleted
{
    public static function commissionsList()
    {
        return [
            self::COMMISSION_ZERO => 'Zero Commission',
            self::COMMISSION_FIVE => '5% Commission',
            self::COMMISSION_FIFTEEN => '15% Commission',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $allowedStatus = AccountStatusHelper::statuesDefault();

        return ArrayHelper::merge(
            parent::rules(),
            [
                [['status'], 'in', 'range' => $allowedStatus],
                [['commission'], 'integer'],
            ]
        );
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $statistic = AccountClientStatistic::getUserStatistic($this->id);
        if (
            $this->isSpecialist()
            && isset($changedAttributes['status'])
            && $this->status == AccountStatusHelper::STATUS_ACTIVE
            && !$statistic->congratulationEmailSent
        ) {
            // When admin approves specialist (whether the email is confirmed or not)
            Yii::$app->notifier->send($this, Yii::createObject(SpecialistAccountApprovedNotification::class));

            $statistic->congratulationEmailSent = true;
            $statistic->save(false);
        }
    }

    public function beforeDelete()
    {
        $tokens = Token::find()->andWhere(['accountId' => $this->id])->all();
        if ($tokens) {
            foreach ($tokens as $token) {
                $token->delete();
            }
        }
        return parent::beforeDelete();
    }

    /**\
     * @return void|\yii\db\ActiveQuery
     */
    public function getNotes()
    {
        $this->hasMany(AccountNote::class, ['accountId' => 'id']);
    }
}
