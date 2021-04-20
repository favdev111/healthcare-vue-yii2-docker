<?php

/**
 * @var string $link
 * @var string $buttonText
 */
?>

<tr>
    <td class="email-button" style="text-align: center; padding-top: 15px; padding-bottom: 20px;">
        <div>
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?=$link?>" style="height:42px;v-text-anchor:middle;width:220px;" arcsize="60%" stroke="f" fillcolor="#7A27C5">
                <w:anchorlock/>
                <center>
            <![endif]-->
            <a href="<?=$link?>" target="_blank"
               style="background-color:#7A27C5;border-radius:25px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:42px;text-align:center;text-decoration:none;width:220px;-webkit-text-size-adjust:none;">
                <?=$buttonText?>
            </a>
            <!--[if mso]>
            </center>
            </v:roundrect>
            <![endif]-->
        </div>
    </td>
</tr>
