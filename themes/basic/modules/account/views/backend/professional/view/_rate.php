<?php

/* @var $this yii\web\View */
/* @var $model modules\account\models\Account */

use yii\widgets\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        'rate.rate15:currency:15-minute rate',
        'rate.rate30:currency:30-minute rate',
        'rate.rate45:currency:45-minute rate',
        'rate.rate60:currency:60-minute rate',
    ]
]) ?>

