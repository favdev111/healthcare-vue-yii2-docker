<?php

namespace modules\account\widgets;

use common\models\Review;
use modules\account\models\Category;
use modules\account\models\query\AccountQuery;
use modules\account\models\Subject;
use Yii;
use yii\base\Widget;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class TutorsNearYou extends Widget
{
    public $template = 'tutorsNearYou';

    public function run()
    {
        $data = [
            ['id' => 12, 'isCategory' => true],
            ['id' => 339, 'isCategory' => false],
            ['id' => 423, 'isCategory' => false],
            ['id' => 405, 'isCategory' => false],
            ['id' => 14, 'isCategory' => true],
            ['id' => 452, 'isCategory' => false],
            ['id' => 420, 'isCategory' => false],
            ['id' => 418, 'isCategory' => false],
        ];

        $excludeAccountIds = [];
        foreach ($data as &$d) {
            $id = $d['id'];
            $subjects = [$id];
            if ($d['isCategory']) {
                $d['model'] = Category::findOne($id);
                $subjects = ArrayHelper::getColumn($d['model']->subjects, 'id');
            } else {
                $d['model'] = Subject::findOne($id);
            }

            $d['review'] = $this->getTopReviewBySubject($subjects, $excludeAccountIds);
            if ($d['review']) {
                $excludeAccountIds[] = $d['review']->accountId;
            } else {
                $d['review'] = $this->getTopReviewBySubject([], $excludeAccountIds);
                if ($d['review']) {
                    $excludeAccountIds[] = $d['review']->accountId;
                }
            }
        }

        return $this->render($this->template, [
            'data' => $data,
        ]);
    }

    protected function getTopReviewBySubject(array $subjects = [], array $excludeAccountIds = [])
    {
        return Review::find()
            ->select([
                'accountId',
                'rating',
                new Expression('COUNT(accountId) AS count'),
            ])
            ->joinWith(['lesson' => function ($query) use ($subjects) {
                if (!empty($subjects)) {
                    $query->andWhere(['lesson.subjectId' => $subjects]);
                }
            }
            ])
            ->joinWith([
                'tutor' => function (AccountQuery $query) {
                    $query
                        ->excludeHiddenProfiles()
                        ->excludeHiddenOnMarketplace()
                        ->byActiveStatus()
                        ->isSpecialist();
                },
            ])
            ->andWhere(['not in', Review::tableName() . '.accountId',  $excludeAccountIds])
            ->andWhere([Review::tableName() . '.isAdmin' => 0])
            ->andWhere([Review::tableName() . '.status' => Review::ACTIVE])
            ->andWhere([
                'not',
                ['message' => ''],
            ])
            ->groupBy('accountId, rating')
            ->andHaving('rating > 4')
            ->orderBy(['count' => SORT_DESC])
            ->limit(1)
            ->one();
    }
}
