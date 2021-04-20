<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $searchModel modules\account\models\search\JobSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<?php
echo GridView::widget([
    'id' => 'grid-search-tutor',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'attribute' => 'id',
            'filterOptions' => [
                'style' => 'max-width: 50px;'
            ],
        ],
        [
            'attribute' => 'email',
            'value' => function ($model) {
                return Html::a($model->account->email, ['/account/patient/view', 'id' => $model->accountId]);
            },
            'format' => 'raw',
            'visible' => $searchModel->accountId == null,
        ],
        'zipCode',
        [
            'attribute' => 'studentGrade',
            'value' => 'studentGradeText',
            'filter' => $searchModel->grade,
        ],
        [
            'attribute' => 'lessonOccur',
            'value' => 'lessonOccurText',
            'filter' => $searchModel->lesson,
        ],
        [
            'attribute' => 'gender',
            'value' => 'genderText',
            'filter' => $searchModel->genderArray,
        ],
        [
            'attribute' => 'startLesson',
            'value' => 'startLessonText',
            'filter' => $searchModel->stLesson,
        ],
        [
            'attribute' => 'hourlyRate',
            'value' => function ($model) {
                return $model->hourlyRateFrom . ' - ' . $model->hourlyRateTo;
            }
        ],
        [
            'class' => 'kartik\grid\BooleanColumn',
            'attribute' => 'close',
        ],
        !empty($autogenerate)? [
            'attribute' => 'status',
            'value' => 'statusText',
            'filter' => $searchModel->statusTextArray,
        ]: 'closeDate:date',
        'createdAt:date',

        [
            'class' => \backend\components\rbac\column\ActionColumn::class,
            'visibleButtons' => [
                'block' => function ($model) {
                    return !$model->block;
                },
                'unblock' => function ($model) {
                    return $model->block;
                },
            ],
            'template' => !empty($autogenerate)? '{view} {update} {block} {unblock}': '{view} {block} {unblock}',
            'buttons' => [
                'block' => function ($url, $model, $key) {
                    return Html::a('<i class="fa fa-lock" aria-hidden="true"></i>', $url, [
                        'title' => Yii::t('yii', 'Block'),
                        'aria-label' => Yii::t('yii', 'Block'),
                        'data-pjax' => '0',
                    ]);
                },
                'unblock' => function ($url, $model, $key) {
                    return Html::a('<i class="fa fa-unlock-alt" aria-hidden="true"></i>', $url, [
                        'title' => Yii::t('yii', 'Unblock'),
                        'aria-label' => Yii::t('yii', 'Unblock'),
                        'data-pjax' => '0',
                    ]);
                },
            ],
            'urlCreator' => function ($action, $model, $key, $index, $actionColumn) {
                return \common\helpers\Url::to(['/account/job/' . $action, 'id' => $key]);
            },
            'contentOptions' => [
                'style' => 'min-width: 60px;'
            ],
        ],
    ],
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'pjax' => true, // pjax is set to always true for this demo
    // set your toolbar
    'toolbar' => [
        '{export}',
        '{toggleData}',
    ],
    // set export properties
    'export' => [
        'fontAwesome' => true
    ],
    // parameters from the demo form
    'condensed' => true,
    'responsive' => true,
    'hover' => true,
    'showPageSummary' => false,
//        'panel' => [
//            'type' => GridView::TYPE_PRIMARY,
//            'heading' => $heading,
//        ],
    'persistResize' => false,
]); ?>
