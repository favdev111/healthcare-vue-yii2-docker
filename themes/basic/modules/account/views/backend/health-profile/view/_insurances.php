<?php

/* @var $this yii\web\View */
/* @var $model \common\models\healthProfile\HealthProfile */

?>

<?php if (!$model->insurances): ?>
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Insurances not found</h5>
    </div>
  </div>
<?php endif; ?>

<?php foreach ($model->insurances as $insurance): ?>
  <div class="card">
    <div class="card-body">
      <h5 class="card-title"><?= $insurance->isPrimary ? 'Primary' : 'Additional' ?></h5>
      <?= $this->render('_insurance', ['model' => $insurance]) ?>
    </div>
  </div>
<?php endforeach; ?>
