<?php

use yii\helpers\Html;


assert($this instanceof yii\web\View);
assert($model instanceof modules\labels\models\LabelsCategory);

$this->title = 'Create Labels Category';
$this->params['breadcrumbs'][] = ['label' => 'Labels Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="labels-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
