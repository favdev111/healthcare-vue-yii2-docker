<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\autoimmuneDisease\AutoimmuneDiseaseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Autoimmune Diseases';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="autoimmune-disease-index">
    <p>
        <?= Html::a('Create Autoimmune Disease', ['create'], ['class' => 'btn btn-success']) ?>
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
