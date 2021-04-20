<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\healthGoal\HealthGoalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Health Goals';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="health-goal-index">
    <p>
        <?= Html::a('Create Health Goal', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'description:ntext',
            'slug',
            'createdAt',
            ['class' => 'backend\components\rbac\column\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
