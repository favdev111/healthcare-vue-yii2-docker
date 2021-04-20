<?php

namespace modules\account\models\api\search;

use common\components\behaviors\FilterDatesBehavior;
use common\components\validators\JobHireClientValidator;
use modules\account\models\api\AccountReturn;
use modules\account\models\api\AccountClient;
use modules\account\models\JobHire;
use modules\account\models\RematchJobHire;
use yii\data\ActiveDataProvider;

class AccountReturnSearch extends AccountReturn
{
    public $jobHireId;
    public $employeeId;
    public $tutorId;
    //select only data where createdAt > static::STATISTIC_DATE_START
    public $userStatisticStartDateCondition = false;
    public $onlyWithHires = false;
    public function rules()
    {
        $rules = [
            [['type', 'accountId','employeeId', 'tutorId'], 'integer'],
            [['startDate'], 'date', 'type' => 'datetime', 'format' => 'php:' . static::getIncomingDateFormat(), 'timestampAttribute' => 'startDate', 'timestampAttributeFormat' => 'php:' . static::getInternalDateFormat()],
            [['accountId'],'exist', 'targetClass' => AccountClient::class, 'targetAttribute' => 'id'],
            [['jobHireId'], JobHireClientValidator::class],
            [['reasonCode'], 'integer'],
        ];
        return array_merge($rules, $this->getFilterDatesRulesArray());
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [FilterDatesBehavior::class]);
    }

    public function search($params)
    {
        $query = static::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        if (!$this->load($params, '') && !$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->joinWith('rematchJobHires.jobHire');
        if ($this->type === static::TYPE_REMATCH) {
            $employeeIdField = JobHire::tableName() . '.responsibleId';
        } else {
            $employeeIdField = static::tableName() . '.createdBy';
        }

        $query = $this->filterDate($query, static::tableName());
        $query->andFilterWhere([static::tableName() . '.type' => $this->type]);
        $query->andFilterWhere([static::tableName() . '.accountId' => $this->accountId]);
        $query->andFilterWhere([static::tableName() . '.reasonCode' => $this->reasonCode]);
        $query->andFilterWhere([RematchJobHire::tableName() . '.jobHireId' => $this->jobHireId]);
        $query->andFilterWhere([JobHire::tableName() . '.tutorId' => $this->tutorId]);
        $query->andFilterWhere([$employeeIdField => $this->employeeId]);
        if ($this->userStatisticStartDateCondition) {
            $query->andWhere(['>=', static::tableName() . '.createdAt', static::STATISTIC_DATE_START]);
        }
        if ($this->onlyWithHires && ($this->type == static::TYPE_REMATCH)) {
            $query->andWhere(['not', [JobHire::tableName() . '.id' => null]]);
        }
        $query->distinct();

        return $dataProvider;
    }
}
