<?php
use yii\helpers\Url;

$message = \Yii::t(
    'app',
    'Generated {count, plural, one{job} other{jobs}}',
    [
        'count' => $count
    ]
);
?>

<li>
    <a href="<?= Url::to(['/account/job/index', 'autogenerate' => 1]) ?>">
        <span class="label label-notify label-warning"><?= $count ?></span>
        <i class="fa fa-briefcase text-aqua"></i> <?= $message ?>

    </a>
</li>
