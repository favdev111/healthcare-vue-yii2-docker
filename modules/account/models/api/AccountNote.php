<?php

namespace modules\account\models\api;

use Yii;
use yii\db\ActiveQueryInterface;

/**
 * This is the model class for table "account_note".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $content
 * @property string $createdAt
 * @property string $createdBy
 * @property string $updatedAt
 * @property string $updatedByt
 *
 * @property Account $account
 * @property Account $creator
 * @property Account $editor
 */
class AccountNote extends \modules\account\models\AccountNote
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(AccountClient::className(), ['id' => 'accountId']);
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = parent::findByCondition(['id' => $id]);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::find();
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::findByCondition($condition);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySql($sql, $params);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByCondition($condition);
        return $query->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(Account::class, ['id' => 'createdBy']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEditor()
    {
        return $this->hasOne(Account::class, ['id' => 'updatedBy']);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id',
            'content',
            'isPinned',
            'createdAt',
            'updatedAt',
        ];
    }

    public function extraFields()
    {
        return [
          'creator',
          'editor'
        ];
    }
}
