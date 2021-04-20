<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\HealthTest */

$this->title = 'Update Health Test: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Health Tests', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="health-test-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
