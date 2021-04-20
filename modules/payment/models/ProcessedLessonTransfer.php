<?php

namespace modules\payment\models;

use modules\payment\models\query\TransactionQuery;
use Yii;

/**
 * This is the model class for table "{{%processed_lessons_transfers}}".
 *
 * @property int $id
 * @property int $paymentProcessId
 * @property int $lessonTransferId
 *
 * @property Transaction $lessonTransfer
 */
class ProcessedLessonTransfer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%processed_lessons_transfers}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['paymentProcessId', 'lessonTransferId'], 'integer'],
            [['lessonTransferId'], 'exist', 'skipOnError' => true, 'targetClass' => Transaction::class, 'targetAttribute' => ['lessonTransferId' => 'id']],
            [
                ['lessonTransferId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Transaction::class,
                'targetAttribute' => ['lessonTransferId' => 'id'],
                'filter' => function ($query) {
                    /**
                     * @var TransactionQuery $query
                     */
                    $query->lessonTransfer();
                }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'paymentProcessId' => 'Payment Process ID',
            'lessonTransferId' => 'Lesson Transfer ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLessonTransfer()
    {
        return $this->hasOne(Transaction::className(), ['id' => 'lessonTransferId']);
    }

    /**
     * {@inheritdoc}
     * @return ProcessedLessonTransferQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProcessedLessonTransferQuery(get_called_class());
    }
}
