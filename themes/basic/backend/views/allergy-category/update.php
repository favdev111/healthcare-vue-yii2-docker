<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\allergy\AllergyCategory */

$this->title = 'Update Allergy Category: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Allergy Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="allergy-category-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
