<?php

/* @var $this yii\web\View */
/* @var $model Account */

use modules\account\models\Account;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\widgets\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        'profile.title:text:Title',
        'profile.description:text:Description',
        [
            'format' => 'html',
            'label' => 'Languages spoken',
            'contentOptions' => ['class' => 'p-0'],
            'value' => static function (Account $account) {
                $provider = Yii::createObject([
                    'class' => ActiveDataProvider::class,
                    'query' => $account->getLanguages(),
                    'sort' => false,
                    'pagination' => false
                ]);

                return GridView::widget([
                    'dataProvider' => $provider,
                    'pager' => false,
                    'summary' => false,
                    'tableOptions' => ['class' => 'table m-0'],
                    'emptyText' => Yii::$app->formatter->nullDisplay,
                    'showHeader' => false,
                    'columns' => [
                        'name'
                    ],
                ]);
            }
        ],
    ]
]) ?>

