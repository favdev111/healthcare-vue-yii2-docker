<?php

/* @var $this yii\web\View */
/* @var $model \modules\account\models\backend\Account */
/* @var $noteDataProvider \yii\data\ActiveDataProvider */

$this->title = 'Health professional: ' . $model->displayName;
$this->params['breadcrumbs'][] = ['label' => 'Health pros', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="account-view">
  <div class="row">
    <div class="col-md-3">
      <?= $this->render('./view/_left', ['model' => $model]) ?>
    </div>

    <div class="col-md-9">
      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
          <li class="nav-item"><a class="nav-link active" href="#basicInformation" data-toggle="tab">Basic
              information</a></li>
          <li class="nav-item"><a class="nav-link" href="#roleAndSpecifications" data-toggle="tab">Role &
              Specifications</a></li>
          <li class="nav-item"><a class="nav-link" href="#rate" data-toggle="tab">Rate</a></li>
          <li class="nav-item"><a class="nav-link" href="#profile" data-toggle="tab">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="#payoutMethod" data-toggle="tab">Payout method</a></li>
          <li class="nav-item"><a class="nav-link" href="#security" data-toggle="tab">Security</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="basicInformation">
            <div class="box box-primary">
              <div class="box-body">
                <?= $this->render('view/_basicInformation', ['model' => $model]) ?>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="profile">
            <div class="box box-primary">
              <div class="box-body">
                <?= $this->render('view/_profile', ['model' => $model]) ?>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="rate">
            <div class="box box-primary">
              <div class="box-body">
                <?= $this->render('view/_rate', ['model' => $model]) ?>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="roleAndSpecifications">
            <div class="box box-primary">
              <div class="box-body">
                <?= $this->render('view/_roleAndSpecifications', ['model' => $model]) ?>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="payoutMethod">
            <div class="box box-primary">
              <div class="box-body">
                <?= $this->render('view/_payoutMethod', ['model' => $model]) ?>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="security">
            <div class="box box-primary">
              <div class="box-body">
                <?= $this->render('view/_security', ['model' => $model]) ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
