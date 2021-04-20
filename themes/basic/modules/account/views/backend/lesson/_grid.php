<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $searchModel modules\account\models\backend\LessonSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

/**
 * @var Module $paymentModule
 */
$paymentModule = Yii::$app->getModule('payment');
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
        ],[
            'attribute' => 'studentName',
            'value' => function ($model) {
                return Html::a($model->student->profile->fullName(), ['/account/patient/view', 'id' => $model->studentId], ['data-pjax' => 0]);
            },
            'format' => 'raw',
            'label' => 'Student Name',
        ],
        [
            'attribute' => 'tutorName',
            'value' => function ($model) {
                return Html::a($model->tutor->profile->fullName(), ['/account/tutor/view', 'id' => $model->tutorId],  ['data-pjax' => 0]);
            },
            'format' => 'raw',
            'label' => 'Tutor Name',
        ],
        [
            'attribute' => 'fromDate',
            'format' => 'datetime',
            'filterType' => GridView::FILTER_DATE,
            'filterWidgetOptions' => [
                'removeButton' => false,
                'pluginOptions' => [
                    'format' => 'yyyy-mm-dd',
                ],
            ],
        ],
        [
            'attribute' => 'toDate',
            'format' => 'datetime',
            'filterType' => GridView::FILTER_DATE,
            'filterWidgetOptions' => [
                'removeButton' => false,
                'pluginOptions' => [
                    'format' => 'yyyy-mm-dd',
                ],
            ],
        ],
        [
            'attribute' => 'subjectName',
            'value' => 'subject.name',
            'label' => 'Subject',
        ],
        [
            'attribute' => 'hourlyRate',
            'format' => 'currency',
        ],
        [
            'label' => 'Total lesson amount',
            'value' => function($model) {
                return Yii::$app->formatter->asDecimal($model->amount + $model->fee, 2);
            }
        ],
        [
            'label' => 'Total lesson pay to tutor',
            'value' => function($model) {
                return Yii::$app->formatter->asDecimal($model->amount, 2);
            }
        ],
        [
            'label' => "Lesson Duration",
            'value' => function ($model) {
                return $model->getDuration();
            },
        ],

        [
            'attribute' => 'createdAt',
            'format' => 'date',
            'filterType' => GridView::FILTER_DATE,
            'filterWidgetOptions' => [
                'removeButton' => false,
                'pluginOptions' => [
                    'format' => 'yyyy-mm-dd',
                ],
            ],
        ],


        [
            'class' => \backend\components\rbac\column\ActionColumn::class,
            'visibleButtons' => [
                'update' => true,
                'delete' => false,
            ]
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
