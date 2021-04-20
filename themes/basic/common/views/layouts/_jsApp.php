<?php

use common\helpers\Url;
use yii\helpers\ArrayHelper;

/**
 * @var \yii\web\View $this
 * @var array $specificJaApp
 */

/**
 * @var $chatModule \modules\chat\Module
 */
$chatModule = Yii::$app->getModule('chat');
$account = Yii::$app->user->identity;

$endpoints = [
    // Params set by index to use in js
    'blockedUsers' => Url::to(['/chat/default/get-blocked-users']),
    'welcomeMessageProcess' => Url::to(['/account/default/welcome-message-process']),
    'search-data' => Url::to(['/account/search/search-data']),
    'countUnreadNotification' => Url::to(['/notification/default/get-count-unread']),
    'addedAccounts' => Url::to(['/account/dashboard-tutor/get-added-accounts']),
    'paymentRefund' => Url::to(['/payment/payment/refund', 'id' => 0]),
    'unverifiedStudents' => Url::to(['/chat/default/get-unverified-students']),
    'reviewCreate' => Url::to(['/account/review/create']),
    'reviewUnAuthCreate' => Url::to(['/account/review/create-un-auth']),
    'reviewMarkRead' => Url::to(['/mark-as-read']),
    'bookTutorPayment' => Url::toRoute('/account/book-tutor/payment'),
    'bookTutorLanding' => Url::toRoute('/account/book-tutor/landing'),
    'bookTutorComplete' => Url::toRoute('/account/book-tutor/complete'),
    'tutoringJobs' => Url::toRoute('/account/job-search/index'),
    'job' => [
        'lead' => Url::to(['/api/job-lead/']),
    ],
    'tutor' => [
        'calcAmount' => Url::to(['/payment/payment/calc-amount']),
        'paymentCreate' => Url::to(['/payment/payment/create']),
        'setHourlyRate' => Url::to(['/account/profile-tutor/set-hourly-rate']),
        'editProfile' => Url::to(['/account/profile-tutor/edit-profile']),
        'addStudent' => Url::to(['/account/dashboard-tutor/add-student', 'chatUserId' => 0]),
        'removeStudent' => Url::to(['/account/dashboard-tutor/remove-student', 'id' => 0]),
        'setAvatar' => Url::to(['/account/profile-tutor/set-avatar']),
        'activeBankAccount' => Url::to(['/account/profile-tutor/active-bank-account', 'id' => 0]),
    ],
    'chat' => [
        'send' => Url::to(['/chat/default/send', 'chatUserId' => 0]),
        'markRead' => Url::to(['/chat/default/mark-read', 'messageId' => '0', 'dialogId' => '1']),
        'getTutorData' => Url::to(['/account/profile-tutor/get-tutor-data', 'chatUserId' => 0]),
        'checkStudentCard' => Url::to(['/account/default/check-student-card', 'chatUserId' => 0]),
    ],
    'auto' => [
        'subject' => Url::to(['/api/auto/subjects/'], true),
        'college' => Url::to(['/api/auto/college/'], true),
    ],
    'countSearchResult' => Url::to(['/count-search-result/']),
    'frontendHost' => env('FRONTEND_URL'),
];

$jaApp = [
    'gmapsApiKey' => env('GOOGLE_MAPS_API_KEY'),
    'constants' => require('_constants.php'),
    'mailDomains' => $chatModule->getMailDomains(),
    'isGuest' => Yii::$app->user->isGuest,
    'global' => [
        'homeUrl' => Yii::$app->homeUrl,
        'dateTimeFormat' => 'MM/DD/YYYY H:mm',
        'isMobile' => (Yii::$app->isMobile ?? false),
        'minOfferValue' => \modules\account\models\JobOffer::MIN_AMOUNT_TUTOR,
        'maxOfferValue' => \modules\account\models\JobOffer::MAX_AMOUNT_TUTOR,
        'defaultPhoneNumberFormatted' => Yii::$app->phoneNumber->getPhoneNumberFormatted(),
    ],
    'stripe' => [
        'publicKey' => Yii::$app->payment->publicKey ?? null,
    ],
    'googleConversions' => Yii::$app->params['googleConversions'] ?? [],
    'endpoints' => $endpoints,
];
if (
    !Yii::$app->user->isGuest
    && ($auth = $account->chat)
) {
    $jaApp['chat'] = [
        'account' => [
            'appId' => (int)$chatModule->application_id,
            'authKey' => $chatModule->auth_key,
            'authSecret' => $chatModule->secret_key,
            'endpointApi' => $chatModule->endpoint_api,
            'endpointChat' => $chatModule->endpoint_chat,
        ],
        'user' => [
            'login' => $auth->login,
            'password' => $auth->password,
            'chatId' => $auth->chatUserId,
            'name' => $account->profile->getShowName(null, false, false),
            'avatarUrl' => $account->getAvatarUrl(),
        ],
        'symbolsForbiddenInPhoneNumber' => $chatModule->getForbiddenSymbolsList(),
        'allowableCountForbiddenSymbols' => $chatModule::ALLOWABLE_COUNT_FORBIDDEN_SYMBOLS_IN_MESSAGE,
        'forbiddenSymbolReplacement' => $chatModule::FORBIDDEN_SYMBOL_REPLACEMENT,
    ];
}

if ($account) {
    $chatModule = Yii::$app->getModule('chat');
    $jaApp['identity'] = [
        'role' => $account->roleId,
        'isTutor' => $account->isTutor(),
        'isPatient' => $account->isPatient(),
        'isCompany' => $account->isCrmAdmin(),
        'isCompanyClient' => $account->isPatient(),
    ];
}

if (!isset($specificJaApp) || !is_array($specificJaApp)) {
    $specificJaApp = [];
}

$jaApp = ArrayHelper::merge($jaApp, $specificJaApp);

echo '<script>var App = ' . \yii\helpers\Json::encode($jaApp) . ';</script>';

if (!Yii::$app->user->isGuest) : ?>
    <?php
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/fetch/2.0.4/fetch.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/promise-polyfill@7/dist/polyfill.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/object-assign-polyfill@0.1.0/index.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/quickblox/2.12.2/quickblox.min.js"></script>';
    ?>
    <script>
        var usersBlocked = [];
        var unverifiedStudents = [];
    </script>
<?php endif; ?>
