<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \backend\models\InsuranceCompany */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
        'modelClass' => 'Insurance company ',
    ]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Accounts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="account-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
