<?php

namespace modules\account\models;

use modules\account\models\query\GradesQuery;
use Yii;

/**
 * This is the model class for table "grades".
 *
 * @property int $id
 * @property string $name
 * @property string $category
 * @property int $updateGroup
 *
 * @property GradeItem[] $gradeItems
 */
class Grade extends \yii\db\ActiveRecord
{
    const ADULT_GRADE_ID = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%grades}}';
    }

    /**
     * Get list of categories
     * @return array
     */
    public static function categories(): array
    {
        $data = Yii::$app->cache->get('grade_categories_list');
        if (empty($data)) {
            $data = static::find()->groupBy('category')->select('category')->asArray()->column();
            Yii::$app->cache->set('grade_categories_list', $data, 60 * 60 * 24);
        }
        return $data;
    }

    public static function byCategoriesList()
    {
        $data = Yii::$app->cache->get('grades_by_categories_list');
        if (empty($data)) {
            $data = [];
            $categories = static::categories();
            foreach ($categories as $name) {
                $data[$name] = static::find()->indexBy('id')->byCategory($name)->select('name')->asArray()->column();
            }
        }
        return $data;
    }

    /**
     * Get query for next grade in same update group
     * @return GradesQuery
     */
    public function next(): GradesQuery
    {
        return Grade::find()
            ->andWhere(['id' => ((int)$this->id + 1)])
            ->andWhere(['updateGroup' => $this->updateGroup])
            ->limit(1);
    }

    /**
     * Key -> id field from db
     * Value -> name field from db
     * @return array
     */
    public static function list(): array
    {
        $data = Yii::$app->cache->get('grades_list');
        if (empty($data)) {
            $data = static::find()->indexBy('id')->column('name');
            Yii::$app->cache->set('grades_list', $data, 60 * 60 * 24);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'category'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'category' => 'Category',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGradeItems()
    {
        return $this->hasMany(GradeItem::class, ['itemId' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \modules\account\models\query\GradesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\account\models\query\GradesQuery(get_called_class());
    }
}
