<?php

namespace modules\chat\models;

use modules\account\models\AccountWithDeleted;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\chat\models\ChatMessage;

/**
 * ChatMessageSearch represents the model behind the search form about `modules\chat\models\ChatMessage`.
 */
class ChatMessageSearch extends ChatMessage
{
    public $senderEmail;
    public $recipientEmail;
    public $dataSentStart;
    public $dataSentEnd;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sender_id', 'recipient_id'], 'integer'],
            [['_id', 'message', 'chat_dialog_id', 'createdAt', 'updatedAt', 'senderEmail', 'recipientEmail'], 'safe'],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();
        $query->joinWith('messageSender as sender');
        $query->joinWith('messageRecipient as recipient');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'date_sent' => SORT_DESC,
                ],
            ],
        ]);
        $dataProvider->sort->attributes['senderEmail'] = [
            'asc' => ['sender.email' => SORT_ASC],
            'desc' => ['sender.email' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['recipientEmail'] = [
            'asc' => ['recipient.email' => SORT_ASC],
            'desc' => ['recipient.email' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);
        $query->andFilterWhere(['like', 'sender.email', $this->senderEmail]);
        $query->andFilterWhere(['like', 'recipient.email', $this->recipientEmail]);

        $query->andFilterWhere(['like', '_id', $this->_id])
            ->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['like', 'chat_dialog_id', $this->chat_dialog_id]);

        return $dataProvider;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessageSender()
    {
        return $this->hasOne(AccountWithDeleted::className(), ['id' => 'accountId'])->viaTable(Chat::tableName() . ' senderChat', ['chatUserId' => 'sender_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessageRecipient()
    {
        return $this->hasOne(AccountWithDeleted::className(), ['id' => 'accountId'])->viaTable(Chat::tableName() . ' recipientChat', ['chatUserId' => 'recipient_id']);
    }
}
