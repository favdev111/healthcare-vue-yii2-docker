<?php

namespace modules\account\models;

use Yii;

/**
 * This is the model class for table "change_logs".
 *
 * @property int $id
 * @property int $objectType Object Type
 * @property int $actionType Change type
 * @property int $madeBy Id person who made update from account table
 * @property int $objectId Id person who made update from account table
 * @property array $oldValue Value before changes
 * @property array $newValue Value after changes
 * @property string $date Change date
 * @property string $description Description
 *
 * @property string $preparedDescription Description
 * @property string $actionName
 * @property string $objectTypeName
 * @property  $madeByAccount
 * @property  string $comment
 * @property $author Account who made a change
 */
class ChangeLog extends \yii\db\ActiveRecord
{
    const OBJECT_TYPE_ACCOUNT = 1;
    const OBJECT_TYPE_JOB = 2;
    const OBJECT_TYPE_JOB_HIRE = 3;

    const ACTION_TYPE_FLAG_CHANGE = 1;
    const ACTION_TYPE_RATE_CHANGE = 2;

    //account model who made changes
    public $madeByAccount;
    public $comment;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%change_logs}}';
    }

    /**
     * return array when there are more types
     * @return string
     */
    public function getObjectTypeName()
    {
        return 'Account';
    }

    public function getActionName()
    {
        return 'Flag change';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['objectType', 'actionType'], 'required'],
            [['objectType', 'actionType', 'madeBy'], 'integer'],
            [['oldValue', 'newValue', 'date'], 'safe'],
            [['description'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'objectType' => 'Object Type',
            'actionType' => 'Change type',
            'madeBy' => 'Id person who made update from account table',
            'objectId' => 'Id of account related to changes from account table',
            'oldValue' => 'Value before changes',
            'newValue' => 'Value after changes',
            'date' => 'Change date',
            'description' => 'Description',
        ];
    }

    /**
     * fill specific values for types
     */
    public function fillDefaults(): void
    {
        $this->madeBy = $this->author->id ?? null;
        $this->date = date('Y-m-d H:i:s');
    }

    /**
     * fill description field automatically
     * Override in child classes
     * @return string
     */
    public function getPreparedDescription(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     * @return \modules\account\models\query\ChangeLogsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\account\models\query\ChangeLogsQuery(get_called_class());
    }

    public function beforeSave($insert)
    {
        $this->fillDefaults();
        $this->description = $this->preparedDescription;
        return parent::beforeSave($insert);
    }

    public function getAuthor()
    {
        if (empty($this->madeByAccount)) {
            $this->madeByAccount = $this->getAuthorRelation()->limit(1)->one();
        }
        return ($this->madeByAccount);
    }

    public function getAuthorRelation()
    {
        return $this->hasOne(AccountWithDeleted::class, ['madeBy' => 'id']);
    }
}
