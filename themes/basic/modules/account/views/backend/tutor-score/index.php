<?php

use common\assets\ToastrAsset;
use common\helpers\Url;
use common\models\search\SentNewJobNotificationSearch;
use kartik\form\ActiveForm;
use modules\account\models\TutorScoreSettings;
ToastrAsset::register($this);
use kartik\grid\GridView;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TutorProSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var string $type*/
/* @var \yii\data\ActiveDataProvider $statisticProvider*/
/* @var SentNewJobNotificationSearch $search*/

$this->title = 'Tutor Score';
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss(".input-div{max-width:200px; display: inline-block;}");
/**
 * @var \modules\account\controllers\backend\TutorScoreController $context
 */
$context = $this->context;
//echo '<pre>';print_r($settings);die;
$tabs = [
    ['type' => TutorScoreSettings::TYPE_CONTENT_PROFILE, 'title' => 'Profile Content'],
    ['type' => TutorScoreSettings::TYPE_RESPONSE_TIME, 'title' => 'Response Time'],
    ['type' => TutorScoreSettings::TYPE_HOURS, 'title' => 'Tutoring Hours'],
    ['type' => TutorScoreSettings::TYPE_RATING, 'title' => 'Rating'],
    ['type' => TutorScoreSettings::TYPE_RECENT_ACTIVITY, 'title' => 'Most Recent Activity Score'],
    ['type' => TutorScoreSettings::TYPE_DISTANCE_SCORE, 'title' => 'Distance Score'],
    ['type' => TutorScoreSettings::TYPE_AVAILABILITY_SCORE, 'title' => 'Availability Score'],
    ['type' => TutorScoreSettings::TYPE_HOURS_PER_RELATION_SCORE, 'title' => 'Hours per relationship Score'],
    ['type' => $context::TYPE_SCORE_STATISTIC, 'title' => 'Score Statistic'],
    ['type' => $context::TYPE_SCORE_APPLY_BONUS, 'title' => 'Automatch Scores (Quiz)'],
    ['type' => TutorScoreSettings::TYPE_REMATCHES_PER_MATCH_SCORE, 'title' => 'Rematches per match score'],
    ['type' => TutorScoreSettings::TYPE_HOURS_PER_SUBJECT_SCORE, 'title' => 'Hours per subject score'],
    ['type' => TutorScoreSettings::TYPE_REFUNDS_PER_MATCH_SCORE, 'title' => 'Refunds per match score'],
]
?>
<div class="tutor-pro-index nav-tabs-custom">
    <ul class="nav nav-tabs" role="tablist">
        <?php foreach ($tabs as $tab): ?>
            <li role="presentation" class="nav-item ">
                <a class="nav-link <?= $type == $tab['type'] || $type == ''? 'active' : ''?>" href="<?php echo Url::to(['/account/tutor-score/index', 'type' => $tab['type']]); ?>"><?=$tab['title']?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active">
            <div class="">
                <?php if (! in_array($type, [$context::TYPE_SCORE_APPLY_BONUS, $context::TYPE_SCORE_STATISTIC])):?>
                    <?php $form = ActiveForm::begin(['id' => 'dynamic-form'
                    ]); ?>

                    <?php if (sizeof($settings) > 0): ?>

                    <?php DynamicFormWidget::begin([
                            'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                            'widgetBody' => '.container-items', // required: css class selector
                            'widgetItem' => '.item', // required: css class
                            'min' => 1, // 0 or 1 (default 1)
                            'insertButton' => '.add-item', // css class
                            'deleteButton' => '.remove-item', // css class
                            'model' => $settings[0],
                            'formId' => 'dynamic-form',
                            'formFields' => [
                                    'key',
                                    'value',
                            ],
                    ]); ?>

                    <div class="panel panel-default">
                        <div class="panel-heading" style="display:flex; flex-wrap: wrap; justify-content: space-between">
                            <h4>
                                Score values
                            </h4>
                            <button type="button" class="add-item btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> Add new key</button>
                        </div>
                        <div class="panel-body">
                            <div class="container-items" style="display: flex; flex-wrap: wrap; flex-basis: 200px"><!-- widgetBody -->
                                <?php foreach ($settings as $i => $setting): ?>
                                    <div style="margin-right: 20px" class="item panel panel-default"><!-- widgetItem -->
                                        <div class="panel-heading">
                                            <div class="text-right">
                                                <button type="button" class="remove-item btn btn-danger btn-xs"><i class="fa fa-minus"></i></button>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="panel-body">
                                            <?php
                                            // necessary for update action.
                                            if (! $setting->isNewRecord) {
                                                echo Html::activeHiddenInput($setting, "[{$i}]id");
                                            }
                                            ?>
                                            <?= $form->field($setting, "[{$i}]key")->textInput() ?>
                                            <?= $form->field($setting, "[{$i}]value")->textInput() ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div><!-- .panel -->
                    <?php DynamicFormWidget::end(); ?>

                    <?php endif; ?>

                    <div class="text-center">
                        <button class="btn btn-lg btn-primary mb50 tab-pane__button tab-pane__button--save" id="edit-profile-submit">Save Settings</button>
                    </div>
                    <?php ActiveForm::end(); ?>
                <?php elseif ($type == $context::TYPE_SCORE_APPLY_BONUS):?>
                    <?php $form = ActiveForm::begin([]); ?>
                <div class="container-fluid" style="margin-top: 30px">
                    <?php foreach (\common\helpers\Automatch::$applyBonusKeys as $key):?>
                        <div class="input-div" style="min-width:250px;display: flex;align-items: center;justify-content: space-between;">
                            <?= \common\helpers\Html::label(\common\helpers\Automatch::$applyBonusLabels[$key])?>
                            <?= \common\helpers\Html::input('text', $key, \common\helpers\Automatch::getBonusPointValue($key), ['class' => 'form-control', 'style' => 'max-width:150px'])?>
                        </div>
                    <?php endforeach;?>
                </div>
                    <div class="text-center">
                        <button class="btn btn-lg btn-primary mb50 tab-pane__button tab-pane__button--save" id="edit-profile-submit">Save Settings</button>
                    </div>
                    <?php ActiveForm::end(); ?>
                <?php elseif (!empty($statisticProvider)):
                    echo GridView::widget([
                        'showHeader' => true,
                        'summary' => false,
                        'dataProvider' => $statisticProvider,
                        'filterModel' => $search,
                        'columns' => [
                            'accountId',
                            [
                                'attribute' => 'firstName',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $link = Url::toRoute(['/account/tutor/view/', 'id' => $model->accountId]);
                                    return \yii\helpers\Html::a($model->getTutorFirstName(), $link);
                                }
                            ],
                            [
                                'attribute' => 'lastName',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $link = Url::toRoute(['/account/tutor/view/', 'id' => $model->accountId]);
                                    return \yii\helpers\Html::a($model->getTutorLastName(), $link);
                                }
                            ],
                            'jobId',
                            [
                                'attribute' => 'jobName',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $link = Url::toRoute(['/account/job/view/', 'id' => $model->jobId]);
                                    return \yii\helpers\Html::a($model->getJobName(), $link);
                                }
                            ],
                            'totalScore'
                        ]
                    ]);
                endif;?>
            </div>
        </div>
    </div>
</div>
