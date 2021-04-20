<?php

namespace modules\account\models\SubjectOrCategory;

use modules\account\models\Category;
use modules\account\models\Subject;

trait SubjectOrCategoryTrait
{
    public function getSubjectOrCategory()
    {
        if ($this->isCategory) {
            $model = Category::find()->where(['id' => (int)$this->subjectId])->one();
        } else {
            $model = Subject::find()->where(['id' => (int)$this->subjectId])->one();
        }
        if ($model) {
            $model = new SubjectOrCategory($model);
            $model->name = $model->getName();
            $model->text = $model->name;
            $model->id = $model->getId();
        }
        return $model ?? null;
    }
}
