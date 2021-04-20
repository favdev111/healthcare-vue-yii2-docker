<?php

namespace modules\account\models\api\tutor;

use modules\account\models\AccountSubject;

/**
 * @inheritdoc
 */
class TutorAccountSubject extends AccountSubject
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['subject'] = 'subject';
        return $fields;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::className(), ['id' => 'subjectId']);
    }
}
