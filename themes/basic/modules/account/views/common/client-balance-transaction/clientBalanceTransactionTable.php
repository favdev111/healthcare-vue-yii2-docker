<?php

/**
 * @var $transactions \modules\account\models\ClientBalanceTransaction[]
 */

use modules\payment\models\Transaction;
$accountModule = Yii::$app->getModule('account');
?>
<div>


    <table style="border-collapse: collapse; border-spacing: 0;">
        <tr>
            <td style="padding-top: 15px; padding-bottom: 15px;"><img style="width: 80px; height: 80px; vertical-align: middle;" src="<?=$accountModule->getAvatarPath(null, $client->company)?>"/></td>
            <td style="padding: 15px"><span style="display: inline-block;"><?=$client->companyWithoutRestrictions->profile->companyName?></span></td>
        </tr>
    </table>

    <table style="width: 740px; background-color: #fff; border: 1px solid #000; border-collapse: collapse; border-spacing: 0;">
        <tr>
            <th style="padding: 10px; text-align: left; font-weight: 100; border-bottom: 1px solid #000">DATE</th>
            <th style="padding: 10px; text-align: center; font-weight: 100; border-bottom: 1px solid #000">DESCRIPTION</th>
            <th style="padding: 10px; text-align: center; font-weight: 100; border-bottom: 1px solid #000">STATUS</th>
            <th style="padding: 10px; text-align: right; font-weight: 100; border-bottom: 1px solid #000">Debit/Credit</th>
        </tr>
        <?php
        foreach ($transactions as $clientBalance) {
        $lesson = null;
        $transaction = $clientBalance->transaction;
        ?>
        <tr>
            <?php
            $isLessonRefundTransaction = false;
            if (!empty($transaction)) {
                if ($transaction->isLessonBatchPayment() || $transaction->isLesson()) {
                    $lesson = $transaction->lesson;
                    $subjectName = $lesson->subject->name;
                    $lessonDate = $lesson->getFormattedLessonDate('m/d/Y');
                    $lessonDuration = $lesson->getDuration();
                    $isLessonRefundTransaction = $transaction->type === Transaction::STRIPE_REFUND;
                }
            }
            ?>
            <td style="padding: 10px; text-align: left; border-bottom: 1px solid #000">
                <?=DateTime::createFromFormat('Y-m-d H:i:s', $clientBalance->createdAt)->format('m/d/Y') ?>
            </td>
            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #000">
                <?php if (!$isLessonRefundTransaction && empty($lesson)) {
                    echo $clientBalance->getTypeLabel() . '<br>';
                } ?>
                <?=
                //check partial refunds
                !empty($clientBalance->transaction) && $clientBalance->transaction->isHasPartialRefunds() ?
                    (
                        //client balance full refund works as partial refund with full sum of transaction
                        // to check full refund check not refunded sum
                        (
                        $clientBalance->transaction->calculateNotRefundedSum() == 0 ?
                            'Refunded'
                            : 'Partially refunded'
                        ) . '<br>'
                    )
                    : '' ?>
                <?= isset($lesson) ? 'Lesson' : 'Balance refill' ?>
                <?= !empty($clientBalance->transaction) && $clientBalance->transaction->isTypeRefund() ? 'Refunded' : '' ?>
                <?php if (!empty($lesson) ) : ?>
                    <?=$clientBalance->getTableRowDescription()?>
                <?php endif; ?>
                <?php if ($clientBalance->isManual() && !empty($clientBalance->note)) :?>
                <br><b style="font-size: 14px">"<?=$clientBalance->note?>"</b>
                <?php endif;?>
            </td>
            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #000">Success</td>
            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                <?= \common\components\View::displayedAmountClientBalancePdf($clientBalance);?>
            </td>
        </tr>
        <?php }  ?>
    </table>
</div>
