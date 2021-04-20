<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\health\HealthTest */

$this->title = 'Create Health Test';
$this->params['breadcrumbs'][] = ['label' => 'Health Tests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="health-test-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
