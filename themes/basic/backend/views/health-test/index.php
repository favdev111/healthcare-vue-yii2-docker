<?php

use backend\components\rbac\column\ActionColumn;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\healthTest\HealthTestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Health Tests';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="health-test-index">
    <p>
        <?= Html::a('Create Health Test', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'createdAt',
            ['class' => 'backend\components\rbac\column\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
