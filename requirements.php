<?php

/**
 * Application requirement checker script.
 *
 * In order to run this script use the following console command:
 * php requirements.php
 *
 * In order to run this script from the web, you should copy it to the web root.
 * If you are using Linux you can create a hard link instead, using the following command:
 * ln requirements.php ../requirements.php
 */

if (version_compare(PHP_VERSION, '7.2', '<')) {
    echo 'At least PHP 7.2 is required to run this script!';
    exit(1);
}

require(__DIR__ . '/vendor/autoload.php');

// you may need to adjust this path to the correct Yii framework path
$frameworkPath = dirname(__FILE__) . '/vendor/yiisoft/yii2';

if (!is_dir($frameworkPath)) {
    echo '<h1>Error</h1>';
    echo '<p><strong>The path to yii framework seems to be incorrect.</strong></p>';
    echo '<p>You need to install Yii framework via composer or adjust the framework path in file <abbr title="' . __FILE__ . '">' . basename(__FILE__) . '</abbr>.</p>';
    echo '<p>Please refer to the <abbr title="' . dirname(__FILE__) . '/README.md">README</abbr> on how to install Yii.</p>';
}

require_once($frameworkPath . '/requirements/YiiRequirementChecker.php');
$requirementsChecker = new YiiRequirementChecker();

$gdMemo = $imagickMemo = 'Either GD PHP extension with FreeType support or ImageMagick PHP extension with PNG support is required for image CAPTCHA.';
$gdOK = false;

if (extension_loaded('gd')) {
    $gdInfo = gd_info();
    if (!empty($gdInfo['FreeType Support'])) {
        $gdOK = true;
    } else {
        $gdMemo = 'GD extension should be installed with FreeType support in order to be used for image CAPTCHA.';
    }
}

$dotenvOk = true;
$dotenvErrors = null;
try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $dotenv->required([
        // Database
        'DB_HOST',
        'DB_NAME',
        'DB_USERNAME',
        'DB_PASSWORD',
        // SMTP
        'SMTP_USERNAME',
        'SMTP_PASSWORD',
        'SMTP_HOST',
        'SMTP_PORT',
        'SMTP_ENCRYPTION',
        // Stripe
        'STRIPE_PUBLIC_KEY',
        'STRIPE_PRIVATE_KEY',
        '',
        '',
        '',
        '',
        '',
        '',
    ])->notEmpty();
} catch (\Exception $e) {
    $dotenvOk = false;
    $dotenvErrors = $e->getMessage();
}

/**
 * Adjust requirements according to your application specifics.
 */
$requirements = array(
    [
        'name' => 'PDO extension',
        'mandatory' => true,
        'condition' => extension_loaded('pdo'),
        'by' => 'All DB-related classes',
    ],
    [
        'name' => 'PDO MySQL extension',
        'mandatory' => false,
        'condition' => extension_loaded('pdo_mysql'),
        'by' => 'All DB-related classes',
        'memo' => 'Required for MySQL database.',
    ],
    [
        'name' => 'GD PHP extension with FreeType support',
        'mandatory' => true,
        'condition' => $gdOK,
        'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-captcha-captcha.html">Captcha</a>',
        'memo' => $gdMemo,
    ],
    '.env' => [
        'name' => 'Check .env variables',
        'mandatory' => true,
        'condition' => $dotenvOk,
        'by' => 'All Apps',
        'memo' => $dotenvErrors,
    ],
);

$result = $requirementsChecker->checkYii()->check($requirements);
$result->render();

if (isset($result->getResult()['summary']['errors']) && $result->getResult()['summary']['errors'] > 0) {
    exit(1);
}
