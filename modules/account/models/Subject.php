<?php

namespace modules\account\models;

use modules\account\models\query\SubjectQuery;
use Yii;

/**
 * This is the model class for table "{{%subject}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $keywords
 * @property string $slug
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property AccountSubject[] $accountSubjects
 * @property Account[] $accounts
 * @property Category $category
 *
 * @property-read Category $categories
 */
class Subject extends \yii\db\ActiveRecord
{
    use UpdateAllSlugsTrait;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['slug'] = $this->getSlugBehavior();

        return $behaviors;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @inheritdoc
     */
    public static function find()
    {
        return new SubjectQuery(static::class);
    }

    /**
    /* Get subjects id by name
     * @param $name
     * @return int|mixed|null
     */
    public static function getIdByName($name)
    {
        if (empty($name)) {
            return '';
        }
        $subject = Subject::find()->andWhere(['name' => $name])->limit(1)->one();
        return $subject instanceof static ? $subject->id : null;
    }

    /**
     * Get by slug
     * @param $slug
     * @return static|null
     */
    public static function findBySlug($slug)
    {
        return !empty($slug)
            ? static::find()->andWhere(['slug' => $slug])->limit(1)->one()
            : null;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getIndexedByIds()
    {
        return static::find()->indexBy('id')->all();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%subject}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['keywords'], 'string', 'max' => 65535],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'keywords' => 'Keywords',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountSubjects()
    {
        return $this->hasMany(AccountSubject::class, ['subjectId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccounts()
    {
        return $this->hasMany(Account::class, ['id' => 'accountId'])->viaTable('{{%account_subject}}', ['subjectId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'categoryId'])->viaTable('{{%category_subject}}', ['subjectId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'categoryId'])->viaTable('{{%category_subject}}', ['subjectId' => 'id']);
    }


    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id' => function () {
                return $this->id;
            },
            'text' => function () {
                return $this->name;
            },
        ];
    }

    public static function setCallUsButtonSubjects($subjectIds): void
    {
        Yii::$app->settings->set('lp', 'call-us-subjects', $subjectIds);
    }

    public static function getCallUsButtonSubjects(): array
    {
        return Yii::$app->settings->get('lp', 'call-us-subjects');
    }

    public static function displayCallUsButton(int $subjectId): bool
    {
        return in_array($subjectId, static::getCallUsButtonSubjects());
    }
}
