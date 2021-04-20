<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model modules\account\models\Lesson */

$this->title = $model->account->profile->showName . ' review';
$this->params['breadcrumbs'][] = ['label' => 'Reviews', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lesson-view">

    <p>
        <?php if ($model->status == \modules\account\models\backend\Review::BANNED) : ?>
            <?= Html::a('Unblock', ['unblock', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
        <?php if ($model->status != \modules\account\models\backend\Review::BANNED) : ?>
            <?= Html::a('Block', ['block', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?php endif; ?>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label' => 'Student',
                'value' => $model->lesson ? Html::a($model->lesson->student->profile->showName, ['/account/patient/view', 'id' => $model->lesson->studentId]) : $model->name,
                'format' => 'raw',
            ],
            'message',
            [
                'attribute' => 'lessonId',
                'value' => Html::a('View Lesson #' . $model->lessonId, ['/account/lesson/view', 'id' => $model->lessonId]),
                'format' => 'raw',
                'visible' => $model->lessonId != null,
            ],
            [
                'attribute' => 'articulation',
                'format' => 'integer',
            ],
            [
                'attribute' => 'proficiency',
                'format' => 'integer',
            ],
            [
                'attribute' => 'punctual',
                'format' => 'integer',
            ],
            [
                'attribute' => 'hours',
                'format' => 'integer',
            ],
            [
                'attribute' => 'accounts',
                'format' => 'integer',
            ],
            [
                'attribute' => 'status',
                'value' => $model->statusText,
            ],
            'createdAt:date',
        ],
    ]) ?>

</div>
