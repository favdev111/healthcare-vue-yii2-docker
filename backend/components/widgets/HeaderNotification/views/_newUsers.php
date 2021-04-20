<?php
use yii\helpers\Url;
use yii\helpers\Inflector;
use backend\components\widgets\HeaderNotification\Widget;
use yii\i18n\MessageFormatter;

$user = Inflector::titleize($type);
$pluralUsers = Inflector::pluralize($user);

$message = \Yii::t(
    'app',
    '{count, plural, one{{user}} other{{pluralUsers}}} joined today',
    [
        'count' => $count,
        'user' => $user,
        'pluralUsers' => $pluralUsers,
    ]
);
?>

<li>
    <a href="<?= Url::to(['/accounts/'. $type .'/']) ?>">
        <span class="label label-notify label-warning dropdown-toggle"><?= $count ?></span>
        <i class="<?= ($type === Widget::USER_TYPE_TUTOR) ? 'fa fa-graduation-cap' : 'fa fa-user-o'?> text-aqua"></i> <?= $message ?>

    </a>
</li>