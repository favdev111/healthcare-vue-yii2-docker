<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\TutorPro */

$this->title = 'Create Tutor Pro';
$this->params['breadcrumbs'][] = ['label' => 'Tutor Pros', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tutor-pro-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
