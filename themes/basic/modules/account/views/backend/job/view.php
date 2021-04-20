<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model \modules\account\models\backend\Job */

$this->title = $model->getNameWithLocationAndSubject();
$this->params['breadcrumbs'][] = ['label' => 'Jobs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-view">

    <p>
        <?php if ($model->block) : ?>
            <?= Html::a('Unblock', ['unblock', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?php else: ?>
            <?= Html::a('Block', ['block', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?php endif; ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'accountId',
                'value' => $model->account->email,
                'format' => 'email',
            ],
            [
                'attribute' => 'studentGrade',
                'value' => $model->studentGradeText,
            ],
            [
                'attribute' => 'lessonOccur',
                'value' => $model->lessonOccurText,
            ],
            'zipCode',
            [
                'attribute' => 'gender',
                'value' => $model->genderText,
            ],
            [
                'attribute' => 'startLesson',
                'value' => $model->startLessonText,
            ],
            [
                'label' => 'Hourly Rate',
                'value' => $model->hourlyRateFrom . ' - ' . $model->hourlyRateTo,
            ],
            [
                'attribute' => 'availability',
                'value' => $this->render('_availability', ['model' => $model]),
                'format' => 'raw',
            ],
            [
                'label' => 'Subjects',
                'value' => implode(', ', $model->getSubjectOrCategoryNamesArray()),
            ],
            'description:ntext',
            'close:boolean',
            [
                'attribute' => 'closeDate',
                'format' => 'date',
                'visible' => $model->close,
            ],
            'createdAt:date',
        ],
    ]) ?>

</div>
