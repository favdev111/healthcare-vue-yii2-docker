<?php

namespace modules\account\models\search;

use modules\account\models\AutomatchSubject;
use modules\account\models\Category;
use modules\account\models\Subject;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class AutomatchSubjectSearch extends AutomatchSubject
{
    public $subjectName;
    public $categoryName;

    public function attributes()
    {
        return array_merge(parent::attributes(), []);
    }

    public function rules()
    {
        return [
           [
               ['subjectName', 'categoryName'], 'string'
           ]
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $q = static::find()
            ->select(
                [
                    static::tableName() . '.*',
                    new Expression(Subject::tableName() . '.name as subjectName'),
                ]
            )
            ->joinWith('subject')
            ->orderBy(new Expression(' subjectName ASC'));

        $dataProvider = new ActiveDataProvider([
            'query' => $q,
        ]);

        $this->load($params, '');

        if (!empty($this->subjectName)) {
            $q->andWhere(['like', Subject::tableName() . ".name", $this->subjectName]);
        }

        $q->distinct();

        return $dataProvider;
    }
}
