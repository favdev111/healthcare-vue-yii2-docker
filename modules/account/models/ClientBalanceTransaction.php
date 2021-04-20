<?php

namespace modules\account\models;

use common\components\ActiveQuery;
use common\components\HtmlPurifier;
use modules\account\models\forms\ClientInvitationForm;
use modules\account\models\query\AccountQuery;
use modules\account\Module;
use modules\payment\models\Transaction;
use Yii;

/**
 * This is the model class for table "client_balance_transaction".
 *
 * @property integer $id
 * @property integer $clientId
 * @property double $amount
 * @property integer $type
 *
 * @property Account $client
 * @property Transaction $transaction
 * @property boolean $hide
 * @property integer $transactionId
 * @property double $balance
 * @property double $displayedBalance
 * @property string $note
 */
class ClientBalanceTransaction extends \yii\db\ActiveRecord
{
    const TYPE_OFFLINE = 1;
    const TYPE_TRANSACTION_AUTO = 2;
    const TYPE_TRANSACTION_MANUAL = 3;
    const TYPE_TRANSACTION_POST_PAYMENT = 4;

    protected $sendInvitationAfterSave = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_balance_transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clientId', 'type', 'transactionId'], 'integer'],
            [['amount'], 'required'],
            [['amount'], 'double'],
            ['type', 'default', 'value' => self::TYPE_OFFLINE],
            'clientIdExist' => [['clientId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountWithBlocked::class, 'targetAttribute' => ['clientId' => 'id'], 'filter' => function ($query) {
                /**
                 * @var $query AccountQuery
                 */
                $query->isPatient();
            }
            ],
            [['transactionId'], 'exist', 'skipOnError' => true, 'targetClass' => Transaction::className(), 'targetAttribute' => ['transactionId' => 'id']],
            [['note'], 'required', 'when' => function () {
                return $this->type === static::TYPE_OFFLINE && empty($this->transactionId);
            }
            ],
            [['note'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert && !empty($this->client->clientStatistic)) {
            $this->balance = $this->client->clientStatistic->balance + $this->amount;
        }
        if ($insert && ($this->amount > 0)) {
            $isPositiveTransactionsExists = static::find()->andWhere(['clientId' => $this->clientId])->andWhere(['>', 'amount', 0])->exists();
            if (!$isPositiveTransactionsExists) {
                $this->sendInvitationAfterSave = true;
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'clientId' => Yii::t('app', 'Client ID'),
            'amount' => Yii::t('app', 'Amount'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(AccountWithBlocked::class, ['id' => 'clientId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransaction()
    {
        $paymentModule = Yii::$app->getModule('payment');
        $transactionClassName = get_class($paymentModule->model('Transaction'));
        return $this->hasOne($transactionClassName, ['id' => 'transactionId']);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        foreach ($scenarios as $scenario) {
            $scenario['type'] = '!type';
        }
        return $scenarios;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            /**
             * @var $accountModule Module
             */
            if ($this->sendInvitationAfterSave) {
                ClientInvitationForm::send($this->client);
            }
        }
    }

    public function isManual(): bool
    {
        return ($this->type === static::TYPE_OFFLINE) && empty($this->transactionId);
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['amount'] = function () {
            return (float)$this->amount;
        };
        return $fields;
    }

    public function getTypeLabel($key = null)
    {
        $labelArray = [
            static::TYPE_OFFLINE => 'Manual Credit Input',
            static::TYPE_TRANSACTION_AUTO => 'Automatic',
            static::TYPE_TRANSACTION_MANUAL => 'Manual Charge',
            static::TYPE_TRANSACTION_POST_PAYMENT => "Automatic <br> Post Payment",
        ];
        return $labelArray[$key ?? $this->type];
    }

    public function isRelatedToRefund()
    {
        return !empty($this->transaction)
            && (
                $this->transaction->isTypeRefund()
                || $this->transaction->isTypePartialRefund()
            )
            && $this->type == static::TYPE_TRANSACTION_AUTO;
    }

    public function getTableRowDescription()
    {
        /**
         * @var \modules\payment\models\Transaction $transaction
         */
        $transaction = $this->transaction ?? null;
        if (empty($transaction)) {
            $description = '<span>' . "Manual Credit Input <br> Balance refill" . '</span>';
        } elseif (!empty($transaction) && ($transaction->isLesson() || $transaction->isLessonBatchPayment())) {
            $date = Yii::$app->formatter->asDateWithSlashes($transaction->lesson->fromDate);
            $firstPart =  $this->isRelatedToRefund() ? 'Refund' : '<br>' . $transaction->lesson->tutor->profile->showName;
            $description = "{$firstPart} <br> ({$transaction->lesson->subject->name} $date)"  . '<br>';
            $description .= '<span>Lesson duration ' . $transaction->lesson->getDuration() . '</span>';
        } elseif ($transaction->isPostPaymentTransaction()) {
            $description =  '<span>' . 'Post payment balance refill' . '</span>';
        } else {
            $description = ($this->isRelatedToRefund() ? "Refund<br>" : '') . "Balance refill";
        }
        return $description ;
    }

    /**
     * @return string
     */
    public function getDisplayedBalance()
    {
        if (is_null($this->balance)) {
            return '';
        }
        return ($this->balance || $this->balance == 0) ? number_format($this->balance, 2, '.', '') : '';
    }

    public static function setMoneyIncomeConditions($query)
    {
        /**
         * @var ActiveQuery $query
         */
        return $query
            ->leftJoin(
                Transaction::tableName(),
                Transaction::tableName() . '.id = ' . ClientBalanceTransaction::tableName() . '.transactionId'
            )
            ->andWhere(
                [
                    'or',
                    [
                        Transaction::tableName() . '.objectType' => Transaction::clientBalanceTypes(),
                    ],
                    [
                        Transaction::tableName() . '.id' => null,
                    ],
                ]
            );
    }

    public static function getCashBasis($clientsList)
    {
        $query = static::setMoneyIncomeConditions(static::find());
        return (float)$query
            ->andWhere(['clientId' => $clientsList])
            ->sum(ClientBalanceTransaction::tableName() . '.amount');
    }

    public static function setSpentMoneyConditions($query)
    {
        /**
         * @var ActiveQuery $query
         */
        return $query
            ->joinWith('transaction')
            ->andWhere([Transaction::tableName() . '.objectType' => Transaction::lessonTypes()])
            ->joinWith('transaction.lesson');
    }

    public static function getAccrualBasis($clientsList, $subjects = null)
    {
        return -1 * static::setSpentMoneyConditions(static::find())
            ->andWhere(['clientId' => $clientsList])
            ->andFilterWhere(['subjectId' => $subjects])
            ->sum(ClientBalanceTransaction::tableName() . '.amount');
    }
}
