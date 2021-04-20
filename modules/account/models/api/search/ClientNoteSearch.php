<?php

namespace modules\account\models\api\search;

use modules\account\models\AccountNote;
use modules\account\models\api\AccountClient;
use yii\data\ActiveDataProvider;

class ClientNoteSearch extends \modules\account\models\api\AccountNote
{
    public $clientAccountId;
    public function rules()
    {
        return [
            [['clientAccountId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountClient::className(), 'targetAttribute' => ['clientAccountId' => 'id']],
            ['isPinned', 'boolean']
        ];
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
                //using index accountId_isPinned_updatedAt_note_index.
                'defaultOrder' => [
                    'isPinned' => SORT_DESC,
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

        $query->andFilterWhere([self::tableName() . '.accountId' => $this->clientAccountId]);
        $query->andFilterWhere([self::tableName() . '.isPinned' => $this->isPinned]);

        return $dataProvider;
    }
}
