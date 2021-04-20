<?php

use common\models\Lead;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Lead */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Leads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="lead-view">
    <p>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'Link to SalesForce',
                'format' => 'html',
                'visible' => $model->salesForceLinkAttribute,
                'value' => function (Lead $lead) {
                    return Html::a('Go to Sales Force', $lead->salesForceLinkAttribute);
                },
            ],
            'name',
            'email:email',
            'phoneNumber',
            'createdAt:dateTime:Date create (PDT)',
            [
                'format' => 'raw',
                'label' => 'Symptoms',
                'value' => function (Lead $lead) {
                    $relations = ArrayHelper::getValue($lead->data, 'relations', []);
                    $symptoms = ArrayHelper::getColumn($relations, function ($value) {
                        return Html::button($value['name'], ['class' => 'btn btn-outline-primary btn-sm']);
                    });
                    return implode(' ', $symptoms);
                },
            ],
            'advertisingChannel',
            'source:ntext',
            [
                'attribute' => 'status',
                'value' => function (Lead $lead) {
                    return ArrayHelper::getValue(Lead::QUEUE_STATUS_LABELS, $lead->status);
                }
            ],
            'clickId',
            'externalId',
            'ip',
        ],
    ]) ?>

</div>
