<?php

use common\components\View;
use kartik\grid\GridView;
use kartik\select2\Select2;
use modules\core\components\ApiLogRequests;
use modules\core\models\ApiLogRequest;
use modules\core\models\search\ApiLogRequestSearch;
use yii\bootstrap4\Modal;
use yii\web\JsExpression;
use common\helpers\Html;

/* @var $searchModel ApiLogRequestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $this View */

$this->title = Yii::t('app', 'API Log Requests');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
  <div class="col-12">
      <?= GridView::widget([
          'id' => 'api-log-request-grid-view',
          'dataProvider' => $dataProvider,
          'filterModel' => $searchModel,
          'export' => false,
          'pjax' => true,
          'pjaxSettings' => [
              'neverTimeout' => true,
              'options' => [
                  'id' => 'api-log-request-grid-view-container',
                  'enablePushState' => false,
              ],
          ],
          'responsive' => true,
          'layout' => "{items}\n{pager}",
          'columns' => [
              [
                  'attribute' => 'request_url',
              ],
              [
                  'attribute' => 'action_name',
                  'value' => function ($model) {
                    $name = ApiLogRequest::getRequests($model->action_name);
                    if ($model->controller_name && $model->action_name) {
                      return $name === 'N/A' ? $model->controller_name . '/' . $model->action_name : $name;
                    }

                    return $name;
                  },
                  'filterType' => GridView::FILTER_SELECT2,
                  'filterWidgetOptions' => [
                      'data' => ApiLogRequestSearch::$actionsList,
                      'options' => ['placeholder' => Yii::t('app', 'All')],
                      'pluginOptions' => [
                          'escapeMarkup' => new JsExpression('function (m) { return m; }'),
                      ],
                      'theme' => Select2::THEME_KRAJEE_BS4,
                  ]
              ],
              [
                  'attribute' => 'request_body_params',
                  'value' => function ($model) {
                    return empty($model->request_body_params) ? null : Html::a('Click to see body params', ['ajax-view-log-request', 'type' => 'request_body_params', 'id' => $model->id], ['class' => 'ajax-view-log-request', 'data-pjax' => '0']);
                  },
                  'format' => 'raw',
              ],
              [
                  'attribute' => 'response',
                  'value' => function ($model) {
                    return empty($model->response) ? null : Html::a('Click to see response', ['ajax-view-log-request', 'type' => 'response', 'id' => $model->id], ['class' => 'ajax-view-log-request', 'data-pjax' => '0']);
                  },
                  'format' => 'raw',
              ],
              [
                  'attribute' => 'request_duration',
                  'value' => function ($model) {
                    return $model->getRequestDuration();
                  },
                  'format' => 'decimal',
              ],
              [
                  'attribute' => 'status',
                  'filterType' => GridView::FILTER_SELECT2,
                  'filterWidgetOptions' => [
                      'data' => \common\helpers\Reflection::getConstants(ApiLogRequests::class, 'STATUS'),
                      'options' => ['placeholder' => Yii::t('app', 'All')],
                      'pluginOptions' => [
                          'escapeMarkup' => new JsExpression('function (m) { return m; }'),
                      ],
                      'theme' => Select2::THEME_KRAJEE_BS4
                  ]
              ],
              [
                  'attribute' => 'started_at',
                  'format' => 'datetime',
                  'filterType' => GridView::FILTER_DATE,
                  'filterWidgetOptions' => [
                      'readonly' => true,
                      'pluginOptions' => [
                          'format' => 'dd/mm/yyyy',
                          'autoclose' => true,
                          'todayHighlight' => true,
                      ],
                      'options' => ['aria-describedby' => 'birthdayHelpBlock'],
                  ],
              ],
          ],
      ]); ?>
  </div>
</div>
<?php
Modal::begin([
    'id' => 'modal-log-request',
    'size' => Modal::SIZE_LARGE,
]); ?>
<div id="modal-log-request-content" style="width:100%; word-wrap: break-word;"></div>

<?php Modal::end(); ?>
<?php
$script =
    <<< JS
    $(document).on('click', '.ajax-view-log-request', function(e){
        e.preventDefault();
        $('#modal-log-request-content').html('');
        $.get($(this).attr('href'), function(data){
            $('#modal-log-request-content').html(data);
        })

        $('#modal-log-request').modal('show');
    })
JS;

$this->registerJs($script);
