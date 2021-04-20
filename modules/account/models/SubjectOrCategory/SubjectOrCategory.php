<?php

namespace modules\account\models\SubjectOrCategory;

use modules\account\models\Category;
use modules\account\models\CategorySubject;
use modules\account\models\Subject;
use yii\base\Exception;

class SubjectOrCategory
{
    protected $model;
    protected $is_category;

    /**
     * SubjectOrCategory constructor.
     * @param $model
     * @throws Exception
     */
    public function __construct($model)
    {
        switch (get_class($model)) {
            case 'modules\account\models\Subject':
                $this->is_category = false;
                break;
            case 'modules\account\models\Category':
                $this->is_category = true;
                break;
            default:
                throw new Exception('Param must be an object of class Category or Subject. Object class is ' . get_class($model));
                break;
        }
        $this->model = $model;
    }

    /**
     * @param $id
     * @return SubjectOrCategory
     */
    public static function findById($id)
    {
        if (self::isIdOfCategory($id)) {
            $model = Category::find()->where(['id' => (int)$id])->one();
        } else {
            $model = Subject::find()->where(['id' => (int)$id])->one();
        }
        if (!empty($model)) {
            return new self($model);
        } else {
            return null;
        }
    }

    public static function generateCategoryId($categoryModel)
    {
        if (empty($categoryModel)) {
            return null;
        }
        return $categoryModel->id . "-" . $categoryModel->slug;
    }

    /**
     * returns true if id was id of Category or false if it was id of subject
     * @param $id
     * @return bool
     */
    public static function isIdOfCategory($id)
    {
        return !empty(explode('-', $id)[1]);
    }

    /**
     * @return bool
     */
    public function isCategory()
    {
        return $this->is_category;
    }

    /**
     * returns data array for Select2 widget
     * @return array
     */
    public function getSelect2Data()
    {
        return [$this->getId() => $this->model->name];
    }

    /**
     * @return string
     */
    public function getId()
    {
        if ($this->isCategory()) {
            return self::generateCategoryId($this->model);
        } else {
            return $this->model->id;
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->model->name;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * reutrns data for Selectize widget
     * @param int|null $defaultSubjectId
     * @param bool $onlySubjects
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public static function getSelectizeData($defaultSubjectId = null, bool $onlySubjects = false)
    {
        $array = [];
        $categories = [];

        $subjects = Subject::getDb()->cache(function ($db) {
            return Subject::find()->indexBy('id')->all();
        });

        if (!$onlySubjects) {
            $categories = Category::getDb()->cache(function ($db) {
                return Category::find()->indexBy('id')->all();
            });

            if (!empty($categories)) {
                foreach ($categories as &$category) {
                    $category->id = self::generateCategoryId($category);
                }
            }
        }

        if (!empty($defaultSubjectId)) {
            $id = self::generateCategoryId(Category::findOne($defaultSubjectId));
        }

        $array['value'] = $id ?? null;
        $array['items'] = array_column(array_merge($categories, $subjects), 'name', 'id') + ['' => 'All Subjects'];
        return $array;
    }

    /**
     * Converts mixed array with subject and categories ids to array which contain only subjects, and subjects from selected categories
     * @param $subjectOrCategoryIds
     * @return array
     */
    public static function convertToSubjectIds($subjectOrCategoryIds)
    {
        $subjectIds = array_filter($subjectOrCategoryIds, function ($subjectOrCategoryId) {
            return !self::isIdOfCategory($subjectOrCategoryId);
        });
        $categoryIds = array_filter($subjectOrCategoryIds, function ($subjectOrCategoryId) {
            return self::isIdOfCategory($subjectOrCategoryId);
        });
        if (!$categoryIds) {
            return $subjectIds;
        }

        $categoryIds = array_map(function ($categoryId) {
            return (int)$categoryId;
        }, $categoryIds);
        $allCategoriesSubjectIds = CategorySubject::find()
            ->andWhere(['categoryId' => $categoryIds])
            ->select('subjectId')
            ->groupBy('subjectId')
            ->column();
        return array_merge($subjectIds, $allCategoriesSubjectIds);
    }
}
