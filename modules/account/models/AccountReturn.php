<?php

namespace modules\account\models;

use common\components\behaviors\TimestampBehavior;
use common\components\Formatter;
use modules\account\models\api\AccountClient;
use Yii;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "{{%account_returns}}".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $accountId
 * @property string $startDate
 * @property string $description
 * @property integer $reasonCode
 * @property integer $createdBy - user who creates rematch/refund
 * @property integer $employeeId - employee who was related to client in rematch/refund moment
 * @property string $createdAt
 * @property int $jobHireId - additional data. info about jobHire during refund process
 *
 * @property-read  string $note
 * @property AccountWithDeleted $account
 * @property RematchJobHire $rematchJobHires
 */
class AccountReturn extends \yii\db\ActiveRecord
{
    protected static $accountClass = AccountWithDeleted::class;

    const TYPE_REFUND = 1;
    const TYPE_REMATCH = 2;
    const STATISTIC_DATE_START = '2019-06-01 00:00:00';
    public static $typesArray = [
        self::TYPE_REFUND,
        self::TYPE_REMATCH,
    ];

    public static function getIncomingDateFormat()
    {
        /**
         * @var $formatter Formatter
         */
        $formatter = Yii::$app->formatter;
        return $formatter->dateWithSlashesPhp;
    }

    public static function getInternalDateFormat()
    {
        /**
         * @var $formatter Formatter
         */
        $formatter = Yii::$app->formatter;
        return $formatter->MYSQL_DATETIME;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_returns}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'createdBy',
                'updatedByAttribute' => false,
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
                //there is no way to update rows in account_return table
                'updatedAtAttribute' => false,
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'accountId', 'reasonCode'], 'required'],
            [['description'], 'string'],
            [['type', 'accountId', 'reasonCode'], 'integer'],
            [['type'], 'in', 'range' => [self::TYPE_REFUND, self::TYPE_REMATCH]],
            [['accountId'],'exist', 'targetClass' => static::$accountClass, 'targetAttribute' => 'id'],
        ];
    }

    public function getAccount()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'accountId'])->with('profile');
    }

    public function getAuthor()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'createdBy'])->with('profile');
    }

    public function getRematchJobHires()
    {
        return $this->hasMany(RematchJobHire::class, ['accountReturnId' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reasonCode' => 'Reason Code',
            'type' => 'Type',
            'accountId' => 'Account ID',
            'startDate' => 'Start Date',
        ];
    }

    /**
     * @inheritdoc
     * @return \modules\account\models\query\AccountReturnsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\account\models\query\AccountReturnsQuery(get_called_class());
    }
}
