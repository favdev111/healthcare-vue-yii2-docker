<?php

namespace modules\analytics\models\search;

use common\components\behaviors\FilterDatesBehavior;
use modules\account\models\api\AccountClient;
use modules\account\models\api\ClientBalanceTransaction;
use modules\account\models\Lesson;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use modules\payment\models\api\Transaction;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class StatisticSearch extends Model
{
    public $subjectOrCategoryIds = [];
    public $hasLessonWithinDays = null;
    public $query = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            $this->getFilterDatesRulesArray(),
            [
                ['query', 'string'],
                ['subjectOrCategoryIds', 'each', 'rule' => ['string']],
                ['hasLessonWithinDays', 'integer'],
            ]
        );
    }

    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                FilterDatesBehavior::class,
            ]
        );
    }

    /**
     * @param $params
     * @param $pageSize
     *
     * @return []
     */
    public function search($params)
    {
        $this->load($params, '');

        if (!$this->validate()) {
            return false;
        }

        $allClientsQuery = AccountClient::find()
            ->asArray()
            ->select(AccountClient::tableName() . '.id');

        if (is_numeric($this->hasLessonWithinDays)) {
            $allClientsQuery->andWhere([AccountClient::tableName() . '.id' => AccountClient::getListOfActiveClients((int)$this->hasLessonWithinDays)]);
        }

        if (!empty($this->query)) {
            $allClientsQuery->joinWith('profile profile')
                ->andWhere([
                    'or',
                    ['like', 'profile.firstName', $this->query],
                    ['like', 'profile.lastName', $this->query],
                ])
            ;
        }

        $query = ClientBalanceTransaction::find()
            ->andWhere(['clientId' => $allClientsQuery])
            ->leftJoin(Transaction::tableName(), \modules\account\models\ClientBalanceTransaction::tableName() . '.transactionId=' . \modules\payment\models\Transaction::tableName() . '.id')
            ->leftJoin(Lesson::tableName(), \modules\payment\models\Transaction::tableName() . '.objectId=' . Lesson::tableName() . '.id')
            ->andWhere([
                'or',
                [Transaction::tableName() . '.objectType' => array_merge(Transaction::clientBalanceTypes(), Transaction::lessonTypes())],
                [ClientBalanceTransaction::tableName() . '.transactionId' => null]
            ]);

        $selectedSubjectIds = SubjectOrCategory::convertToSubjectIds($this->subjectOrCategoryIds);
        if (!empty($selectedSubjectIds)) {
            $query->andWhere([
                'or',
                ['subjectId' => $selectedSubjectIds],
                [
                    'or',
                    [Transaction::tableName() . '.objectType' => Transaction::clientBalanceTypes()],
                    [ClientBalanceTransaction::tableName() . '.transactionId' => null]
                ]
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'updatedAt' => SORT_DESC,
                ],
            ],
        ]);

        return $dataProvider;
    }
}
