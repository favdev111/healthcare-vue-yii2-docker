<?php

/* @var $this yii\web\View */
/* @var $model \modules\account\models\forms\patient\PatientUpdateForm */

$this->title = 'Update Patient: ' . $model->account->displayName;
$this->params['breadcrumbs'][] = ['label' => 'Patients', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->account->displayName, 'url' => ['view', 'id' => $model->account->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="faq-post-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
