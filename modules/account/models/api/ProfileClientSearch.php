<?php

namespace modules\account\models\api;

use modules\account\helpers\ConstantsHelper;
use modules\account\models\AccountClientStatistic;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class ProfileClientSearch
 * @package modules\account\models\api
 */
final class ProfileClientSearch extends Model
{
    /**
     * @var
     */
    public $query;
    /**
     * @var
     */
    public $clientLessonStatuses;
    /**
     * @var
     */
    public $clientPaymentStatuses;
    /**
     * @var
     */
    public $clientFlag;
    /**
     * @var
     */
    public $clientId;
    /**
     * @var
     */
    public $employeeId;
    /**
     * @var bool
     */
    public $onlyWithEmployee = false;

    /**
     * @var
     */
    public $positiveBalance;
    /**
     * @var
     */
    public $negativeBalance;
    /**
     * @var
     */
    public $zeroBalance;
    /**
     * @var
     */
    public $lessThan200;
    /**
     * @var
     */
    public $onlyQueued;
    /**
     * @var
     */
    public $exceptQueued;

    /**
     * @var string $phoneNumber
     */
    public $phoneNumber;

    /**
     * @var string $email
     */
    public $email;

    /**
     * @var array
     */
    public static $sort = [
        'name' => [
            'label' => 'Name',
            'asc' => ['profile.firstName' => SORT_ASC],
            'desc' => ['profile.firstName' => SORT_DESC],
            'default' => SORT_ASC,
        ],
        'lessonStatus' => [
            'label' => 'Lesson status',
            'asc' => ['statistic.clientLessonStatus' => SORT_ASC],
            'desc' => ['statistic.clientLessonStatus' => SORT_DESC],
            'default' => SORT_ASC,
        ],
        'paymentStatus' => [
            'label' => 'Payment status',
            'asc' => ['sortedPaymentStatus' => SORT_ASC],
            'desc' => ['sortedPaymentStatus' => SORT_DESC],
            'default' => SORT_ASC,
        ],
        'lastLessonDate' => [
            'label' => 'Last lesson date',
            'asc' => ['lastLessonDate' => SORT_ASC],
            'desc' => ['lastLessonDate' => SORT_DESC],
            'default' => SORT_ASC,
        ],
        'activity' => [
            'label' => 'Activity',
            'asc' => ['statistic.lastLessonDate' => SORT_DESC, 'statistic.clientLessonStatus' => SORT_DESC],
            'desc' => ['statistic.lastLessonDate' => SORT_ASC, 'statistic.clientLessonStatus' => SORT_ASC],
            'default' => SORT_ASC,
        ],
        'startDate' => [
            'asc' => ['startDate' => SORT_ASC],
            'desc' => ['startDate' => SORT_DESC],
            'default' => SORT_DESC,
            'label' => 'Start Date',
        ],
        'custom' => [
            'asc' => ['employee_clients.position' => SORT_ASC],
            'desc' => ['employee_clients.position' => SORT_DESC],
            'default' => SORT_ASC,
            'label' => 'Custom'
        ],
        'flagDate' => [
            'asc' => ['flagDate' => SORT_ASC],
            'desc' => ['flagDate' => SORT_DESC],
            'default' => SORT_DESC,
            'label' => 'Flag date'
        ]
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['query'], 'string', 'max' => 255],
            [['clientFlag', 'phoneNumber', 'email'], 'string'],
            [['clientId'], 'integer'],
            [['employeeId'], 'integer'],
            [
                'clientId',
                'exist',
                'skipOnError' => true,
                'targetClass' => Account::class,
                'targetAttribute' => ['clientId' => 'id'],
            ],
            [['onlyWithEmployee', 'exceptQueued', 'onlyQueued'], 'boolean'],
            [
                ['clientLessonStatuses'],
                'each',
                'rule' => [
                    'in', 'range' => array_keys(ConstantsHelper::clientLessonStatus()),
                ],
            ],
            [
                ['clientPaymentStatuses'],
                'each',
                'rule' => [
                    'in', 'range' => array_keys(ConstantsHelper::clientPaymentStatus()),
                ],
            ],
            [['positiveBalance', 'negativeBalance', 'zeroBalance', 'lessThan200'], 'boolean']
        ];
    }

    /**
     * @return array
     */
    public static function getSortList(): array
    {
        return ArrayHelper::getColumn(static::$sort, 'label', true);
    }

    /**
     * @param array $params
     * @return ProfileClientSearch|ActiveDataProvider
     * @throws NotSupportedException
     */
    public function search(array $params)
    {
        //null values to the end
        self::$sort['startDate']['asc'] = new Expression('-startDate DESC');

        // get models
        $profileTable = ProfileClient::tableName();
        $clientStatistic = AccountClientStatistic::tableName();
        $employeeTable = EmployeeClient::tableName();

        // set up query relation for `user`.`profile`
        // http://www.yiiframework.com/doc-2.0/guide-output-data-widgets.html#working-with-model-relations
        $query = AccountClient::find();
        $query
            ->joinWith([
                'profile' => function ($query) use ($profileTable) {
                    assert($query instanceof ActiveQuery);
                    $query->from(['profile' => $profileTable]);
                },
            ])
            ->joinWith([
                'clientStatistic' => function ($query) use ($clientStatistic) {
                    assert($query instanceof ActiveQuery);
                    $query->from(['statistic' => $clientStatistic]);
                },
            ])
            ->joinWith('accountMainPhoneNumber.phoneValidation');
        $query->addSelect(AccountClient::tableName() . '.*');
        $query->addSelect(
            new Expression(
                'CASE ' . 'statistic.clientPaymentStatus '
                . 'WHEN ' . ConstantsHelper::PAYMENT_STATUS__PAYMENT_ISSUES . ' THEN 1 '
                . 'WHEN ' . ConstantsHelper::PAYMENT_STATUS__NO_PAYMENT_ADDED . ' THEN 2 '
                . 'WHEN ' . ConstantsHelper::PAYMENT_STATUS__PAYMENT_ADDED . ' THEN 3 '
                // By Default No Payment Added status is set
                . 'ELSE 2 END AS sortedPaymentStatus'
            )
        );

        // create data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updatedAt' => SORT_DESC,
                ],
            ],
        ]);

        $dataProvider->sort->attributes = static::$sort;
        $dataProvider->sort->attributes['updatedAt'] = [
            'label' => 'Updated At',
            'attribute' => 'updatedAt',
            'asc' => ['updatedAt' => SORT_ASC],
            'desc' => ['updatedAt' => SORT_DESC],
            'default' => SORT_DESC,
        ];

        if (isset($params['sort']) && $params['sort'] === 'custom') {
            unset($dataProvider->sort->attributes['custom']);
        }

        if (!($this->load($params, ''))) {
            return $dataProvider;
        }

        if (!$this->validate()) {
            return $this;
        }
        if (!empty($this->employeeId) || $this->onlyWithEmployee || $this->onlyQueued || $this->exceptQueued) {
            $query->joinWith(['employeeClient']);
            if (!isset($params['sort']) || empty($params['sort'])) {
                $query->orderBy(['employee_clients.position' => SORT_ASC]);
            }
        }

        if ($this->phoneNumber) {
            $query->leftJoin(AccountPhone::tableName() . ' as phones', 'phones.accountId = account.id');
            $query->andWhere(['like', 'phones.phoneNumber', $this->phoneNumber . '%', false]);
        }

        if ($this->email) {
            $query->leftJoin(AccountEmail::tableName() . ' as emails', 'emails.accountId = account.id');
            $query->andFilterWhere(['like', 'emails.email', $this->email]);
        }



        $query->andFilterWhere([
            $employeeTable . '.employeeId' => $this->employeeId,
        ]);

        if ($this->clientId) {
            $query->joinWith(['employeeClient']);
            $query->andWhere([$employeeTable . '.clientId' => $this->clientId]);
        }
        if ($this->onlyWithEmployee) {
            $query->andWhere([
                'not',
                [$employeeTable . '.employeeId' => null]
            ]);
        }

        $queuedCondition = [
            'and',
            [$employeeTable . '.employeeId' => null],
            [AccountClient::tableName() . '.flag' => AccountClient::FLAG_RED]
        ];

        if ($this->onlyQueued) {
            $query->andWhere($queuedCondition);
        } elseif ($this->exceptQueued) {
            $query->andWhere(['not', $queuedCondition]);
        }

        $query
            ->andFilterWhere(['like', 'fullName', $this->query])
            ->andFilterWhere(['in', 'statistic.clientLessonStatus', $this->clientLessonStatuses])
            ->andFilterWhere(['in', 'statistic.clientPaymentStatus', $this->clientPaymentStatuses]);

        if (isset($this->clientFlag)) {
            $query->andWhere([
                'flag' => $this->clientFlag,
            ]);
        }

        $balanceCondition = [];
        if (!empty($this->zeroBalance)) {
            array_push($balanceCondition, ['statistic.balance' => 0]);
        }

        if (!empty($this->positiveBalance)) {
            array_push($balanceCondition, ['>', 'statistic.balance', 0]);
        }

        if (!empty($this->negativeBalance)) {
            array_push($balanceCondition, ['<', 'statistic.balance', 0]);
        }

        if (!empty($this->lessThan200)) {
            array_push($balanceCondition, ['<', 'statistic.balance', 200]);
        }

        if ($balanceCondition) {
            $query->andWhere(array_merge(['or'], $balanceCondition));
        }

        return $dataProvider;
    }
}
