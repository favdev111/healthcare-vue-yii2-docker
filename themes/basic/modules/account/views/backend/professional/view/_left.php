<?php

/* @var $this yii\web\View */

/* @var $model \modules\account\models\backend\Account */

use backend\components\widgets\content\DetailViewList;
use yii\bootstrap4\Html;

$avatarUrl = $model->getSmallThumbnailUrl() ?? $this->theme->getUrl('img/avatar.png');

?>

<div class="box box-primary">
  <div class="box-body box-profile">
    <img class="profile-user-img img-responsive img-circle" src="<?= $avatarUrl ?>" alt="User profile picture">
    <h5 class="profile-username text-center"><?= Html::encode($model->displayName) ?></h5>
    <p class="text-muted text-center"><?= Yii::$app->formatter->asEmail($model->email) ?></p>
    <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-block']) ?>
    <?= DetailViewList::widget([
        'model' => $model,
        'attributes' => [
            'statusName:text:Status',
            'registrationStep:text',
            'isEmailConfirmed:boolean:Email confirmed',
            'searchHide:boolean:Search hide',
            'hideProfile:boolean:Public hide',
            'commission:text',
            'createdAt:date',
            'createdIp:text',
        ],
    ]) ?>
  </div>
</div>
