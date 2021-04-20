<?php

/* @var $this yii\web\View */
/* @var $model Account */

use backend\components\widgets\content\AjaxContentLoader;
use modules\account\models\Account;
use yii\helpers\Url;

?>

<?= AjaxContentLoader::widget([
    'url' => Url::toRoute(['/accounts/professional/change-password', 'id' => $model->id]),
    'contentOptions' => ['class' => 'col-6']
]) ?>
