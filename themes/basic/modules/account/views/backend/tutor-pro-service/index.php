<?php

use yii\helpers\Html;
use common\models\TutorPro;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TutorProSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tutor Pro Service';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tutor-pro-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Tutor Pro', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'phone',
            'email:email',
            'message:ntext',
            [
                'label' => 'Status',
                'value' => function ($model) {
                    return ucfirst(TutorPro::VIEWED_STATUSES[$model->viewed]);
                },
            ],
            // 'createdAt',
            [
                'label' => 'Created At',
                'filter' => false,
                'value' => function ($model) {
                    return \Yii::$app->formatter->asDatetime($model->createdAt);
                },
                'attribute' => 'createdAt',
            ],

            ['class' => \backend\components\rbac\column\ActionColumn::class,],
        ],
    ]); ?>
</div>
