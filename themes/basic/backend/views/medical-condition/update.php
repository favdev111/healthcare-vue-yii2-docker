<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\MedicalCondition */

$this->title = 'Update Medical Condition: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Medical Conditions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="medical-condition-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
