<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel \modules\account\models\search\AutomatchSubjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Automatch Subjects';
$this->params['breadcrumbs'][] = $this->title;
use kartik\select2\Select2;
use yii\web\JsExpression;

?>
<div class="subject-index container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <label>Add subject</label>
            </div>
            <div class="row">
                <?= Html::beginForm(['/automatch-subjects/create'], 'post');?>
                <div style="display: flex;align-items: baseline;justify-content: start;">
                    <div class="form-group form-group--w250 mb20 mr20">
                        <?php echo Select2::widget([
                                'id' => 'nav-search-select',
                                'name' => 'subjectId',
                                'theme' => Select2::THEME_KRAJEE_BS4,
                                'options' => ['placeholder' => 'Subject', 'autocomplete' => "off"],
                                'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 1,
                                        'ajax' => [
                                                'url' => '/api/auto/subjects-list/?withCategory=0',
                                                'data' => new JsExpression('function(params) { return {query:params.term}; }'),
                                                'processResults' => new JsExpression('function (data, params) {
                                                            if ($(".select2-search__field").val() == 0) {
                                                                return {
                                                                    results: []
                                                                  };
                                                            }
                                                            subjectsData = data;
                                                            return {
                                                                results: data
                                                              };
                                                        }'),
                                        ],

                                ],
                                'pluginEvents' => [
                                        "change" => "function() { }",
                                        "select2:selecting" => "function(el) {
                                                        var choice = el.params.args.data;
                                                        var id = choice.id;
                                                        var exist = false;
                                                        $.each($('.current-subject-block'), function (i, el) {
                                                            if($(el).data('id') == id) {
                                                                exist = true;
                                                                return false;
                                                            }
                                                        });
                                                        if (exist) {
                                                            toastr.error('such subject exists');
                                                            return false;
                                                        }
                                                    }",
                                        "select2:select" => "function(el) {
                                                        $('#btnGetSearch').trigger('click');
                                                    }"
                                ]
                        ]);
                        ?>
                        <span class="addon-placeholder">
                            <img class="icon-book" src="<?= $this->theme->getUrl('/img/icon-book.png'); ?>" alt="">
                     </span>
                    </div>
                    <?=  Html::submitButton(
                            'Add subject to list',
                            [
                                    'class' => 'btn btn-primary',
                            ]
                    ); ?>
                    <?=Html::endForm()?>
                </div>
            </div>
            <div class="row automatch-table-row">
                <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                                'subjectName',
                                [
                                        'class' => \backend\components\rbac\column\ActionColumn::class,
                                        'visibleButtons' => [
                                                'update' => false,
                                                'view' => false,
                                        ]
                                ],
                        ],
                ]); ?>
            </div>
        </div>
</div>
