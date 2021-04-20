<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\Symptom */

$this->title = 'Create Symptom';
$this->params['breadcrumbs'][] = ['label' => 'Symptoms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="symptom-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
