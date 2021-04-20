<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\lifestyleDiet\LifestyleDietSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Lifestyle Diets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lifestyle-diet-index">
    <p>
        <?= Html::a('Create Lifestyle Diet', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'createdAt',
            ['class' => 'backend\components\rbac\column\ActionColumn']
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
