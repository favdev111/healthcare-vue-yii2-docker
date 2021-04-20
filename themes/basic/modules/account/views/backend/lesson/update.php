<?php

use kartik\form\ActiveForm;
use kartik\datetime\DateTimePicker;
use yii\helpers\Html;
$this->title = 'Update Lesson ' . $model->id;
$form = ActiveForm::begin(['id' => 'update-lesson']);
?>
<b>Tutor: </b><?=$model->tutor->profile->showName . ' (' . $model->tutor->email . ')'?><br>
<b>Student: </b><?=$model->student->profile->showName. ' (' . $model->student->email . ')'?><br>
<b>Subject: </b><?=$model->subject->name ?><br>
<?= $form->field($model, 'convertedFromDate')
    ->widget(DateTimePicker::className(), [
        'type' => DateTimePicker::TYPE_COMPONENT_PREPEND,
        'pluginOptions' => [
            'timezone' => 'GMT',
            'autoclose'=>true,
            'format' => 'yyyy-mm-dd hh:ii:ss'
        ],
    ])
    ->label("From"); ?>
<?= $form->field($model, 'convertedToDate')
    ->widget(DateTimePicker::className(), [
        'type' => DateTimePicker::TYPE_COMPONENT_PREPEND,
        'pluginOptions' => [
            'timezone' => \Yii::$app->formatter->timeZone,
            'convertFormat' => true,
            'format' => 'yyyy-mm-dd hh:ii:ss'
        ]
    ])
    ->label("To"); ?>
<?= Html::submitButton('Save'); ?>
<?php \common\helpers\FormError::show($model);?>
<?php ActiveForm::end(); ?>

