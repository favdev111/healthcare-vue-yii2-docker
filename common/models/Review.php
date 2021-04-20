<?php

namespace common\models;

use common\helpers\AccountStatusHelper;
use modules\account\helpers\EventHelper;
use modules\account\models\Account;
use common\components\HtmlPurifier;
use modules\account\models\AccountWithDeleted;
use modules\account\models\Lesson;
use Yii;

/**
 * This is the model class for table "{{%review}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $lessonId
 * @property integer $articulation
 * @property integer $proficiency
 * @property integer $punctual
 * @property-read double $rating
 * @property string $message
 * @property string $createdAt
 * @property string $updatedAt
 * @property integer $isAdmin
 *
 * @property Account $tutor
 */
class Review extends \yii\db\ActiveRecord
{
    const SCENARIO_BACKEND_ADD = 'backend-add';

    const ACTIVE = 1;
    const BANNED = 2;
    const NEW = 3;

    public $statusTextList = [1 => 'Active', 2 => 'Banned', 3 => 'New'];

    const ARTICULATION = 'articulation';
    const PROFICIENCY = 'proficiency';
    const PUNCTUAL = 'punctual';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%review}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
            [['message'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute);
            }
            ],
            //['name', 'required', 'on' => self::SCENARIO_BACKEND_ADD],
            [['status'], 'default', 'value' => 1, 'on' => self::SCENARIO_BACKEND_ADD],
            [['status'], 'default', 'value' => 0, 'on' => self::SCENARIO_DEFAULT],
            [['message', 'name'], 'filter', 'filter' => 'trim'],
            [['accountId', 'articulation', 'proficiency', 'punctual', 'hours', 'accounts'], 'integer', 'min' => 0],
            [['articulation', 'proficiency', 'punctual'], 'integer', 'max' => 5],
            [['articulation', 'proficiency', 'punctual', 'message'], 'required', 'on' => self::SCENARIO_DEFAULT],
            [['message'], 'string', 'max' => 1000],
            ['createdAt', 'date', 'type' => 'datetime', 'format' => 'php:m/d/Y H:i', 'timestampAttribute' => 'createdAt', 'timestampAttributeFormat' => 'php:Y-m-d H:i:s'],
            [['name'], 'string', 'max' => 255],
            [['articulation', 'proficiency', 'punctual'], 'default', 'value' => 0, 'on' => self::SCENARIO_BACKEND_ADD],
        ];
    }

    public function getAverage()
    {
        return Yii::$app->formatter->asDecimal(round(($this->articulation + $this->proficiency + $this->punctual) / 3, 1), 1);
    }

    public function getStatusText()
    {
        return $this->statusTextList[$this->status];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_BACKEND_ADD] = ['articulation', 'proficiency', 'punctual', 'hours', 'accounts', 'name', 'message', 'status', 'createdAt'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->scenario == self::SCENARIO_BACKEND_ADD) {
            $this->detachBehavior('timestamp');
            if ($this->message == '' || $this->name == '') {
                $this->isAdmin = 1;
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        EventHelper::changeReviewEvent(
            $this,
            $insert,
            $changedAttributes
        );

        if (
            isset($changedAttributes['status'])
            && $changedAttributes['status'] == self::NEW
            && $this->status == self::ACTIVE
        ) {
            $moduleAccount = Yii::$app->getModuleAccount();
            $moduleAccount->eventLeavedReview($this);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        parent::afterDelete();

        EventHelper::deletedReviewEvent($this);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLesson()
    {
        return $this->hasOne(Lesson::class, ['id' => 'lessonId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutor()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'accountId']);
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
            'name' => 'Student name',
            'lessonId' => 'Lesson',
            'articulation' => 'Articulation',
            'proficiency' => 'Proficiency',
            'punctual' => 'Punctuality',
            'message' => 'Review',
            'hours' => 'Tutoring hours',
            'accounts' => 'Number of students',
            'createdAt' => 'Date',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function getAllReviewsQuery()
    {
        return static::find()
            ->joinWith([
                'tutor' => function ($query) {
                    $query->andWhere(['not', [Account::tableName() . '.status' => AccountStatusHelper::STATUS_DELETED]]);
                },
            ])
            ->andWhere([Review::tableName() . '.status' => self::ACTIVE])
            ->andWhere([
                'not',
                ['message' => '']
            ]);
    }
}
