<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\AutoimmuneDisease */

$this->title = 'Create Autoimmune Disease';
$this->params['breadcrumbs'][] = ['label' => 'Autoimmune Diseases', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="autoimmune-disease-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
