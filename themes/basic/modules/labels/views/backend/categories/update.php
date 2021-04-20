<?php

use yii\helpers\Html;

assert($this instanceof yii\web\View);
assert($model instanceof modules\labels\models\LabelsCategory);

$this->title = 'Update Labels Category: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Labels Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="labels-category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
