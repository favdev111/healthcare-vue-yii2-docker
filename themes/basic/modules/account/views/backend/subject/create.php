<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model modules\account\models\Subject */

$this->title = 'Create Subject';
$this->params['breadcrumbs'][] = ['label' => 'Subject', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="faq-post-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
