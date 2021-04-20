<?php
  /* @var $this \yii\web\View view component instance */
  /* @var $message \yii\mail\MessageInterface the message being composed */
  /* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?= $this->render('../_parts/head-main') ?>
</head>
<body bgcolor="#F3F3F3" style="margin: 0; padding: 0; background-color:#F3F3F3; padding-top: 50px; padding-bottom: 50px; padding-left: 15px; padding-right: 15px;">
    <?php $this->beginBody() ?>

    <!--main wrapper of email-->
    <table cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto; width: 100%; max-width: 600px; font-family: Arial, sans-serif; line-height: 1.5;" width="600">
        <tbody>
        <tr>
            <td>
                <center>
                    <!--[if mso]>
                    <table style="width: 600px;"><tr><td>
                    <![endif]-->

                    <table bgcolor="#FFFFFF" width="100%" border="0" cellpadding="0" cellspacing="0"
                           style="max-width:600px;background: #FFFFFF;color: #333333; font-size: 16px;box-shadow: 0 5px 20px 0 #C5BCCD;">
                        <tr>
                            <td align="center" valign="center">
                                <?= $this->render('../_parts/header', ['padding' => '0 0 25px 0']) ?>

                                <!-- BODY START-->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="body" style="text-align: left; padding-left: 15px; padding-right: 15px;">
                                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                <?= $this->render('../_parts/title', ['title' => $this->params['title'] ?? '']) ?>

                                                <?= $content ?>

                                                <?= $this->render('../_parts/regards-text') ?>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <!-- BODY END-->

                                <?= $this->render('../_parts/footer') ?>

                            </td>
                        </tr>
                    </table>
                    <!--[if mso]>
                    </td></tr></table>
                    <![endif]-->
                </center>
            </td>
        </tr>
        </tbody>
    </table>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
