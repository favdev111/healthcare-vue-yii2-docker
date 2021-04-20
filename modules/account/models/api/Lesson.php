<?php

namespace modules\account\models\api;

use modules\payment\models\Transaction;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;

class Lesson extends \modules\account\models\Lesson
{
    const DATE_FROM_GREATER_THAN_DATE_TO_ERROR_MESSAGE = 'Lesson start could not be later than lesson finish.';
    const DATE_FROM_LESS_THAN_DATE_TO_ERROR_MESSAGE = 'Lesson finish could not be later than lesson start.';

    public function scenarios()
    {
        $scenarios['update'] = ['fromDate', 'toDate'];
        return $scenarios;
    }

    public function fields()
    {
        return array_merge(parent::fields(), [
           'duration' => function () {
               return $hoursBilled = $this->minutesDuration / 60;
           },
            'fromDate' => 'convertedFromDate',
            'toDate' => 'convertedToDate',
            'amount' => function () {
                return (double)$this->amount;
            },
            'fee' => function () {
                return (double)$this->fee;
            },
        ]);
    }

    public function extraFields()
    {
        $fields = [
            'studentName' => function () {
                return $this->student->getFullName();
            },
            'tutorName' => function () {
                return $this->tutor->getFullName();
            },
            'transaction' => function () {

                return Transaction::find()
                    ->andWhere(['objectId' => $this->id, 'objectType' => Transaction::TYPE_LESSON])
                    ->one();
            },
            'clientBalanceAmount' => 'clientPrice',
        ];

        return $fields;
    }

    /**
     * @param $query ActiveQuery
     * @return mixed
     */
    protected static function addOwnCondition($query)
    {
        $query->joinWith('student');
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = parent::findByCondition([static::tableName() . '.id' => $id]);
        static::addOwnCondition($query);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::find();
        static::addOwnCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::find($condition);
        static::addOwnCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySql($sql, $params);
        static::addOwnCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByCondition($condition);
        static::addOwnCondition($query);
        return $query->all();
    }
}
