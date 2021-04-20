<?php

namespace modules\account\models\api\tutor;

/**
 * @inheritdoc
 */
class Review extends \common\models\Review
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Reviewer::className(), ['id' => 'studentId'])->via('lesson');
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['student'] = 'student';
        return $fields;
    }
}
