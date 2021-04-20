<?php

namespace modules\account\models\api\tutor;

/**
 * @inheritdoc
 */
class Education extends \modules\account\models\Education
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCollege()
    {
        return $this->hasOne(EducationCollege::className(), ['id' => 'collegeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDegree()
    {
        return $this->hasOne(EducationDegree::className(), ['id' => 'degreeId']);
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['college'] = 'college';
        $fields['degree'] = 'degree';
        return $fields;
    }
}
