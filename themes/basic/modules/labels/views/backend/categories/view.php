<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

assert($this instanceof yii\web\View);
assert($model instanceof modules\labels\models\LabelsCategory);

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Labels Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="labels-category-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'createdAt',
            'updatedAt',
        ],
    ]) ?>

</div>
