<?php

use yii\helpers\Html;

assert($this instanceof yii\web\View);
assert($model instanceof modules\labels\models\Labels);

$this->title = 'Update Labels: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Labels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="labels-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'statusList'=>$statusList,
        'categories'=>$categories
    ]) ?>

</div>
