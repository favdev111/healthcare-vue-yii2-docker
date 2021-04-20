<?php

/* @var $model Account */

use modules\account\models\Account;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;

$provider = Yii::createObject([
    'class' => ActiveDataProvider::class,
    'query' => $model->getCardInfo(),
    'sort' => false,
    'pagination' => false
]);

?>

<?= GridView::widget([
    'dataProvider' => $provider,
    'tableOptions' => ['class' => 'table m-0'],
    'emptyText' => 'No payment cards found.',
    'pager' => false,
    'summary' => false,
    'columns' => [
        'cardNumber:text',
        'active:boolean',
        'holderName',
        'createdAt:dateTime',
    ],
]) ?>
