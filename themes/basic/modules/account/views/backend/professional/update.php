<?php

/* @var $this yii\web\View */
/* @var $model \backend\models\Account */

$this->title = 'Update Health professional: ' . $model->getDisplayName();
$this->params['breadcrumbs'][] = ['label' => 'Health professional', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->getDisplayName(), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

?>
<div class="faq-post-update">

    <?= $this->render('_form', [
        'model' => $model
    ]) ?>

</div>
