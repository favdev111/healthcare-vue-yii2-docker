<?php

use backend\components\rbac\Rbac;
use kartik\daterange\DateRangePicker;
use kartik\grid\GridView;
use modules\payment\models\Transaction;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $searchModel modules\payment\models\backend\TransactionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$query = $dataProvider->query;
?>
<?php
echo GridView::widget([
    'id' => 'grid-search-transaction',
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
            'value' => function ($model) {
                return $model->getPaidForString();
            },
            'format' => 'raw',
            'label' => 'Paid for',
        ],
        [
            'attribute' => 'studentEmail',
            'value' => function ($model) {
                if (!$model->student) {
                    return null;
                }
                return Html::a($model->student->email, ['/account/patient/view', 'id' => $model->studentId], ['data-pjax' => 0]);
            },
            'format' => 'raw',
            'label' => 'Student',
        ],
        [
            'attribute' => 'tutorEmail',
            'value' => function ($model) {
                /**
                 * @var $model Transaction
                 */
                if ($model->isClientBalance() || empty($model->tutor)) {
                    return null;
                }
                return Html::a($model->tutor->email, ['/account/tutor/view', 'id' => $model->tutorId],  ['data-pjax' => 0]);
            },
            'format' => 'raw',
            'label' => 'Tutor',
        ],
        [
            'attribute' => 'processDate',
            'format' => 'date',
            'filterType' => GridView::FILTER_DATE_RANGE,
            'filterWidgetOptions' => [
                'convertFormat' => true,
                'pluginOptions' => [
                    'locale' => [
                        'format' => 'Y-m-d'
                    ],
                ],
            ],
            'footer' => ($searchModel->processDateStart && $searchModel->processDateEnd)
                ? $searchModel->processDateStart
                    . ' - '
                    . $searchModel->processDateEnd
                : '',
        ],
        [
            'attribute' => 'status',
            'value' => 'statusText',
            'filter' => $searchModel->statuses,
        ],
        [
            'attribute' => 'amount',
            'format' => 'currency',
            'value' => function ($model) {
                if (Yii::$app->user->can(Rbac::PERMISSION_VIEW_TRANSACTIONS)) {
                    return $model->amount;
                }
            },
            'footer' => Yii::$app->user->can(Rbac::PERMISSION_VIEW_TRANSACTIONS) ? 'Total: ' . Yii::$app->formatter->asCurrency(
                (clone($query))
                    ->sum(
                        new \yii\db\Expression('CASE WHEN transaction.status = '
                            . Transaction::STATUS_SUCCESS
                            . ' and transaction.type = '
                            . Transaction::STRIPE_CHARGE
                            . ' THEN transaction.amount + transaction.fee ELSE 0 END')
                    )
            ) : '',
        ],
        [
            'attribute' => 'fee',
            'format' => 'currency',
            'value' => function ($model) {
                if (Yii::$app->user->can(Rbac::PERMISSION_VIEW_TRANSACTIONS)) {
                    return $model->fee;
                }
            },
            'footer' => Yii::$app->user->can(Rbac::PERMISSION_VIEW_TRANSACTIONS) ? 'Income: ' . Yii::$app->formatter->asCurrency(
                    (clone($query))
                        ->sum(
                            new \yii\db\Expression('CASE WHEN transaction.status = '
                                . Transaction::STATUS_SUCCESS
                                . ' and transaction.type = '
                                . Transaction::STRIPE_CHARGE
                                . ' THEN transaction.fee ELSE 0 END')
                        )
                ) : '',
        ],
        [
            'attribute' => 'type',
            'value' => 'typeText',
            'filter' => $searchModel->types,
        ],
        [
            'attribute' => 'createdAt',
            'format' => 'date',
            'filterType' => GridView::FILTER_DATE_RANGE,
            'filterWidgetOptions' => [
                'convertFormat' => true,
                'pluginOptions' => [
                    'locale' => [
                        'format' => 'Y-m-d'
                    ],
                ],
            ],
        ],
        [
            'class' => \backend\components\rbac\column\KartikActionColumn::className(),
            'template' => '{approve} {reject} {view} {refund}',
            'visibleButtons' => [
                'refund' => function ($model) {
                    /**
                     * @var Transaction $model
                     */
                    return $model->type == Transaction::STRIPE_CHARGE
                        && in_array($model->status, [Transaction::STATUS_SUCCESS])
                        && $model->isNeedShowRefundBlockOrButton();
                },
                'approve' => function ($model) {
                    return $model->isWaitingForApprove;
                },
                'reject' => function ($model) {
                    return $model->isWaitingForApprove;
                },
            ],
            'urlCreator' => function ($action, $model, $key, $index, $actionColumn) {
                return \common\helpers\Url::to(['/payment/transaction/' . $action, 'id' => $key]);
            },
            'buttons' => [
                'refund' => function ($url, $model, $key) use ($dataProvider) {
                    return Html::a('<i class="fa fa-money" aria-hidden="true"></i>', $url, [
                        'title' => Yii::t('yii', 'Refund'),
                        'aria-label' => Yii::t('yii', 'Refund'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to refund this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ]);
                },
                'approve' => function ($url, $model, $key) use ($dataProvider) {
                    return Html::a('<i class="fa fa-check" aria-hidden="true"></i>', $url, [
                        'title' => Yii::t('yii', 'Approve'),
                        'aria-label' => Yii::t('yii', 'Approve'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to approve this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ]);
                },
                'reject' => function ($url, $model, $key) use ($dataProvider) {
                    return Html::a('<i class="fa fa-remove" aria-hidden="true"></i>', $url, [
                        'title' => Yii::t('yii', 'Reject'),
                        'aria-label' => Yii::t('yii', 'Reject'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to reject this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ]);
                },
            ],
        ],

    ],
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'pjax' => true, // pjax is set to always true for this demo
    'pjaxSettings' => [
        'options' => [
            'id' => 'grid-search-transaction-pjax',
        ],
    ],
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
    'showFooter' => true,
//        'panel' => [
//            'type' => GridView::TYPE_PRIMARY,
//            'heading' => $heading,
//        ],
    'persistResize' => false,
]); ?>
