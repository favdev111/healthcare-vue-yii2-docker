<?php

namespace modules\core\models\search;

use modules\core\models\ApiLogRequest;
use yii\data\ActiveDataProvider;

class ApiLogRequestSearch extends ApiLogRequest
{
    const REQUEST_TYPE_POST = 'POST';
    const REQUEST_TYPE_GET = 'GET';
    const REQUEST_TYPE_DELETE = 'DELETE';
    const REQUEST_TYPE_PUT = 'PUT';

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['id'], 'integer'],
            [['request_type', 'request_url', 'controller_name', 'action_name', 'status', 'started_at', 'finished_at'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params): ActiveDataProvider
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'started_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

//        if ($this->started_at) {
//            $fromDateFormat = DateFactory::component()->receiveAppDateTimeFormat();
//            $toFormat       = DateFactory::MACHINE_DATE_FORMAT;
//            $query->andWhere(['like', 'started_at', DateFactory::component()->convertDateTime($this->started_at, $fromDateFormat, $toFormat)]);
//        }

        $query->andFilterWhere(['like', 'request_method', $this->request_method])
            ->andFilterWhere(['like', 'request_url', $this->request_url])
            ->andFilterWhere(['like', 'controller_name', $this->controller_name])
            ->andFilterWhere(['like', 'action_name', $this->action_name])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
