<?php

namespace modules\payment\models\api\search;

use modules\account\models\Account;
use modules\payment\models\api\PaymentBankAccount;
use yii\data\ActiveDataProvider;

class PaymentBankAccountSearch extends PaymentBankAccount
{
    public function rules()
    {
        return $this->companyIdValidationRules();
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        // This method is used to search for own jobs. Showing all including suspended
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updatedAt' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
             $query->where('0=1');
            return $dataProvider;
        }

        if ($this->companyId) {
            $companyAccount = Account::findOne($this->companyId);
            $query->andFilterWhere([self::tableName() . '.paymentCustomerId' => ($companyAccount->paymentCustomer->id ?? null)]);
        }

        return $dataProvider;
    }
}
