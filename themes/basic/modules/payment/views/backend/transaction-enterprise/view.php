<?php

use common\helpers\Url;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use modules\payment\models\Transaction;
use yii\helpers\Html;
use backend\components\rbac\Rbac;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model Transaction */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Transactions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$paidFor = $model->getPaidForString();
$attributes = [
    [
        'attribute' => 'id',
        'filterOptions' => [
            'style' => 'max-width: 50px;'
        ],
    ],
    [
        'label' => 'Paid For',
        'value' => $paidFor,
        'format' => 'raw',
    ],
    'transactionExternalId',
    [
        'attribute' => 'studentId',
        'value' => (!$model->student) ? null : Html::a($model->student->email, ['/account/patient/view', 'id' => $model->studentId], ['data-pjax' => 0]),
        'format' => 'raw',
        'label' => 'Student',
    ],
    [
        'attribute' => 'tutorId',
        'value' => $model->isClientBalance() || $model->isGroupChargeTransaction() ?
            null
            : Html::a($model->tutor->email, ['/account/tutor/view', 'id' => $model->tutorId],  ['data-pjax' => 0]),
        'format' => 'raw',
        'label' => 'Tutor',
    ],
    [
        'attribute' => 'processDate',
        'format' => 'datetime',
    ],
    [
        'attribute' => 'status',
        'value' => $model->statusText,
    ],
    [
        'attribute' => 'amount',
        'visible' => Yii::$app->user->can(Rbac::PERMISSION_VIEW_TRANSACTIONS),
        'format' => 'currency',
    ],
    [
        'attribute' => 'fee',
        'visible' => Yii::$app->user->can(Rbac::PERMISSION_VIEW_TRANSACTIONS),
        'format' => 'currency',
    ],
    [
        'attribute' => 'type',
        'value' => $model->typeText,
    ],
    'createdAt:date',
    [
        'label' => 'Actions',
        'format' => 'raw',
        'visible' => $model->isWaitingForApprove,
        'value' => function ($model) {

            $approve = Html::a(
                'Approve',
                ['/payment/transaction/approve', 'id' => $model->id],
                [
                    'class' => 'fa fa-check btn btn-success',
                    'data-pjax' => 0,
                    'data'  => [
                        'confirm' => Yii::t(
                            'app',
                            'Are you sure you want to approve this item?'
                        ),
                        'method'  => 'post',
                    ],
                ]
            );

            $reject = Html::a(
                'Reject',
                ['/payment/transaction/reject', 'id' => $model->id],
                [
                    'class' => 'fa fa-remove btn btn-danger',
                    'data-pjax' => 0,
                    'data'  => [
                        'confirm' => Yii::t(
                            'app',
                            'Are you sure you want to reject this item?'
                        ),
                        'method'  => 'post',
                    ],
                ]
            );

            return $approve . " " . $reject;
        },

    ]
];
\common\components\View::addBackendTransactionError($attributes, $model);
?>
<div class="lesson-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => $attributes,
    ]) ?>

</div>
<?php
/*partial refund fo client-balance transactions*/
if ($model->isClientBalance() && $model->status === Transaction::STATUS_SUCCESS):?>
<section class="content">
        <?php
        $form = ActiveForm::begin([
            'action'=> Url::to(['/payment/transaction-enterprise/view', 'id' => $model->id]),
            'method' => 'post'
        ])?>
        <?=$form->field($refundData,'transactionId')->hiddenInput()->label('')?>
        <b>Partial refund</b><br>
        <?=$form->field($refundData,'amount')->input('text')?>
        <?=Html::submitButton('Refund')?>
        <?php ActiveForm::end();
        \common\helpers\FormError::show($refundData);
        ?>
</section>
<?php endif;?>

<?php
if (!empty($relatedTransfersProvider)):?>
<h4>Related transfers</h4>
<?php
echo GridView::widget([
'id' => 'grid-parfial-refunds',
'dataProvider' => $relatedTransfersProvider,
'columns' => [
    'id',
    'processDate',
    'transactionExternalId',
    'amount'
],
'showFooter' => false,
]);
endif;?>

<h4>Refunds</h4>
<?php
echo GridView::widget([
    'id' => 'grid-parfial-refunds',
    'dataProvider' => $providerPartialRefunds,
    'filterModel' => $searchPartialRefunds,
    'columns' => [
        'id',
        'processDate',
        'transactionExternalId',
        [
            'attribute' => 'amount',
            'footer' => $model->getTotalPartialRefundSum(),
        ],
    ],
    'showFooter' => true,
]);

