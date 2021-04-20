<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model modules\account\models\Lesson */
/* @var $searchModel \modules\payment\models\backend\TransactionSearch*/
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->subject->name;
$this->params['breadcrumbs'][] = ['label' => 'Lessons', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lesson-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'studentId',
                'value' => (!$model->student) ? null : Html::a($model->student->email, ['/account/patient/view', 'id' => $model->studentId], ['data-pjax' => 0]),
                'format' => 'raw',
                'label' => 'Student',
            ],
            [
                'attribute' => 'tutorId',
                'value' => Html::a($model->tutor->email, ['/account/tutor/view', 'id' => $model->tutorId],  ['data-pjax' => 0]),
                'format' => 'raw',
                'label' => 'Tutor',
            ],
            [
                'attribute' => 'fromDate',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'toDate',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'subjectId',
                'value' => $model->subject->name
            ],
            [
                'attribute' => 'hourlyRate',
                'format' => 'currency',
            ],
            [
                'attribute' => 'status',
                'value' => $model->statusString,
            ],
            [
                'attribute' => 'amount',
                'format' => 'currency',
            ],
            [
                'attribute' => 'fee',
                'format' => 'currency',
            ],
            'createdAt:date',
        ],
    ]) ?>

    <?= $this->render('@modules/payment/views/backend/transaction/_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>
