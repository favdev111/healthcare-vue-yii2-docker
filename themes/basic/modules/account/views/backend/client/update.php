<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $account modules\account\models\Account */
/* @var $rate modules\account\models\Rate */
/* @var $model modules\account\models\Profile */
/* @var $subjects modules\account\models\Subject */

$this->title = 'Update Student: ' . $account->displayName;
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $account->displayName, 'url' => ['view', 'id' => $account->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="faq-post-update">

    <?= $this->render('_form', [
        'model' => $model,
        'account' => $account,
    ]) ?>

</div>
