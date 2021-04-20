<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\HealthGoal */

$this->title = 'Update Health Goal: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Health Goals', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="health-goal-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
