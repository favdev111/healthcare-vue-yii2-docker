<?php

namespace common\models;

use modules\account\models\Category;
use modules\account\models\Subject;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use modules\account\models\TutorSearch;
use yii\base\Model;

class CountSearchResult extends Model
{
    public $subjects;
    public $zipCode;
    public $distance;

    public function rules()
    {
        return [
            [['subjects', 'zipCode', 'distance'], 'required'],
            [['zipCode'], 'integer'],
            [['subjects'], 'exist', 'targetAttribute' => ['zipCode' => 'code'], 'targetClass' => Zipcode::className()],
        ];
    }

    public function getCount()
    {
        $search = new TutorSearch();
        $subjectOrCategory = SubjectOrCategory::findById($this->subjects);
        if ($subjectOrCategory->isCategory()) {
            /**
             * @var $categoryModel Category
             */
            $categoryModel = $subjectOrCategory->getModel();
            $this->subjects = $categoryModel->getSubjects()->select('id')->column();
        }
        $search->subjects = $this->subjects;
        $search->zipCode = $this->zipCode;
        $search->distance  = $this->distance;
        $provider = $search->search();
        return $provider->getTotalCount();
    }
}
