<?php

namespace modules\account\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\account\models\Account;

/**
 * UserSearch represents the model behind the search form about `modules\account\models\Account`.
 */
class Search extends Account
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'roleId', 'status'], 'integer'],
            [['email', 'createdAt', 'updatedAt'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        // add related fields to searchable attributes
        return array_merge(parent::attributes(), ['profile.firstName']);
    }

    /**
     * Search
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /** @var \modules\account\models\Account $account */
        /** @var \modules\account\models\Profile $profile */

        // get models
        $account = $this->module->model('User');
        $profile = $this->module->model('Profile');
        $userTable = $account::tableName();
        $profileTable = $profile::tableName();

        // set up query relation for `user`.`profile`
        // http://www.yiiframework.com/doc-2.0/guide-output-data-widgets.html#working-with-model-relations
        $query = $account::find();
        $query->joinWith(['profile' => function ($query) use ($profileTable) {
            $query->from(['profile' => $profileTable]);
        }
        ]);

        // create data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // enable sorting for the related columns
        $addSortAttributes = ['profile.firstName'];
        foreach ($addSortAttributes as $addSortAttribute) {
            $dataProvider->sort->attributes[$addSortAttribute] = [
                'asc' => [$addSortAttribute => SORT_ASC],
                'desc' => [$addSortAttribute => SORT_DESC],
            ];
        }

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            "{$userTable}.id" => $this->id,
            'roleId' => $this->roleId,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', '{$userTable}.createdAt', $this->createdAt])
            ->andFilterWhere(['like', '{$userTable}.updatedAt', $this->updatedAt])
            ->andFilterWhere(['like', 'profile.firstName', $this->getAttribute('profile.firstName')]);

        return $dataProvider;
    }
}
