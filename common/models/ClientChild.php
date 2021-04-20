<?php

namespace common\models;

use common\components\behaviors\HasGradeRelationBehavior;
use common\components\HtmlPurifier;
use common\components\validators\NameStringValidator;
use modules\account\models\api\AccountClient;
use modules\account\models\Grade;
use modules\account\models\GradeItem;
use Yii;

/**
 * This is the model class for table "client_children".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $firstName
 * @property string $lastName
 * @property string $gender
 * @property string $schoolName
 * @property integer $schoolGradeLevel
 * @property integer $schoolGradeLevelId
 *
 * @property string $fullName
 * @property Grade $grade
 * @property GradeItem $gradeItem
 *
 * @method createGradeItem()
 */
class ClientChild extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_children}}';
    }

    public function behaviors()
    {
        return [
            'grade' => [
                'class' => HasGradeRelationBehavior::class,
                'type' => GradeItem::TYPE_CLIENT_CHILD,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'schoolGradeLevelId'], 'integer'],
            [['firstName','lastName',  'schoolName', 'gender', 'schoolGradeLevel'], 'string', 'max' => 255],
            [['firstName','lastName'], NameStringValidator::className()],
            [['firstName'], 'required'],
            [['firstName','lastName',  'schoolName', 'gender'  ], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute);
            },  'skipOnEmpty' => true
            ],
            ['gender', 'in', 'range' => array_keys(\modules\account\models\Profile::getGenderArray())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Client Profile ID',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'gender' => 'gender',
            'schoolGradeLevelId' => 'schoolGradeLevelId',
            'schoolName' => 'School Name',
            'schoolGradeLevel' => 'School Grade Level',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountClient()
    {
        return $this->hasOne(AccountClient::className(), ['id' => 'accountId']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\ClientChildQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ClientChildQuery(get_called_class());
    }

    public static function findById($id)
    {
        return static::find()->andWhere(['id' => $id])->limit(1)->one();
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
