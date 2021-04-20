<?php

namespace modules\account\models;

use Yii;
use yii\base\InvalidCallException;

/**
 * This is the model class for table "{{%list_added_account}}".
 *
 * @property integer $id
 * @property integer $ownerId
 * @property integer $accountId
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Account $account
 * @property Account $owner
 */
class ListAddedAccount extends \yii\db\ActiveRecord
{
    private const EXIST_ERROR = 'Student has already been added.';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_added_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountId'], 'required'],
            [['accountId'], 'integer'],
            [
                ['accountId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => AccountWithDeleted::class,
                'targetAttribute' => ['accountId' => 'id'],
            ],
            [
                ['accountId'],
                'unique',
                'targetAttribute' => ['ownerId', 'accountId'],
                'message' => self::EXIST_ERROR,
                'comboNotUnique' => self::EXIST_ERROR,
            ],
            [['accountId'], 'checkIsVerified', 'skipOnError' => true],
        ];
    }

    public function checkIsVerified($attribute, $params)
    {
        if (
            $this->account
            && !$this->account->isVerified()
        ) {
            $this->addError($attribute, 'Please ask the student to add a payment method to their account.');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ownerId' => 'Owner ID',
            'accountId' => 'Account ID',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
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
     * @param Account $student
     * @return bool
     */
    public static function isStudentAdded(Account $student)
    {
        if (Yii::$app->user->isGuest) {
            throw new InvalidCallException('Method could not be used by quest user');
        }
        return static::find()
            ->andWhere(['ownerId' => Yii::$app->user->identity->id])
            ->andWhere(['accountId' => $student->id])
            ->exists();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(Account::class, ['id' => 'ownerId']);
    }

    /**
     * @param int $accountId
     * @param bool $isTutor
     * @param string|null $searchName
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     * @throws \yii\base\InvalidConfigException
     */
    public static function getList(
        int $accountId,
        bool $isTutor = true,
        string $searchName = null,
        int $limit = null
    ) {
        $tutorNameSearchCondition = ['LIKE', 'account_profile.firstName', $searchName . '%', false];

        $relationFiled = static::tableName() . '.';
        $field = static::tableName() . '.';
        if ($isTutor) {
            $relationFiled .= 'accountId';
            $field .= 'ownerId';
            $nameSearchCondition = $tutorNameSearchCondition;
        } else {
            $relationFiled .= 'ownerId';
            $field .= 'accountId';
            $nameSearchCondition = [
                'or',
                $tutorNameSearchCondition,
                ['LIKE', 'account_profile.lastName', $searchName . '%', false]
            ];
        }

        $accountModel = Yii::$app->getModuleAccount()->getAccountModel(true);
        $accounts = $accountModel::find()
            ->joinWith('profile')
            ->rightJoin(static::tableName(), $accountModel::tableName() . '.id = ' . $relationFiled)
            ->andWhere([$field => $accountId])
            ->orderBy(['account_profile.firstName' => SORT_ASC]);

        if ($searchName) {
            $accounts->andFilterWhere($nameSearchCondition);
        }

        return $accounts->limit($limit)->all();
    }

    /**
     * @param int $ownerId
     * @param int $accountId
     * @return ListAddedAccount|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function addStudent($ownerId, $accountId)
    {
        $addAccount = new static();
        $addAccount->ownerId = $ownerId;
        $addAccount->accountId = $accountId;
        $addAccount->save();

        return $addAccount;
    }
}
