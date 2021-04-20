<?php

use modules\account\models\Account;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel modules\account\models\SearchDataSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Subject Feed';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="data-search-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            'whoIs',
            'search',
            'zipCode',
            'createdAt:datetime',
            // 'updatedAt',
            [
                'class' => \backend\components\rbac\column\ActionColumn::class,
                'template' => '{delete}'
            ],
        ],
    ]); ?>
</div>
