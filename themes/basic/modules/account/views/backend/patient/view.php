<?php

/* @var $this yii\web\View */
/* @var $model \modules\account\models\backend\Account */
/* @var $noteDataProvider \yii\data\ActiveDataProvider */

$this->title = 'Patient: ' . $model->displayName;
$this->params['breadcrumbs'][] = ['label' => 'Patient', 'url' => ['index']];
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
                    <li class="nav-item"><a class="nav-link" href="#paymentMethod" data-toggle="tab">Payment method</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#security" data-toggle="tab">Security</a></li>
                    <li class="nav-item"><a class="nav-link" href="#healthProfile" data-toggle="tab">Health profiles</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="basicInformation">
                        <div class="box box-primary">
                            <div class="box-body">
                                <?= $this->render('view/_basicInformation', ['model' => $model]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="paymentMethod">
                        <div class="box box-primary">
                            <div class="box-body">
                                <?= $this->render('view/_paymentMethod', ['model' => $model]) ?>
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

                    <div class="tab-pane" id="healthProfile">
                        <div class="box box-primary">
                            <div class="box-body">
                                <?= $this->render('view/_healthProfiles', ['model' => $model]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
