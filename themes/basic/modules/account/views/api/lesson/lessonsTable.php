
<?php

/**
 * @var $lessonsQuery \yii\db\ActiveQuery
 * @var $companyName string
 * @var $avatarPath string
 */


$totalLessonsDuration = 0;
$totalEarned = 0;
$totalSpent = 0;
?>
<div>


    <table style="border-collapse: collapse; border-spacing: 0;">
        <tr>
            <td style="padding-top: 15px; padding-bottom: 15px;"><img style="width: 80px; height: 80px; vertical-align: middle;" src="<?=$avatarPath?>"/></td>
            <td style="padding: 15px"><span style="display: inline-block;"><?=$companyName?></span></td>
        </tr>
    </table>

    <table style="width: 740px; background-color: #fff; border: 1px solid #000; border-collapse: collapse; border-spacing: 0;">
        <tr>
            <th style="padding: 10px; text-align: left; font-weight: 100; border-bottom: 1px solid #000">ID</th>
            <th style="padding: 10px; text-align: left; font-weight: 100; border-bottom: 1px solid #000">STUDENT NAME</th>
            <th style="padding: 10px; text-align: center; font-weight: 100; border-bottom: 1px solid #000">TUTOR NAME</th>
            <th style="padding: 10px; text-align: center; font-weight: 100; border-bottom: 1px solid #000">SUBJECT</th>
            <th style="padding: 10px; text-align: right; font-weight: 100; border-bottom: 1px solid #000">FROM DATE</th>
            <th style="padding: 10px; text-align: right; font-weight: 100; border-bottom: 1px solid #000">TO DATE</th>
            <th style="padding: 10px; text-align: right; font-weight: 100; border-bottom: 1px solid #000">LESSON DURATION</th>
            <th style="padding: 10px; text-align: right; font-weight: 100; border-bottom: 1px solid #000">TOTAL LESSON AMOUNT</th>
            <th style="padding: 10px; text-align: right; font-weight: 100; border-bottom: 1px solid #000">TOTAL PAYED TO TUTOR</th>
        </tr>
        <?php foreach ($lessonsQuery->batch(50) as $lessons) {?>
        <?php   foreach ($lessons as $lesson) {
                $totalLessonsDuration += $lesson->minutesDuration;
                $totalEarned += $lesson->clientPrice;
                $totalSpent += ($lesson->amount + $lesson->fee);
            ?>
            <tr>
                <td style="padding: 10px; text-align: left; border-bottom: 1px solid #000">
                    <?=$lesson->id?>
                </td>
                <td style="padding: 10px; text-align: center; border-bottom: 1px solid #000">
                    <?=$lesson->student->getFullName()?>
                </td>
                <td style="padding: 10px; text-align: center; border-bottom: 1px solid #000">
                    <?=$lesson->tutor->getFullName()?>
                </td>
                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                    <?=$lesson->subject->name?>
                </td>
                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                    <?=Yii::$app->formatter->asDatetime($lesson->fromDate, 'php:m/d/Y g:i A');?>
                </td>
                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                    <?=Yii::$app->formatter->asDatetime($lesson->toDate, 'php:m/d/Y g:i A');?>
                </td>
                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                    <?php
                        $hours = $lesson->minutesDuration / 60;
                    ?>
                    <?= date("H:i", ($lesson->minutesDuration * 60)) . " " . ($hours == 1 ? "hour" : "hours");?>
                </td>
                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                    <?=Yii::$app->formatter->priceFormat($lesson->clientPrice)?> $
                </td>
                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                    <?=Yii::$app->formatter->priceFormat($lesson->amount + $lesson->fee)?> $
                </td>
            </tr>
        <?php }  ?>
        <?php }?>
        <tr>
            <td style="padding: 10px; text-align: left; border-bottom: 1px solid #000">
                &nbsp;
            </td>
            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #000">
                &nbsp;
            </td>
            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #000">
                &nbsp;
            </td>
            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                &nbsp;
            </td>
            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                &nbsp;
            </td>
            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                Total:
            </td>
            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                <?=  Yii::$app->formatter->getTimestampAsHoursAndMinutes($totalLessonsDuration * 60)?>
            </td>
            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                <?= Yii::$app->formatter->priceFormat($totalEarned) ?> $
            </td>
            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000">
                <?= Yii::$app->formatter->priceFormat($totalSpent) ?> $
            </td>
        </tr>
    </table>
</div>
