<?php

/* @var $this yii\web\View */
/* @var $model \common\models\healthProfile\HealthProfile */
/* @var $noteDataProvider \yii\data\ActiveDataProvider */

$this->title = 'Patient: ' . $model->firstName . " " . $model->lastName;
$this->params['breadcrumbs'][] = ['label' => 'Health profile', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="account-view">
  <div class="row">
    <div class="col-md-12">
      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
          <li class="nav-item"><a class="nav-link " href="#general" data-toggle="tab">General</a></li>
          <li class="nav-item"><a class="nav-link" href="#insurances" data-toggle="tab">Insurances</a></li>
          <li class="nav-item"><a class="nav-link active" href="#health" data-toggle="tab">Health</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane " id="general">
            <div class="box box-primary">
              <div class="box-body">
                <?= $this->render('view/_general', ['model' => $model]) ?>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="insurances">
            <div class="box box-primary">
              <div class="box-body">
                <?= $this->render('view/_insurances', ['model' => $model]) ?>
              </div>
            </div>
          </div>

          <div class="tab-pane active" id="health">
            <div class="box box-primary">
              <div class="box-body">
                <?= $this->render('view/_health', ['model' => $model]) ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
