<?php

use modules\account\models\backend\Account;
use modules\payment\models\CardInfo;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel modules\account\models\backend\AccountSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Patient Accounts';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="account-index">
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
            'email:email',
            [
                'attribute' => 'profile.firstName',
                'filter' => Html::activeTextInput($searchModel, 'firstName', ['class'=>'form-control']),
            ],
            [
                'attribute' => 'profile.lastName',
                'filter' => Html::activeTextInput($searchModel, 'lastName', ['class'=>'form-control']),
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->statusName;
                },
                'filter' => Html::activeDropDownList($searchModel, 'status', \common\helpers\AccountStatusHelper::getAllStatuses(), ['class' => 'form-control', 'prompt' => '']),
                'filterOptions' => [
                    'style' => 'max-width: 100px;'
                ],
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'header' => 'Payment status',
                'content' => function ($model, $key, $index, $column) {
                    if (!empty(CardInfo::find()->joinWith('paymentCustomer')->andWhere(['accountId' => $model->id])->andWhere(['active' => 1])->one())) {
                        return '<i class="fa fa-credit-card" aria-hidden="true" style="color:#008000"></i>';
                    }
                    return '<i class="fa fa-credit-card" aria-hidden="true" style="color:#ff0000"></i>';
                },
            ],
             'createdAt:date',
            [
                'class' => \backend\components\rbac\column\ActionColumn::class,
                'template' => '{view} {update}'
            ],
        ],
        'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'showPageSummary' => false,
        'persistResize' => false,
    ]); ?>
</div>
