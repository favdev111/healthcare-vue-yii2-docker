<?php

namespace modules\account\models\forms\healthProfile\search;

use common\models\healthProfile\HealthProfile;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class HealthProfileSearch
 * @package modules\account\models\forms\healthProfile\search
 */
class HealthProfileSearch extends Model
{
    /**
     * @var array
     */
    public $accountId;

    /**
     * @return string[][]
     */
    public function rules()
    {
        return [
            ['accountId', 'safe']
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = HealthProfile::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['accountId' => $this->accountId]);

        return $dataProvider;
    }
}
