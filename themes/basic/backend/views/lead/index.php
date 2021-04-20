<?php

use backend\components\rbac\column\ActionColumn;
use common\models\Lead;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\lead\LeadSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Leads';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lead-index">
    <p>
        <?= Html::a('Create Lead', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'name',
                    'email:email',
                    'phoneNumber',
                    [
                            'format' => 'raw',
                            'label' => 'Symptoms',
                            'value' => function (Lead $lead) {
                                $relations = ArrayHelper::getValue($lead->data, 'relations', []);
                                $symptoms = ArrayHelper::getColumn($relations, 'name');
                                return implode(', ', $symptoms);
                            },
                    ],
                    [
                            'format' => 'date',
                            'label' => 'Date Create (PDT)',
                            'filter' => DatePicker::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'createdAt',
                                    'type' => DatePicker::TYPE_INPUT,
                                    'pluginOptions' => [
                                            'ignoreReadonly' => true,
                                            'autoclose' => true,
                                            'removeButton' => true,
                                            'format' => 'mm-dd-yyyy'
                                    ]
                            ]),
                            'attribute' => 'createdAt',
                    ],

                    [
                            'class' => ActionColumn::class,
                            'template' => '{view}'
                    ],
            ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
