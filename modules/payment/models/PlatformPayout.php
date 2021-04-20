<?php

namespace modules\payment\models;

use common\components\Formatter;
use Yii;

/**
 * This is the model class for table "{{%platform_payouts}}".
 *
 * @property int $id
 * @property int $paymentProcessId
 * @property string $createdAt
 * @property string $updatedAt
 * @property int $status
 * @property array $response
 * @property string $stripeId
 * @property float $amount
 * @property int $source
 *
 * @property PaymentProcess $paymentProcess
 */
class PlatformPayout extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 1;
    const STATUS_ERROR = 2;
    const STATUS_SUCCESS = 3;

    const SOURCE_BA = 1;
    const SOURCE_BA_LABEL = 'bank_account';
    const SOURCE_CARD = 2;
    const SOURCE_CARD_LABEL = 'card';

    public static $sourceCodes = [
        self::SOURCE_BA_LABEL => self::SOURCE_BA,
        self::SOURCE_CARD_LABEL => self::SOURCE_CARD,
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%platform_payouts}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['paymentProcessId', 'status'], 'integer'],
            [['amount'], 'double'],
            [['source'], 'in', 'range' => static::$sourceCodes],
            [['response'], 'safe'],
            [['stripeId'], 'string', 'max' => 255],
            [['paymentProcessId'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentProcess::class, 'targetAttribute' => ['paymentProcessId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'paymentProcessId' => 'Payment Process ID',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'status' => 'Status',
            'response' => 'Response',
            'stripeId' => 'Stripe ID',
            'amount' => 'Amount',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentProcess()
    {
        return $this->hasOne(PaymentProcess::class, ['id' => 'paymentProcessId']);
    }


    /**
     * {@inheritdoc}
     * @return PlatformPayoutQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PlatformPayoutQuery(get_called_class());
    }
}
