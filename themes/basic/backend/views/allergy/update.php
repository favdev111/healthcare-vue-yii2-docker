<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\allergy\Allergy */

$this->title = 'Update Allergy: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Allergies', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="allergy-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
