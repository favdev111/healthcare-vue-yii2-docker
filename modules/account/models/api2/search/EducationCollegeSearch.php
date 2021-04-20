<?php

namespace modules\account\models\api2\search;

use modules\account\models\api2\EducationCollege;
use yii\data\ActiveDataProvider;

class EducationCollegeSearch extends EducationCollege
{
    public static function tableName()
    {
        return '{{%education_college}}';
    }

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['country'], 'string']
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = EducationCollege::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['country' => $this->country]);
        return $dataProvider;
    }
}
