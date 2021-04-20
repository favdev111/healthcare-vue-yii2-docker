<?= Yii::$app->shareasale->getLeadPixel(); ?>

<?php
    $sentryJsDsn = env('SENTRY_JS_DSN');
    if ($sentryJsDsn) {
        echo '<script src="https://browser.sentry-cdn.com/4.5.3/bundle.min.js" crossorigin="anonymous"></script>';
        echo '<script>Sentry.init({
          dsn: \''. $sentryJsDsn .'\',
          environment: \''. YII_ENV .'\'
        });</script>';
    }
?>
