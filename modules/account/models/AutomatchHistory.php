<?php

namespace modules\account\models;

use Yii;

/**
 * This is the model class for table "automatch_history".
 *
 * @property int $id
 * @property int $jobId
 * @property array $data
 * @property int $matchedTutor
 */
class AutomatchHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'automatch_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['jobId', 'matchedTutor'], 'integer'],
            [['data'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jobId' => 'Job ID',
            'data' => 'Data',
            'matchedTutor' => 'Matched Tutor',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \modules\account\models\query\AutomatchHistoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\account\models\query\AutomatchHistoryQuery(get_called_class());
    }
}
