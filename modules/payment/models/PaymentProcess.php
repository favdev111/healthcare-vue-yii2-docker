<?php

namespace modules\payment\models;

use common\components\behaviors\TimestampBehavior;
use common\components\Formatter;
use common\helpers\EmailHelper;
use Exception;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%payment_process}}".
 *
 * @property int $id
 * @property string $date
 * @property boolean $hasErrors
 * @property boolean $isNotEnoughFunds
 * @property float $earnedToday
 * @property float $paidToday
 * @property int $status
 * @property string $error
 * @property float $availableBalanceAfterPaymentProcess
 * @property string $cancelPayoutReason
 *
 * @property PlatformPayout[] $platformPayouts
 * @property ProcessedLessonTransfer[] $processedLessonTransfers
 */
class PaymentProcess extends \yii\db\ActiveRecord
{
    const STATUS_CREATED = 0;
    const STATUS_COMPLETE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment_process}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
                'updatedAtAttribute' => null,
                'createdAtAttribute' => 'date',
                'value' => TimestampBehavior::currentDate()
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['earnedToday', 'paidToday', 'availableBalanceAfterPaymentProcess'], 'double'],
            [['hasErrors', 'isNotEnoughFunds'], 'boolean'],
            [['status'], 'default', 'value' => static::STATUS_CREATED],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'hasErrors' => 'Has Errors',
            'isNotEnoughFunds' => 'Is Not Enough Funds',
            'earnedToday' => 'Earned Today',
            'paidToday' => 'Paid Today',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatformPayouts(): ActiveQuery
    {
        return $this->hasMany(PlatformPayout::class, ['paymentProcessId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProcessedLessonTransfers(): ActiveQuery
    {
        return $this->hasMany(ProcessedLessonTransfer::class, ['paymentProcessId' => 'id']);
    }


    /**
     * {@inheritdoc}
     * @return \modules\payment\models\query\PaymentProcessQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\payment\models\query\PaymentProcessQuery(get_called_class());
    }

    public static function getLastCompletedProcess(): self
    {
        /**
         * @var self $last
         */
        $last = static::find()->byStatus(static::STATUS_COMPLETE)->orderBy('id DESC')->limit(1)->one();
        return $last;
    }
}
