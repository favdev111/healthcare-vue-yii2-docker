<?php
/**
 * @var string $specialistName
 * @var string $date
 */
?>
<div>
    <h4 class="text-center text-uppercase">DOCTOR INDEPENDENT CONTRACTING Agreement</h4>


    <table class="row-divider form-inline--agreement" width="100%">
        <tr>
            <td width="50%" style="padding: 30px 30px 20px;">
                <p class="agreement__title" style="padding-bottom: 10px;">WINIT (“COMPANY”):</p>
                <br class="invisible">
                <small class="agreement__date text-grey" style="color: #999;"><?= $date ?></small>
            </td>
            <td width="50%" style="padding: 30px 30px 20px;">
                <p class="agreement__title"><span>DOCTOR: </span><span id="digit-sign-fullname"><?=$specialistName ?? null?></span></p>
                <br class="invisible">
                <small class="agreement__date text-grey" style="color: #999;"><?= $date ?></small>
            </td>
        </tr>
    </table>
</div>
