<?php

namespace modules\account\models;

use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use yii\data\ActiveDataProvider;
use yii\elasticsearch\ActiveRecord;

/**
 * Class TutorSearch
 * @package modules\account\models
 *
 * @property string $subjects
 */
class SubjectSearch extends ActiveRecord
{
    public $_id;

    public $subjects;

    public static function index()
    {
        return 'subject';
    }

    public static function type()
    {
        return '_doc';
    }

    public function attributes()
    {
        return [
            'name',
            'keywords'
        ];
    }

    public function rules()
    {
        return [
            [[
                'name',
                'keywords',
                'subjects'
            ], 'safe'
            ],
        ];
    }

    public function search()
    {
        $subject = mb_strtolower($this->subjects);
        $params = [
            'query' => [
                'bool' => [
                    'must' => [],
                    'filter' => [],
                    'must_not' => [],
                    'should' => []
                ],
            ],
        ];
        if (!empty($this->subjects)) {
            array_push($params['query']['bool']['should'], [
                'regexp' => [
                    'name' => '.*' . $subject . '.*',
                ],

            ]);
            array_push($params['query']['bool']['should'], [
                'regexp' => [
                    'keywords' => '.*' . $subject . '.*',
                ],
            ]);
        }
        $query = self::find()->query($params);
        $limit = $query->count();

        $query->limit($limit)->asArray();

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        return array_column($provider->getModels(), '_id');
    }

    public function searchWeight($search, $withCat = true, $excludeCategorySubjects = true)
    {
        if (empty($search)) {
            return [];
        }
        $subject = mb_strtolower($search);
        $params['multi_match'] = [
            'query' => $subject,
            'fields' => ['name^2', 'keywords'],
            'operator' => 'or',
        ];

        $query = self::find()->query($params);
        $limit = $query->count();

        $query->limit($limit);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
        $res = $provider->getModels();
        $result = [];
        if ($withCat) {
            $categories = Category::find()->andWhere(['like', 'name', $subject . '%', false])->all();
            foreach ($categories as $category) {
                $categoryName = ucfirst($category['name']);

                // Exclude category if exists subject with same name
                if ($excludeCategorySubjects) {
                    $continue = false;
                    foreach ($res as $subjectItem) {
                        if ($subjectItem->name === $categoryName) {
                            $continue = true;
                            break;
                        }
                    }
                    if ($continue) {
                        continue;
                    }
                }

                $result[] = [
                    'text' => $categoryName,
                    'id' => SubjectOrCategory::generateCategoryId($category),
                ];
            }
        }
        foreach ($res as $subject) {
            $result[] = [
                'text' => $subject['name'],
                'id' => (int)$subject->getPrimaryKey(),
            ];
        }

        return $result;
    }

    public function getSubject()
    {
        return Subject::findOne(parent::getPrimaryKey());
    }

    public function formName()
    {
        return '';
    }

    public static function createIndex($subject)
    {
        $model = new SubjectSearch();
        $model->primaryKey = $subject->id;
        $model->name = $subject->name;
        $keywords = str_replace("\t", ' ', $subject->keywords);
        $model->keywords = implode(' ', (array_filter(explode(' ', trim($keywords)))));
        return $model->save();
    }
}
