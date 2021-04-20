<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\HealthGoal */

$this->title = 'Create Health Goal';
$this->params['breadcrumbs'][] = ['label' => 'Health Goals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="health-goal-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
