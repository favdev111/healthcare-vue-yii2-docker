<?php

namespace modules\account\models\api2\search;

use modules\account\models\api2\ListPatientModel;
use modules\account\models\Profile;
use yii\data\ActiveDataProvider;

/**
 * @property Profile $accountProfile
 */

class ListPatientSearch extends ListPatientModel
{
    public static function tableName()
    {
        return '{{%account}}';
    }

    public function rules()
    {
        return [
            [['email'], 'string'],
            [['roleId'], 'integer']
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
        $query = ListPatientModel::find()
                ->joinWith('accountProfile')
                ->select(['account.*', 'account_profile.*'])
                ->andWhere([ListPatientModel::tableName() . '.roleId' => 1]);
        // add conditions that should always apply here
        $command = $query->createCommand();
        $dataProvider = $command->queryAll();
        $this->load($params, '');
        // $query->andFilterWhere(['like', 'roleId', $this->roleId]);
        return $dataProvider;
    }
}
