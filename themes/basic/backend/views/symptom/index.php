<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\symptom\SymptomSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Symptoms';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="symptom-index">
    <p>
        <?= Html::a('Create Symptom', ['create'], ['class' => 'btn btn-success']) ?>
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
            ['class' => 'backend\components\rbac\column\ActionColumn']
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
