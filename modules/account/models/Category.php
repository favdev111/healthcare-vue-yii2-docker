<?php

namespace modules\account\models;

use Yii;
use yii\db\Expression;
use common\components\HtmlPurifier;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Subject[] $subjects
 * @property string $ucfirstName
 * @property string $slug
 */
class Category extends \yii\db\ActiveRecord
{
    use UpdateAllSlugsTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
        'slug' => $this->getSlugBehavior(),
        ]);
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
            [['name'], 'required'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubjects()
    {
        return $this->hasMany(Subject::class, ['id' => 'subjectId'])->viaTable('{{%category_subject}}', ['categoryId' => 'id']);
    }

    public static function findPopularCategories($limit = 6)
    {
        // Caching query for one day
        $result = self::getDb()->cache(function ($db) use ($limit) {
            return self::find()
                ->joinWith('subjects.accountSubjects', false)
                ->select(
                    [
                        self::tableName() . '.id',
                        self::tableName() . '.name',
                        self::tableName() . '.slug'
                    ]
                )
                ->addSelect(new Expression('COUNT(' . AccountSubject::tableName() . '.id) as tutorsCount'))
                ->groupBy(self::tableName() . '.id')
                ->orderBy(
                    [
                        'tutorsCount' => SORT_DESC,
                        'sort' => SORT_ASC,
                    ]
                )
                ->indexBy('id')
                ->limit($limit)
                ->all();
        }, 24 * 60 * 60);
        return $result;
    }

    public function getPopularSubjects($limit = 6)
    {
        // Caching query for one day
        $result = self::getDb()->cache(function ($db) use ($limit) {
            return $this->getSubjects()
                ->joinWith('accountSubjects', false)
                ->select(
                    [
                        Subject::tableName() . '.id',
                        Subject::tableName() . '.name',
                        Subject::tableName() . '.slug'
                    ]
                )
                ->addSelect(new Expression('COUNT(' . AccountSubject::tableName() . '.id) as tutorsCount'))
                ->groupBy(Subject::tableName() . '.id')
                ->orderBy(
                    [
                        'tutorsCount' => SORT_DESC,
                    ]
                )
                ->indexBy('id')
                ->limit($limit)
                ->all();
        }, 24 * 60 * 60);
        return $result;
    }

    /**
     * @return string
     */
    public function getUcfirstName()
    {
        return ucfirst(strtolower($this->name));
    }

    /**
     * @param $name
     * @return int|mixed|null
     */
    public static function findByName($name)
    {
        $category = self::find()->andWhere(['name' => $name])->limit(1)->one();
        return $category instanceof static ? $category : null;
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
}
