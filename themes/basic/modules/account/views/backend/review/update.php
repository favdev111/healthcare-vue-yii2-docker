<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \modules\account\models\backend\Review */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
        'modelClass' => 'Review',
    ]) . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reviews'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="review-update">

    <?php if ($model->lessonId != null) : ?>
        <?= Html::a('View Lesson #' . $model->lessonId, ['/account/lesson/view', 'id' => $model->lessonId]) ?>
    <?php endif; ?>

    <p><?= Html::encode($model->account->email); ?></p>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
