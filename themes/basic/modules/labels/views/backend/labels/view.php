<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

assert($this instanceof yii\web\View);
assert($model instanceof modules\labels\models\Labels);

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Labels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="labels-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'status',
            'color',
            'categoryId',
            'createdAt',
            'updatedAt',
        ],
    ]) ?>

</div>
