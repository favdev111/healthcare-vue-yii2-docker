<?php
use yii\helpers\Url;

$message = \Yii::t(
    'app',
    'Pro service {count, plural, one{request} other{requests}}',
    [
        'count' => $count
    ]
);
?>




<li>
    <a href="<?= Url::to(['/tutor-pro-service']) ?>">
        <span class="label label-notify label-warning dropdown-toggle"><?= $count ?></span>
        <i class="fa fa-users text-aqua"></i> <?= $message ?>

    </a>
</li>
