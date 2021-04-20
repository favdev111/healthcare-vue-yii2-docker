<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel modules\account\models\search\ChangeLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Change Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="change-log-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'objectType',
                'value' => 'objectTypeName'
            ],
            [
                'attribute' => 'actionType',
                'value' => 'actionName'
            ],
            'description',
            'date',
            'objectId',
            'madeBy',
            //'oldValue',
            //'newValue',
            ['class' => 'backend\components\rbac\column\ActionColumn']
        ],
    ]); ?>


</div>
