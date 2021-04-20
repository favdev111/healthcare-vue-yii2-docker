<?php

namespace modules\payment\models;

use modules\account\models\AccountWithDeleted;
use modules\payment\models\query\DeclineChargeQuery;
use modules\account\models\Account;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%decline_charge}}".
 *
 * @property integer $id
 * @property integer $chargeId
 * @property integer $studentId
 * @property integer $tutorId
 * @property integer $declineTime
 *
 * @property Transaction $charge
 * @property Account $student
 * @property Account $tutor
 */
class DeclineCharge extends \yii\db\ActiveRecord
{
    const ONE_DAY_IN_SECONDS = 86400;

    const DECLINE_TRANSACTION_LIMIT = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%decline_charge}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'declineTime',
                'updatedAtAttribute' => false,
            ]
        ];
    }

    /**
     * @param Transaction $transaction
     * @return bool
     */
    public static function createNew(Transaction $transaction)
    {
        $rootTransaction = $transaction->getRootParent();
        $newDeclineCharge = new DeclineCharge();
        $newDeclineCharge->setAttributes([
            'studentId' => $rootTransaction->studentId,
            'tutorId' => $rootTransaction->tutorId,
            'chargeId' => $rootTransaction->id,
        ]);
        if (!$newDeclineCharge->save()) {
            Yii::error('Failed to save declined transaction entity. Erros: ' . json_encode($newDeclineCharge->getErrors()), 'payment');
        }
        return $newDeclineCharge;
    }

    /**
     * @param Transaction $transaction
     * @return bool
     */
    public static function isLimitPassed(Transaction $transaction)
    {
        $rootTransaction = $transaction->getRootParent();

        return (static::find()->byCharge($rootTransaction)->count() >= static::DECLINE_TRANSACTION_LIMIT);
    }

    /**
     * Is this tutor-student pair has declined transaction on this day.
     * @param Transaction $transaction
     * @return bool
     */
    public static function isNotifiedToday(Transaction $transaction)
    {
        $query = static::find()
            ->byStudent($transaction->student);

        if ($transaction->tutor) {
            $query->byTutor($transaction->tutor);
        }

        return $query->exceptCharge($transaction->getRootParent())
            ->declinedToday()
            ->exists();
    }

    /**
     * Is this transaction already declined today
     * @param Transaction $transaction
     * @return bool
     */
    public static function isDeclinedToday(Transaction $transaction)
    {
        $declinedTransaction = static::find()
            ->byCharge($transaction)
            ->orderLast()
            ->one();

        if (!$declinedTransaction) {
            return false;
        }

        return ($declinedTransaction->isDayPassed() === false);
    }



    /**
     * release declined notifications of student
     * @param Account $student
     * @return int
     */
    public static function releaseDeclinedTransaction(Account $student)
    {
        return static::deleteAll(['studentId' => $student->id]);
    }

    /**
     * is one day passed after decline
     * @return bool
     */
    public function isDayPassed()
    {
        return ($this->declineTime < (time() - self::ONE_DAY_IN_SECONDS));
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chargeId', 'studentId'], 'required'],
            [['chargeId', 'studentId', 'declineTime'], 'integer'],
            [['chargeId'], 'exist', 'skipOnError' => true, 'targetClass' => Transaction::className(), 'targetAttribute' => ['chargeId' => 'id']],
            [['studentId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountWithDeleted::className(), 'targetAttribute' => ['studentId' => 'id']],
            [['tutorId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountWithDeleted::className(), 'targetAttribute' => ['tutorId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'chargeId' => Yii::t('app', 'Charge ID'),
            'studentId' => Yii::t('app', 'Student ID'),
            'tutorId' => Yii::t('app', 'Tutor ID'),
            'declineTime' => Yii::t('app', 'Decline Time'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCharge()
    {
        return $this->hasOne(Transaction::className(), ['id' => 'chargeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Account::className(), ['id' => 'studentId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutor()
    {
        return $this->hasOne(AccountWithDeleted::className(), ['id' => 'tutorId']);
    }

    /**
     * @inheritdoc
     * @return \modules\payment\models\query\DeclineChargeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DeclineChargeQuery(static::class);
    }
}
