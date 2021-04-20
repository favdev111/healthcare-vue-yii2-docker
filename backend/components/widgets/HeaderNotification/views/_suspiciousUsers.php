<?php
use yii\helpers\Url;
use yii\helpers\Inflector;
use backend\components\widgets\HeaderNotification\Widget;
use yii\i18n\MessageFormatter;


$message = \Yii::t(
    'app',
    'Suspicious {count, plural, one{{user}} other{{pluralUsers}}}',
    [
        'count' => $count,
        'user' => 'user',
        'pluralUsers' => 'users',
    ]
);
?>

<li>
    <a href="<?= Url::to(['/chat/suspicious-users/']) ?>">
        <span class="label label-notify label-warning dropdown-toggle"><?= $count ?></span>
        <i class="fa fa-credit-card text-aqua"></i> <?= $message ?>

    </a>
</li>
