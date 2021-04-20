<?php

namespace modules\account\models\api2\search;

use api2\components\models\forms\ApiBaseForm;
use modules\account\models\api2\health\LifestyleDiet;
use yii\data\ActiveDataProvider;

/**
 * Class AllergySearch
 * @package modules\account\models\api2\search
 */
class LifestyleDietSearch extends ApiBaseForm
{
    /**
     * @var string
     */
    public $name;

    /**
     * @return array[]
     */
    public function rules()
    {
        return [
            ['name', 'string'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search(array $params = [])
    {
        $query = LifestyleDiet::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [20, 300],
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
