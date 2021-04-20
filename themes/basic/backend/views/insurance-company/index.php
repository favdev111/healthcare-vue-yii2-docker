<?php

use common\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $search \backend\models\search\InsuranceCompanySearch */
/* @var $provider yii\data\ActiveDataProvider */

$this->title = 'Insurance companies';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-index">
    <p>
        <?= Html::a('Create Company', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php  // echo $this->render('_search', ['model' => $search]);  // TODO uncomment after create _search view ?>
    Static pages
    <?= GridView::widget([
        'id' => 'grid-search-company',
        'dataProvider' => $provider,
        'columns' => [
            'name',
            'createdAt:date',
            'updatedAt:date',
            [
                'class' => \backend\components\rbac\column\ActionColumn::class,
                'contentOptions' => [
                    'style' => [
                        'min-width' => '80px',
                    ],
                ],
                'template' => '{update} {delete}',
            ],
        ],

        'containerOptions' => ['style' => 'overflow: auto'],
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'showPageSummary' => false,
        'persistResize' => false,
    ]);
    ?>

</div>
