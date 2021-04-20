<?php

namespace common\models;

use common\helpers\QueueHelper;
use modules\account\models\Account;
use modules\account\models\api\ClientBalanceTransaction;
use modules\notification\helpers\NotificationHelper;
use modules\notification\models\Notification;
use modules\payment\models\Transaction;
use UrbanIndo\Yii2\Queue\Job;
use Yii;

/**
 * This is the model class for table "post_payment".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $amount
 * @property integer $status
 * @property string $date
 *
 * @property Account $account
 */
class PostPayment extends \yii\db\ActiveRecord
{
    const DATE_FORMAT = 'Y-m-d';
    const POST_PAYMENT_DATE_FORMAT = "m/d/Y";
    const STATUS_NOT_RECEIVED = 0;
    const STATUS_RECEIVED = 1;

    /*checking changes of status property in afterSave() method. Needs for calling manuallyReceived() method.*/
    protected $checkStatusChanges = true;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post_payment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountId', 'status'], 'integer'],
            [['amount'], 'double', 'min' => 1],
            [['date'], 'string'],
            [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['accountId' => 'id']],
            [['date'], 'required', 'when' => function ($model) {
                return !empty($this->amount);
            }
            ],
            [['date'], 'date', 'format' => 'php:' . static::POST_PAYMENT_DATE_FORMAT, 'when' => [$this, 'isDateChanged']],
            [['date'],function ($attribute) {
                $dateTime = \DateTime::createFromFormat(static::POST_PAYMENT_DATE_FORMAT . " H:i:s", $this->date . " 23:59:59");
                $now = new \DateTime();
                if ($dateTime < $now) {
                    $this->addError($attribute, 'Incorrect post-payment date.');
                }
            }, 'when' => [$this, 'isDateChanged'],
            ],
            [['amount'], 'required', 'when' => function ($model) {
                return !empty($this->date);
            }
            ],
            ['date', function () {
                $dateTime = \DateTime::createFromFormat(static::POST_PAYMENT_DATE_FORMAT, $this->date);
                $dateTime = $dateTime->format(PostPayment::DATE_FORMAT);
                $this->date = $dateTime;
            },'when' => [$this, 'isDateChanged']
            ],
            ['status', 'default', 'value' => 0],
        ];
    }

    public function isDateChanged($model)
    {
        return $this->isAttributeChanged('date');
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account ID',
            'amount' => 'Amount',
            'status' => 'Status',
            'date' => 'Date',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->checkStatusChanges && isset($changedAttributes['status']) && $this->status === static::STATUS_RECEIVED) {
            $this->manuallyReceived();
        }
    }

    /*process click on Received button on POST_PAYMENT_SHOULD_PAY notification */
    protected function manuallyReceived()
    {
        $this->addAmountToClientBalance();
        $this->unPinRelatedNotificationsShouldPay();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId']);
    }


    private function unPinRelatedNotificationsShouldPay()
    {
        Notification::unPinRelatedNotifications(NotificationHelper::OBJECT_TYPE_POST_PAYMENT, $this->id, NotificationHelper::TYPE_POST_PAYMENT_SHOULD_PAY);
    }

    public function unpinRelatedSuccessChargeNotifications()
    {
        Notification::unPinRelatedNotifications(NotificationHelper::OBJECT_TYPE_POST_PAYMENT, $this->id, NotificationHelper::TYPE_POST_PAYMENT_TRANSACTION_SUCCESS);
    }
    public function unpinRelatedFailedChargeNotifications()
    {
        Notification::unPinRelatedNotifications(NotificationHelper::OBJECT_TYPE_POST_PAYMENT, $this->id, NotificationHelper::TYPE_POST_PAYMENT_TRANSACTION_DECLINES);
    }

    private function addAmountToClientBalance()
    {
        $clientBalanceTransaction = new ClientBalanceTransaction([
            'clientId' => $this->accountId,
            'amount' => $this->amount,
            'type' => ClientBalanceTransaction::TYPE_OFFLINE,
        ]);
        $clientBalanceTransaction->save(false);
    }

    private function getTransactionQuery()
    {
        return Transaction::find()
            ->andWhere(['objectType' => Transaction::TYPE_CLIENT_BALANCE_POST_PAYMENT])
            ->andWhere(['objectId' => $this->id]);
    }

    public function isTransactionExist()
    {
        return $this->getTransactionQuery()->exists();
    }

    public function isAllowedToCreateTransaction()
    {
        $existNotErrorTransaction = $this->getTransactionQuery()->andWhere(['not',['status' => Transaction::STATUS_ERROR]])->exists();
        if ($existNotErrorTransaction) {
            $this->logPostPayment('Not allowed ot create transaction. Error transaction exists. ', true);
            return false;
        }
        $today = new \DateTime();
        $postPaymentDate = new \DateTime($this->date);
        if ($today < $postPaymentDate) {
            $this->logPostPayment('Not allowed ot create transaction. Post payment date greater then current date. ', true);
            return false;
        }
        return true;
    }

    public static function logPostPayment(string $message, bool $isError = false): void
    {
        if ($isError) {
            \Yii::info($message, 'post-payment');
        } else {
            \Yii::error($message, 'post-payment');
        }
    }

    public function createTransaction()
    {
        $this->logPostPayment('Trying to create transaction for post payment with id = ' . $this->id);
        if (!$this->isAllowedToCreateTransaction()) {
            return false;
        }
        $transaction = new Transaction();
        $transaction->objectType = Transaction::TYPE_CLIENT_BALANCE_POST_PAYMENT;
        $transaction->amount = $this->amount;
        $transaction->objectId = $this->id;
        $transaction->processDate = date('Y-m-d');
        $transaction->type = Transaction::STRIPE_CHARGE;
        $transaction->status = Transaction::STATUS_NEW;
        $transaction->studentId = $this->accountId;
        if ($transaction->save()) {
            $this->logPostPayment('Transaction created with id = ' . $this->id . '.Task added to queue.');
            QueueHelper::processCharge($transaction);
            return $transaction->id;
        }
        $this->logPostPayment(
            'Not allowed ot create transaction. Transaction has not been created. '
            . "\n validation errors:" . ($transaction->hasErrors() ? json_encode($transaction->getErrors()) : ''),
            true
        );
        return false;
    }

    public function paymentReceived()
    {
        $this->checkStatusChanges = false;
        $this->status = static::STATUS_RECEIVED;
        $this->save(false);
    }

    /**
     * @return \modules\payment\models\query\TransactionQuery
     */
    public function getLastRelatedTransactionQuery()
    {
        return $this->getTransactionQuery()->orderBy('id DESC')->limit(1);
    }
}
