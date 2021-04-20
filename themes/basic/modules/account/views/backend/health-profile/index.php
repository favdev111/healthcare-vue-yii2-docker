<?php

use backend\components\rbac\column\ActionColumn;
use backend\components\widgets\content\Pjax;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Health profiles';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="account-index">
  <?php Pjax::begin([
      'id' => 'health-profile-index-pjax',
      'timeout' => false,
      'enablePushState' => false,
      'enableReplaceState' => false,
      'options' => [
          'data-pjax-push-state' => false
      ]
  ]); ?>

  <?= GridView::widget([
          'dataProvider' => $dataProvider,
          'columns' => [
              ['class' => 'yii\grid\SerialColumn'],
              'firstName',
              'lastName',
              'isMain:boolean:Owner',
              [
                  'class' => ActionColumn::class,
                  'template' => '{view}'
              ],
          ],
      ]
  ) ?>

  <?php Pjax::end(); ?>
</div>
