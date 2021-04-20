<?php
use yii\helpers\Url;
use modules\payment\models\Transaction;

$message = \Yii::t(
    'app',
    'Unapproved {count, plural, one{transaction} other{transactions}}',
    [
        'count' => $count
    ]
);
?>

<li>
    <a href="<?= Url::to(['/payment/transaction/index', 'status' => Transaction::STATUS_WAITING_FOR_APPROVE]) ?>">
        <span class="label label-notify label-warning dropdown-toggle"><?= $count ?></span>
        <i class="fa fa-credit-card text-aqua"></i>  <?= $message ?>

    </a>
</li>
