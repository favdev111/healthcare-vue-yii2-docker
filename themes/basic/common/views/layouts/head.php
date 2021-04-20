<?php
use yii\helpers\Html;

/**
 * @var \common\components\View $this
 */
?>
<meta charset="<?= Yii::$app->charset ?>">
<?php if (Yii::$app->isMobile): ?>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
<?php elseif (Yii::$app->isResponsive): ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php else: ?>
    <meta name="viewport" width=device-width">
<?php endif; ?>
<link rel="apple-touch-icon" sizes="76x76" href="<?= $this->theme->getUrl('favicon/apple-touch-icon.png');?>">
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" href="<?= $this->theme->getUrl('favicon/favicon-32x32.png');?>" sizes="32x32">
<link rel="icon" type="image/png" href="<?= $this->theme->getUrl('favicon/favicon-16x16.png');?>" sizes="16x16">
<link rel="manifest" href="<?= $this->theme->getUrl('favicon/manifest.json');?>">
<link rel="mask-icon" href="<?= $this->theme->getUrl('favicon/safari-pinned-tab.svg');?>" color="#5bbad5">
<meta name="theme-color" content="#ffffff">
<meta name="msvalidate.01" content="C7B57037BE3C58970EE882E4BBDEBA68" />
<?php if (isset($this->params['noindex']) && $this->params['noindex']) : ?>
    <meta name="robots" content="noindex">
<?php endif; ?>
<?= $this->renderDynamic(' return \yii\helpers\Html::csrfMetaTags(); '); ?>
<title><?= Html::encode($this->title) ?></title>
<link rel="preload" as="image" href="<?= $this->theme->getUrl('img/preloader.gif') ?>">
<?php
    $this->head();
    if (Yii::$app->user->isGuest) {
        if (
            in_array(
                Yii::$app->requestedRoute,
                [
                    'site/index',
                ]
            )
        ) {
            echo $this->render('_organization');
        }
    }
