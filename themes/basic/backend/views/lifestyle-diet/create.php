<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\LifestyleDiet */

$this->title = 'Create Lifestyle Diet';
$this->params['breadcrumbs'][] = ['label' => 'Lifestyle Diets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lifestyle-diet-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
