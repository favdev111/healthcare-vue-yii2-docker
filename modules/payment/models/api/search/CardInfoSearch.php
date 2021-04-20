<?php

namespace modules\payment\models\api\search;

use modules\account\models\api\Account;
use modules\account\models\api\AccountClient;
use modules\payment\models\api\CardInfo;
use yii\data\ActiveDataProvider;

class CardInfoSearch extends CardInfo
{
    public $accountId;

    public function rules()
    {
        return array_merge(
            [
                [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountClient::class, 'targetAttribute' => ['accountId' => 'id']],
            ],
            static::companyIdValidationRules()
        );
    }


    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        // This method is used to search for own jobs. Showing all including suspended
        $query = self::find()->joinWith('account');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
             $query->where('0=1');
            return $dataProvider;
        }
        $query->andFilterWhere([
            AccountClient::tableName() . '.id' => $this->accountId,
        ]);

        if ($this->companyId) {
            // TODO: Refactording of the whole logic required for company owner and admin. using parent model in order to find all required data
            $query = \modules\payment\models\CardInfo::find();
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'createdAt' => SORT_DESC,
                    ],
                ],
            ]);
            $companyAccount = Account::findOne($this->companyId);
            $query->andWhere([self::tableName() . '.stripeCustomerId' => ($companyAccount->paymentCustomer->id ?? null)]);
            return $dataProvider;
        }

        return $dataProvider;
    }
}
