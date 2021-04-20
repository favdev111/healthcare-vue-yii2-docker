<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model modules\account\models\backend\Account */
$this->title = 'Client: ' . $model->displayName;
$this->params['breadcrumbs'][] = ['label' => 'Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-view">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a($model->isActive()? 'Block' : 'Unblock', [$model->isActive()? 'block' : 'unblock', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?= Html::a('Chats', ['/chat/default/list', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
        <?php if (!$model->isDeleted() && !$model->getJobs()->exists() && !$model->getStudentLessons()->exists()) : ?>

        <?php
        Modal::begin(

            [
                'toggleButton' => ['label' => 'Delete', 'class' => 'btn btn-danger'],

                'footer' =>  Html::a('Delete', ['delete', 'id' => $model->id], ['class' => 'btn btn-danger btn-sm']) . Html::a('Cancel', null, ['data-dismiss'=>'modal', 'aria-hidden' => true, 'class' => 'btn btn-primary btn-sm pull-right close']),
                'options' => [
                    'class' => 'modal--delete-student modal--center-page',
                ],
            ]
        ); ?>
    <div>Are you sure you want to delete this student?</div>

    <?php Modal::end(); ?>
    <?php endif; ?>
    </p>


    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'email:email',
            'profile.phoneNumber',
            'profile.firstName',
            'profile.lastName',
            'profile.zipCode',
            'profile.address',
            'profile.schoolName',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->statusName;
                }
            ],
            'banReason:ntext',
            'isEmailConfirmed:boolean',
            'createdAt',
            'updatedAt',
            'createdIp',
        ],
    ]) ?>
    <?php if (!empty($model->terms) && $model->terms->isTermDocCreated) :?>
    <a class="btn btn-primary" style="margin-bottom: 10px" href="<?=\common\helpers\Url::toRoute(['/account/client/pdf/', 'id' => $model->id])?>">Terms of use</a>
    <?php endif;?>
    <?= $this->render('/job/_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>
