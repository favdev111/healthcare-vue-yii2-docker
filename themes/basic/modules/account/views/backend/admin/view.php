<?php

use backend\models\Account;
use common\helpers\Role;
use yii\helpers\Html;
use backend\components\rbac\Rbac;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Account */

$this->title = 'Admin: ' . $model->displayName;
$this->params['breadcrumbs'][] = ['label' => 'Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-view">
    <p>
        <?php
            if (!$model->isSuperAdmin() ||  \Yii::$app->user->identity->isSuperAdmin()) :
        ?>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php
            endif;
        ?>
        <?php
            if (!$model->isSuperadmin() && ($model->id != Yii::$app->user->id)) {
                echo Html::a(
                    Yii::t(
                        'app',
                        'Delete'
                    ),
                    ['delete', 'id' => $model->id],
                    [
                        'class' => 'btn btn-danger',
                        'data'  => [
                            'confirm' => Yii::t(
                                'app',
                                'Are you sure you want to delete this item?'
                            ),
                            'method'  => 'post',
                        ],
                    ]
                );
            }
        ?>
    </p>

    <?= DetailView::widget([
       'model' => $model,
       'attributes' => [
           'id',
           'email:email',
           'firstName',
           'lastName',
           'isActive:boolean',
           [
               'label' => 'Role',
               'value' => function ($model) {

                    if (array_key_exists($model->roleId, $model::ROLESLABEL)) {
                        return $model::ROLESLABEL[$model->roleId];
                    }
                },
           ],
           'createdAt',
           'updatedAt',
       ],
   ]) ?>

</div>
