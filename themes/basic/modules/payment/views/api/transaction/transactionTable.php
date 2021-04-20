<?php

/**
 * @var $transactions \modules\payment\models\api\Transaction[]
 */

use modules\payment\models\Transaction;
$accountModule = Yii::$app->getModule('account');
?>
<div>
    
    
    <table style="border-collapse: collapse; border-spacing: 0;">
        <tr>
            <td style="padding-top: 15px; padding-bottom: 15px;"><img style="width: 80px; height: 80px; vertical-align: middle;" src="<?=$accountModule->getAvatarPath()?>"/></td>
            <td style="padding: 15px"><span style="display: inline-block;"><?=$client->company->profile->companyName?></span></td>
        </tr>
    </table>

    <table style="width: 740px; background-color: #fff; border: 1px solid #000; border-collapse: collapse; border-spacing: 0;">
        <tr>
            <th style="padding: 10px; text-align: left; font-weight: 100; border-bottom: 1px solid #000">TRANSACTION #</th>
            <th style="padding: 10px; text-align: center; font-weight: 100; border-bottom: 1px solid #000">TYPE OF TRANSACTIONS</th>
            <th style="padding: 10px; text-align: right; font-weight: 100; border-bottom: 1px solid #000">AMOUNT</th>
        </tr>
        <?php foreach ($transactions as $item) { ?>
        <tr>
            <?php
            $isLessonRefundTransaction = false;
            $lesson = $item->lesson;
            if ($item->objectType === Transaction::TYPE_LESSON && !empty($lesson)) {
                $subjectName = $lesson->subject->name;
                $lessonDate = $lesson->getFormattedLessonDate('m/d/Y');
                $lessonDuration = $lesson->getDuration();
                $amount = $item->amountWithFee;
                $isLessonRefundTransaction = $item->type === Transaction::STRIPE_REFUND;
            } else {
                $amount = $item->amount;
            }
            ?>
            <td style="padding: 10px; text-align: left; border-bottom: 1px solid #000"><?=$item->id?></td>
            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #000"><?=$isLessonRefundTransaction ? "Refund ($subjectName $lessonDate)" : $item->getTypeObjectLabel()?>
                <?php if (!empty($lesson) && !$isLessonRefundTransaction) :?>
                (<?=$subjectName?>)<br> <span><?=$lessonDate?><br><span>Lesson duration <?=$lessonDuration?></span></span>
            <?php endif; ?>
            </td>
            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000"><?=$amount?></td>
        </tr>
        <?php }  ?>
    </table>
</div>
