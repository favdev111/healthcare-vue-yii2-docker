<?php

use common\helpers\AccountStatusHelper;
use kartik\grid\GridView;
use modules\account\models\Account;

/* @var $this yii\web\View */
/* @var $searchModel modules\account\models\backend\AccountProfessionalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Health Pros';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-index">
  <?php echo $this->render('_search', ['model' => $searchModel]); ?>

  <?= GridView::widget([
      'id' => 'grid-search-tutor',
      'dataProvider' => $dataProvider,
      'rowOptions' => static function (Account $model, $key, $index, $grid) {
        if ($model->status === AccountStatusHelper::STATUS_NEED_REVIEW) {
          return ['class' => 'table-warning'];
        }
      },
      'columns' => [
          [
              'attribute' => 'id',
              'width' => '40px',
          ],
          [
              'attribute' => 'zipCode',
              'value' => 'profile.zipCode',
              'width' => '35px',
          ],
          [
              'attribute' => 'distance',
              'value' => function ($model) {
                return Yii::$app->formatter->asDecimal($model->distance, 2);
              },
              'visible' => $searchModel->zipCode,
          ],
          'email:email',
          [
              'label' => 'Name',
              'attribute' => 'displayName',
          ],
          [
              'attribute' => 'gender',
              'value' => 'profile.genderName',
              'width' => '30px',
          ],
          [
              'attribute' => 'phoneNumber',
              'value' => 'profile.formattedPhone',
              'noWrap' => true,
          ],
          'attribute' => 'statusName',
          'createdAt:date',
          [
              'class' => \backend\components\rbac\column\ActionColumn::class,
              'template' => '{view} {update}'
          ],
      ],
      'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
      'headerRowOptions' => ['class' => 'kartik-sheet-style'],
      'pjax' => true,
    // set your toolbar
      'toolbar' => [
          '{export}',
          '{toggleData}',
      ],
    // set export properties
      'export' => [
          'fontAwesome' => true
      ],
      'condensed' => true,
      'responsive' => true,
      'hover' => true,
      'showPageSummary' => false,
      'persistResize' => false,
  ]);
  ?>

</div>
