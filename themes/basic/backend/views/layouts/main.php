<?php


use common\assets\ToastrAsset;
use odaialali\yii2toastr\ToastrFlash;
use yii\helpers\Html;

ToastrAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>
<div id="app" class="main-wrapper">
    <?= ToastrFlash::widget(['options' => ['positionClass' => 'toast-top-right']]); ?>
    <?= $this->render('header') ?>
    <div class="main-sidebar">
        <?= $this->render('left') ?>
    </div>
    <div class="main-content mb-3">
        <?= $this->render('content', ['content' => $content]) ?>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
