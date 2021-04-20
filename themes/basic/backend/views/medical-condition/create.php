<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\MedicalCondition */

$this->title = 'Create Medical Condition';
$this->params['breadcrumbs'][] = ['label' => 'Medical Conditions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="medical-condition-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
