<?php

use yii\grid\ActionColumn;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\grid\GridView;

assert($this instanceof yii\web\View);
assert($searchModel instanceof modules\labels\models\search\LabelsCategorySearch);
assert($dataProvider instanceof yii\data\ActiveDataProvider);

$this->title = 'Labels Categories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="labels-category-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= Html::a('Create Labels Category', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],
            'name',
            'createdAt',
            'updatedAt',
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update}'
            ],
        ],
    ]); ?>
</div>
