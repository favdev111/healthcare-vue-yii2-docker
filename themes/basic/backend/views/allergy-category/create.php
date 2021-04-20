<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\allergy\AllergyCategory */

$this->title = 'Create Allergy Category';
$this->params['breadcrumbs'][] = ['label' => 'Allergy Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="allergy-category-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
