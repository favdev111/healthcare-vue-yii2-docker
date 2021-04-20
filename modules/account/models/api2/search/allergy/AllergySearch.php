<?php

namespace modules\account\models\api2\search\allergy;

use api2\components\models\forms\ApiBaseForm;
use modules\account\models\api2\health\allergy\AllergyCategory;
use modules\account\models\api2\health\allergy\Allergy;
use yii\data\ActiveDataProvider;

/**
 * Class AllergySearch
 * @package modules\account\models\api2\search
 */
class AllergySearch extends ApiBaseForm
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $allergyCategoryId;

    /**
     * @return array[]
     */
    public function rules()
    {
        return [
            ['name', 'string'],
            [
                'allergyCategoryId',
                'exist',
                'skipOnEmpty' => true,
                'targetClass' => AllergyCategory::class,
                'targetAttribute' => ['allergyCategoryId' => 'id'],
            ],
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
        $query = Allergy::find();

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
        $query->andFilterWhere(['allergyCategoryId' => $this->allergyCategoryId]);

        return $dataProvider;
    }
}
