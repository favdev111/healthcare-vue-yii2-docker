<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Admin Accounts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-index">
  <p>
    <?= Html::a(Yii::t('app', 'Create Account'), ['create'], ['class' => 'btn btn-success']) ?>
  </p>
  <?= GridView::widget([
          'dataProvider' => $dataProvider,
          'columns' => [
              ['class' => 'yii\grid\SerialColumn'],

              'email:email',
              'firstName',
              'lastName',
              'isActive:boolean',
              [
                  'attribute' => 'roleId',
                  'label' => 'Role',
                  'value' => function ($model) {

                    if (array_key_exists($model->roleId, $model::ROLESLABEL)) {
                      return $model::ROLESLABEL[$model->roleId];
                    }
                  },
              ],
              'createdAt',
              'updatedAt',

              [
                  'class' => \backend\components\rbac\column\ActionColumn::class,
                  'visibleButtons' => [
                      'delete' => function ($model, $key, $index) {
                        return !$model->isSuperadmin() && !$model->isCurrentUserModel();
                      },
                      'update' => function ($model, $key, $index) {


                        if (!Yii::$app->user->identity->isSuperadmin()) {
                          return !$model->isSuperadmin();
                        }
                        return true;
                      }
                  ],
              ],
          ],
      ]
  ); ?>
</div>
