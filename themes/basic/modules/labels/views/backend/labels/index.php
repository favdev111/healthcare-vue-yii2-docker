<?php

use common\helpers\Url;
use modules\labels\models\LabelStatus;
use yii\grid\ActionColumn;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\grid\GridView;

assert($this instanceof yii\web\View);
assert($searchModel instanceof modules\labels\models\search\LabelsSearch);
assert($dataProvider instanceof yii\data\ActiveDataProvider);

$this->title = 'Labels';
$this->params['breadcrumbs'][] = $this->title;
$activeStatus = LabelStatus::createActiveStatus();

?>
<div class="labels-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Labels', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'name',
            [
                'attribute' => 'status',
                'value' => function ($model) use ($activeStatus) {
                    $label = $model->status === $activeStatus->getStatus() ?
                        [
                            'label' => 'Active',
                            'htmlClass' => 'success'
                        ] :
                        [
                            'htmlClass' => 'warning',
                            'label' => 'Draft'
                        ];
                    return '<div class="text-center">
                        <span class="label label-' . $label['htmlClass'] . '">' . $label['label'] . '</span></div>';
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'color',
                'value' => function ($model) {
                    $style = 'background-color:' . $model->color . ';width:100%;display:block;text-align:center';
                    return
                        '<span style="' . $style . '">' . $model->color . '</span>';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'categoryId',
                'value' => function ($model) {
                    $link = '<a href="' . Url::to(['/labels/categories/view/', 'id' => $model->categoryId]) . '">' .
                        $model->category->name .
                        '</a>';
                    return $link;
                },
                'format' => 'raw'
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update}'
            ],
        ],
    ]); ?>
</div>
