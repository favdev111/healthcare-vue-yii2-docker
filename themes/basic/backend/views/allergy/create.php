<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\allergy\Allergy */

$this->title = 'Create Allergy';
$this->params['breadcrumbs'][] = ['label' => 'Allergies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="allergy-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
