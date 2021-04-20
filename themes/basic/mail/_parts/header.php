<?php
/**
 * @var string $padding
 */
$padding = isset($padding) ? $padding : '0 0 55px 0';
$link = \common\helpers\Url::getFrontendUrl();
?>

<!-- HEADER -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td style="font-size: 0; width: 150px;">
            <img src="<?=$this->theme->getUrl('img/mail/left-corner.png')?>" alt="left-corner" border="0" width="150">
        </td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="logo" style="text-align: center; padding: <?= $padding ?>;">
            <a href="<?=$link?>" target="_blank" style="font-size: 0">
                <img src="<?=$this->theme->getUrl('img/mail/winit-logo.png')?>" alt="logo" border="0" width="140">
            </a>
        </td>
    </tr>
</table>
