<?php
/**
 * @var \common\models\form\SignatureForm $formModel
 * @var string $signaturePath
 * @var string $ip
 */
?>
<?= $this->render('terms', [
    'amount' => $formModel->amountPaid,
    'address' => $formModel->clientAddress,
    'date' => $formModel->date,
    'ip' => $ip
]);?>
<table style="width: 100%">
    <tr>
        <td></td>
        <td style="text-align: right"><b><?=$formModel->clientFullName?></b></td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: right; padding-top: 10px"><img width="80px" height="60px" src="<?=$signaturePath?>" alt=""></td>
    </tr>
</table>
