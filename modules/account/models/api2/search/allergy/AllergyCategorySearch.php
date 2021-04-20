<?php

namespace modules\account\models\api2\search\allergy;

use api2\components\models\forms\ApiBaseForm;
use modules\account\models\api2\health\allergy\AllergyCategory;
use yii\data\ActiveDataProvider;

/**
 * Class AllergyCategorySearch
 * @package modules\account\models\api2\search\allergy
 */
class AllergyCategorySearch extends ApiBaseForm
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $isMedicalGroup;

    /**
     * @return array[]
     */
    public function rules()
    {
        return [
            ['name', 'string'],
            ['isMedicalGroup', 'boolean', 'trueValue' => 'true', 'falseValue' => 'false'],
            [
                'isMedicalGroup',
                'filter',
                'filter' => static function ($value) {
                    return $value === 'true';
                },
                'skipOnEmpty' => true,
                'skipOnError' => true
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
        $query = AllergyCategory::find()->joinWith('medicalAllergyGroup');

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

        if ($this->isMedicalGroup) {
            $query->andWhere(['IS NOT', 'medical_allergy_group.allergyCategoryId', null]);
        } elseif ($this->isMedicalGroup === false) {
            $query->andWhere(['IS', 'medical_allergy_group.allergyCategoryId', null]);
        }

        return $dataProvider;
    }
}
