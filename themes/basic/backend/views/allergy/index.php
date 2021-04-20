<?php

use backend\components\widgets\inputs\Select2Ajax;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\allergy\AllergySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Allergies';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="allergy-index">
    <p>
        <?= Html::a('Create Allergy', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                            'label' => 'Allergy category',
                            'filter' => Select2Ajax::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'allergyCategoryId',
                                    'route' => '/allergy-categories/ajax-search',
                                    'options' => [
                                            'multiple' => false,
                                    ],
                                    'pluginOptions' => [
                                            'minimumInputLength' => 1
                                    ]
                            ]),
                            'attribute' => 'allergyCategory.name'
                    ],
                    'name',
                    'createdAt',
                    ['class' => 'backend\components\rbac\column\ActionColumn']
            ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
