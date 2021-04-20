<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\Symptom */

$this->title = 'Update Symptom: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Symptoms', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="symptom-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
