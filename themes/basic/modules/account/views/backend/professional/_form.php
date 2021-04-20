<?php

/* @var $this yii\web\View */
/* @var $model \backend\models\Account */

/* @var $form yii\widgets\ActiveForm */

use backend\components\widgets\content\AjaxContentLoader;
use yii\helpers\Url;

?>

<div class="account-form">
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#basicInformation" data-toggle="tab">Basic
                            information</a></li>
                    <li class="nav-item"><a class="nav-link" href="#role" data-toggle="tab">Role</a></li>
                    <li class="nav-item"><a class="nav-link" href="#specifications" data-toggle="tab">Specifications</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="basicInformation">
                        <div class="box box-primary">
                            <div class="box-body">
                                <?= AjaxContentLoader::widget([
                                        'id' => 'ajaxContentBasicInformation',
                                        'url' => Url::toRoute(['/accounts/professional/basic-information/', 'id' => $model->id])
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="role">
                        <div class="box box-primary">
                            <div class="box-body">
                                <?= AjaxContentLoader::widget([
                                        'id' => 'ajaxContentRole',
                                        'url' => Url::toRoute(['/accounts/professional/role/', 'id' => $model->id])
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="specifications">
                        <div class="box box-primary">
                            <div class="box-body">
                                <?= AjaxContentLoader::widget([
                                        'id' => 'ajaxContentSpecifications',
                                        'url' => Url::toRoute(['/accounts/professional/specifications/', 'id' => $model->id])
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
