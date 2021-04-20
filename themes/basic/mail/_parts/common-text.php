<?php

/**
 * @var string $text
 * @var string $padding
 */

$padding = isset($padding) ? $padding : '0';
$textAlign = isset($textAlign) ? $textAlign : 'center';
$fontWeight = isset($fontWeight) ? $fontWeight : 'normal';
?>

<tr>
    <td style="text-align: <?= $textAlign ?>; padding: <?= $padding ?>; font-weight: <?= $fontWeight ?>">
        <?=$text?>
    </td>
</tr>
