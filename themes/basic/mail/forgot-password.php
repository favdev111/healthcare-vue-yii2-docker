<?php
/**
 * @var string $subject
 * @var string $link
 */

$this->params['title'] = $subject;
echo $this->render('./_parts/common-text', [
    'text' => 'Please use this link to reset your password.',
    'padding' => '5px 0 0 0'
]);

echo $this->render('./_parts/button-link', ['link' => $link]);
echo $this->render('./_parts/button', ['link' => $link, 'buttonText' => 'Reset now']);
echo $this->render('./_parts/common-text', ['text' => 'If you did not reset your password, please ignore.']);
