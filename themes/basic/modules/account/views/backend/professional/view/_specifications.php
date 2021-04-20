<?php

/* @var $model Account */

use modules\account\models\Account;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\widgets\DetailView;

$contentCallback = static function ($query) {
    return static function (Account $account) use ($query) {
        $provider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'sort' => false,
            'pagination' => false
        ]);

        return GridView::widget([
            'dataProvider' => $provider,
            'tableOptions' => ['class' => 'table m-0'],
            'emptyText' => Yii::$app->formatter->nullDisplay,
            'showHeader' => false,
            'pager' => false,
            'summary' => false,
            'columns' => [
                'name:text:Name',
            ],
        ]);
    };
}

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        [
            'format' => 'html',
            'label' => 'Health tests',
            'contentOptions' => ['class' => 'p-0'],
            'value' => $contentCallback($model->getHealthTests())
        ],
        [
            'format' => 'html',
            'label' => 'Symptoms',
            'contentOptions' => ['class' => 'p-0'],
            'value' => $contentCallback($model->getSymptoms())
        ],
        [
            'format' => 'html',
            'label' => 'Conditions',
            'contentOptions' => ['class' => 'p-0'],
            'value' => $contentCallback($model->getMedicalConditions())
        ],
        [
            'format' => 'html',
            'label' => 'Autoimmune disease',
            'contentOptions' => ['class' => 'p-0'],
            'value' => $contentCallback($model->getAutoimmuneDiseases())
        ],
        [
            'format' => 'html',
            'label' => 'Health goals',
            'contentOptions' => ['class' => 'p-0'],
            'value' => $contentCallback($model->getHealthGoals())
        ],
    ]
]) ?>
