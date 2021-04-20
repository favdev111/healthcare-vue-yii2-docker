<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\LifestyleDiet */

$this->title = 'Update Lifestyle Diet: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Lifestyle Diets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="lifestyle-diet-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
