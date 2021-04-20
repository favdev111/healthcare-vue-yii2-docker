<?php

namespace modules\account\models;

use common\components\HtmlPurifier;
use Yii;

/**
 * This is the model class for table "{{%account_education}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $collegeId
 * @property integer $major
 * @property integer $degreeId
 * @property integer $enrolled
 * @property integer $graduated
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Account $account
 * @property EducationCollege $college
 * @property EducationDegree $degree
 */
class Education extends \yii\db\ActiveRecord
{
    const EDUCATION_ID_ENROLLED = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_education}}';
    }

    public function formName()
    {
        return 'educations';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['major'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
            [['collegeId', 'major'], 'required'],
            [['degreeId'], 'required', 'message' => 'Type of degree cannot be blank'],
            [['collegeId', 'degreeId', 'enrolled', 'graduated'], 'integer'],
            ['enrolled', 'integer', 'min' => 1900],
            ['graduated', 'integer', 'min' => 1900],
            ['enrolled', 'required', 'when' => function ($model) {
                return ($model->degreeId == self::EDUCATION_ID_ENROLLED);
            }
            ],
            ['graduated', 'required', 'when' => function ($model) {
                return ($model->degreeId != self::EDUCATION_ID_ENROLLED);
            }
            ],
            [['collegeId'], 'exist', 'skipOnError' => true, 'targetClass' => EducationCollege::className(), 'targetAttribute' => ['collegeId' => 'id']],
            [['degreeId'], 'exist', 'skipOnError' => true, 'targetClass' => EducationDegree::className(), 'targetAttribute' => ['degreeId' => 'id']],
            [['major'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account',
            'collegeId' => 'School',
            'major' => 'Major',
            'degreeId' => 'Degree',
            'enrolled' => 'Enrolled',
            'graduated' => 'Graduated',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCollege()
    {
        return $this->hasOne(EducationCollege::class, ['id' => 'collegeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDegree()
    {
        return $this->hasOne(EducationDegree::class, ['id' => 'degreeId']);
    }
}
