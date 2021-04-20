<?php
/**
 * @var \modules\account\models\Token $link
 */

echo $this->render('./_parts/common-text', [
    'text' => 'Please verify your email to secure your account and receive important information from us.',
    'padding' => '5px 0 0 0'
]);
echo $this->render('./_parts/button-link', ['link' => $link]);
echo $this->render('./_parts/button', ['link' => $link, 'buttonText' => 'Verify now']);
echo $this->render('./_parts/common-text', ['text' => 'If you did not request to set up an account, please ignore.']);
