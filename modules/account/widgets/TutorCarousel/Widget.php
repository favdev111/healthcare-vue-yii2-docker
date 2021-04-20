<?php

namespace modules\account\widgets\TutorCarousel;

use common\models\Review;
use common\components\ZipCodeHelper;
use modules\account\models\Account;
use modules\account\models\Profile;
use modules\account\models\Role;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class Widget extends \yii\base\Widget
{
    public $template = 'view';
    public $pageSize = 6;

    public function run()
    {
        $query = Account::find()
            ->joinWith('profile')
            ->joinWith('review', false)
            ->select([Account::tableName() . '.*', 'COUNT(review.accountId) as count'])
            ->andWhere(['roleId' => Role::ROLE_SPECIALIST])
            ->andWhere(['account.status' => Account::STATUS_ACTIVE])
            ->andWhere(['searchHide' => false])
            ->andWhere(['hideProfile' => false])
            ->andWhere(['not', [Profile::tableName() . '.description' => '']])
            ->andWhere(['review.status' => Review::ACTIVE])
            ->andWhere(['isAdmin' => 0])
            ->groupBy('account.id')
            ->andHaving('count > 0');

        if ($zipCode = ZipCodeHelper::getZipCodeByUserId()) {
            $query->addSelect(new Expression('CASE WHEN ' . Profile::tableName() . '.zipCode = :zipCode THEN 1 ELSE 0 END as zipCodeFilter', [':zipCode' => $zipCode]))
                ->addOrderBy(['zipCodeFilter' => SORT_DESC]);
        }

        $query->addOrderBy([Account::tableName() . '.createdAt' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
            'sort' => false,
        ]);

        \Yii::$app->getDb()->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        }, 6 * 60 * 60);

        return $this->render(
            $this->template,
            [
                'dataProvider' => $dataProvider,
            ]
        );
    }
}
