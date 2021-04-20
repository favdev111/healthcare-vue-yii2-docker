<?php

/* @var $this yii\web\View */
/* @var $model \common\models\healthProfile\HealthProfile */

?>

<div class="card">
  <div class="card-body">
    <h5 class="card-title">Medical information</h5>
    <?= $this->render('./health/_medicalInformation', ['model' => $model]) ?>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="card-title">Social info</h5>
    <?= $this->render('./health/_socialInfo', ['model' => $model]) ?>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="card-title">Health goals</h5>
    <?= $this->render('./health/_healthGoals', ['model' => $model]) ?>
  </div>
</div>
