<?php

namespace modules\account\models\api;

use Yii;
use yii\db\ActiveQueryInterface;

/**
 * @inheritdoc
 */
class JobOffer extends \modules\account\models\JobOffer
{
    public function rules()
    {
        $rules = parent::rules();
        $rules['jobExists'] = [['jobId'], 'exist', 'skipOnError' => true, 'targetClass' => Job::className(), 'targetAttribute' => ['jobId' => 'id']];
        return $rules;
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['amount'] = function () {
            return intval($this->formattedAmount);
        };
        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['job'] = 'job';
        $extraFields['tutor'] = 'tutor';
        return $extraFields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::className(), ['id' => 'jobId']);
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = self::findByConditionWithoutRestrictions(['id' => $id]);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = self::findWithoutRestrictions();
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = self::findByConditionWithoutRestrictions($condition);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = self::findBySqlWithoutRestrictions($sql, $params);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = self::findByConditionWithoutRestrictions($condition);
        return $query->all();
    }

    // Proxy-ing default methods as custom ones to allow getting suspended jobs too
    public static function findOneWithoutRestrictions($condition)
    {
        return parent::findOne($condition);
    }

    public static function findWithoutRestrictions()
    {
        return parent::find();
    }

    public static function findByConditionWithoutRestrictions($condition)
    {
        return parent::findByCondition($condition);
    }

    public static function findBySqlWithoutRestrictions($sql, $params = [])
    {
        return parent::findBySql($sql, $params = []);
    }

    public static function findAllWithoutRestrictions($condition)
    {
        return parent::findAll($condition);
    }
}
