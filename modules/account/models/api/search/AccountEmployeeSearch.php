<?php

namespace modules\account\models\api\search;

use modules\account\models\Account;
use modules\account\models\api\AccountEmployee;
use modules\account\models\api\AccountTeam;
use modules\account\models\api\EmployeeClient;
use modules\account\models\api\Profile;
use modules\account\models\Role;
use modules\account\models\Team;
use yii\behaviors\AttributeTypecastBehavior;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Class AccountEmployeeSearch
 * @package modules\account\models\api\search
 */
class AccountEmployeeSearch extends AccountEmployee
{
    /**
     * @var
     */
    public $query;
    /**
     * @var
     */
    public $countClients;
    //use this flag to return only employees that have related clients
    /**
     * @var bool
     */
    public $onlyWithClients = false;
    /**
     * @var bool
     */
    public $teamId;

    public function rules()
    {
        return [
          [['query'], 'string'],
          [['onlyWithClients'], 'boolean'],
          [['status', 'teamId'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'countClients' => AttributeTypecastBehavior::TYPE_INTEGER,
                ],
                'typecastAfterValidate' => false,
                'typecastAfterFind' => true,
            ],
        ]);
    }

    /**
     * @param $params
     * @return array|ActiveDataProvider
     * @throws \yii\base\NotSupportedException
     */
    public function search($params)
    {
        if (!$this->load($params, '') && !$this->validate()) {
            return [];
        }

        $query = static::find();
        $query->joinWith('profile');
        $query->joinWith('employeeClients');
        $query->with('employeeClients.employee');
        $query->with('employeeClients.client.jobs.jobHires');
        $query->addSelect(Account::tableName() . '.*, COUNT(' . EmployeeClient::tableName() . '.id) as countClients');
        if (!empty($this->teamId)) {
            $employeeWithTeamCondition = [
                'and',
                [AccountTeam::tableName() . '.teamId' => $this->teamId],
                [Account::tableName() . '.roleId' => Role::ROLE_COMPANY_EMPLOYEE],
            ];

            $query->leftJoin(
                \modules\account\models\api\AccountTeam::tableName(),
                'account_teams.accountId = account.id'
            );

            $query->andWhere($employeeWithTeamCondition);
        }
        $query->groupBy('account.id');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'countClients' => SORT_DESC,
                ],
            ],
        ]);

        //display rows without profile last in all cases
        $dataProvider->sort->attributes['firstName'] = [
            'default' => SORT_DESC,
            'label' => 'Name',
            'asc' => [new Expression('`firstName` IS NULL ASC, `firstName` ASC')],
            'desc' => [new Expression('`firstName` IS NULL ASC, `firstName` DESC')],
        ];

        $dataProvider->sort->attributes['countClients'] = [
            'asc' => [new Expression('`firstName` IS NULL ASC, `countClients` ASC')],
            'desc' => [new Expression('`firstName` IS NULL ASC, `countClients` DESC')],
        ];

        $query->andFilterWhere([static::tableName() . '.status' => $this->status]);

        if ($this->onlyWithClients) {
            $query->andWhere([
                'not',
                [
                    EmployeeClient::tableName() . '.employeeId' => null,
                ],
            ]);
        }

        $query->andFilterWhere([
            'or',
            ['like', Profile::tableName() . '.firstName', $this->query],
            ['like', Profile::tableName() . '.lastName', $this->query]
        ]);

        return $dataProvider;
    }
}
