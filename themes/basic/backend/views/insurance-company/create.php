<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model \backend\models\InsuranceCompany */

$this->title = 'Create Company';
$this->params['breadcrumbs'][] = ['label' => 'Insurance Companies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
