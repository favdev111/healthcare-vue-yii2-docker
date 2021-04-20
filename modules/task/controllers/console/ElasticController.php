<?php

namespace modules\task\controllers\console;

use modules\account\models\Subject;
use modules\account\models\SubjectSearch;
use UrbanIndo\Yii2\Queue\Worker\Controller;
use Yii;

class ElasticController extends Controller
{
    public function actionReCreateSubjectIndex()
    {
        SubjectSearch::deleteAll();

        $subjects = Subject::find();

        foreach ($subjects->each() as $subject) {
            if (!SubjectSearch::createIndex($subject)) {
                Yii::error('Failed to set index for subject #' . $subject->id, 'elastic');
            }
        }
    }
}
