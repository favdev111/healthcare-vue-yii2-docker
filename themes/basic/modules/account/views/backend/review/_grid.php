<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $searchModel modules\account\models\backend\ReviewSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<?php
echo GridView::widget([
    'id' => 'grid-search-review',
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
            'attribute' => 'accountEmail',
            'value' => function ($model) {
                return Html::a($model->account->email, ['/account/tutor/view', 'id' => $model->accountId], ['data-pjax' => 0]);
            },
            'format' => 'raw',
        ],
        [
            'label' => 'Name',
            'value' => function ($model) {
                return $model->lesson ? $model->lesson->student->profile->showName : $model->name;
            },
            'filter' => false,
        ],
        [
            'attribute' => 'message',
            'value' => function ($model) {
                return  \yii\helpers\StringHelper::truncateWords($model->message, 10);
            },
            'format' => 'text',
        ],
        [
            'attribute' => 'articulation',
            'format' => 'integer',
        ],
        [
            'attribute' => 'proficiency',
            'format' => 'integer',
        ],
        [
            'attribute' => 'punctual',
            'format' => 'integer',
        ],
        [
            'attribute' => 'hours',
            'format' => 'integer',
        ],
        [
            'attribute' => 'accounts',
            'format' => 'integer',
        ],
        [
            'attribute' => 'status',
            'value' => 'statusText',
            'filter' => $searchModel->statusTextList,
        ],
        'createdAt:date',

        [
            'class' => \backend\components\rbac\column\ActionColumn::class,
            'template' => '{block} {unblock} {view} {update} {lesson} {student} {delete}',
            'visibleButtons' => [
                'student' => function ($model) {
                    return $model->lesson != null;
                },
                'lesson' => function ($model) {
                    return $model->lessonId != null;
                },
                'block' => function ($model) {
                    return $model->status != \modules\account\models\backend\Review::BANNED;
                },
                'unblock' => function ($model) {
                    return $model->status != \modules\account\models\backend\Review::ACTIVE;
                },
            ],
            'buttons' => [
                'lesson' => function ($url, $model, $key) {
                    return Html::a('<i class="fa fa-graduation-cap" aria-hidden="true"></i>', ['/account/lesson/view', 'id' => $model->lessonId], [
                        'title' => Yii::t('yii', 'View Lesson #' . $model->lessonId),
                        'aria-label' => Yii::t('yii', 'View Lesson #' . $model->lessonId),
                        'data-pjax' => '0',
                    ]);
                },
                'student' => function ($url, $model, $key) {
                    return Html::a('<i class="fa fa-user" aria-hidden="true"></i>', ['/account/patient/view', 'id' => $model->lesson->studentId], [
                        'title' => Yii::t('yii', $model->lesson->student->email),
                        'aria-label' => Yii::t('yii', $model->lesson->student->email),
                        'data-pjax' => '0',
                    ]);
                },
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
            'contentOptions' => [
                'style' => 'min-width: 120px;'
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
