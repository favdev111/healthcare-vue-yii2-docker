<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\AutoimmuneDisease */

$this->title = 'Update Autoimmune Disease: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Autoimmune Diseases', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="autoimmune-disease-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
