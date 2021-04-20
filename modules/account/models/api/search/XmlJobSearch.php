<?php

namespace modules\account\models\api\search;

use modules\account\models\Account;
use modules\account\models\Job;
use yii\data\ActiveDataProvider;

class XmlJobSearch extends JobSearch
{
    const XML_DEFAULT_PAGE_SIZE = 50;

    public function rules()
    {
        $rules = parent::rules();
        $rules['accountExist'] = [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['accountId' => 'id']];
        return $rules;
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Job::find()->joinWith('subjects')->joinWith('account.profile as studentProfile');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updatedAt' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'defaultPageSize' => static::XML_DEFAULT_PAGE_SIZE,
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andWhere([self::tableName() . '.status' => self::PUBLISH]);
        $query->andWhere(['not', [self::tableName() . '.close' => self::STATUS_CLOSED]]);
        $query->andFilterWhere([
            self::tableName() . '.accountId' => $this->accountId,
        ]);

        $query->distinct();

        return $dataProvider;
    }
}
