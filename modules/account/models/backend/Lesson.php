<?php

namespace modules\account\models\backend;

use common\models\Review;
use modules\account\helpers\Timezone;
use modules\account\models\Profile;
use modules\payment\models\Transaction;
use Yii;

/**
 * @inheritdoc
 */
class Lesson extends \modules\account\models\Lesson
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Account::className(), ['id' => 'studentId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutor()
    {
        return $this->hasOne(Account::className(), ['id' => 'tutorId']);
    }
}
