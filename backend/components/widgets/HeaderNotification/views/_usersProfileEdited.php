<?php
use yii\helpers\Url;
use modules\account\models\Account;
use yii\helpers\Inflector;

$message = \Yii::t(
    'app',
    'Profile {count, plural, one{edit} other{edits}}',
    [
        'count' => $count
    ]
);
?>
