<?php
use common\helpers\Role;
use yii\helpers\Html;

$array = $subjectsActive;
$selectedSubject = null;
//   if need current subject
if (is_numeric($selectedSubjectId) && isset($array[$selectedSubjectId])) {
    $selectedSubject = $array[$selectedSubjectId];
    unset($array[$selectedSubjectId]);
}

if ($selectedSubject) {
    $currentSubject = $selectedSubject;
} else {
    $currentSubject = current($array);
    next($array);
}

$twoSubjects = [];
if (!empty($currentSubject)) {
    $twoSubjects[] = [
        'name' => $currentSubject->subject->name,
        'url' => '',
    ];
}

$secondSubject = current($array);
reset($array);
if ($secondSubject) {
    if (!$currentSubject || $secondSubject->id != $currentSubject->id) {
        $twoSubjects[] = [
            'name' => $secondSubject->subject->name,
            'url' => '',
        ];
    }
}

$city = $account->profile->city;

$h1 = Html::encode(
    $account->getDisplayName()
);

$h2NonEncoded = join(' and ', array_column($twoSubjects, 'name')) . ' tutor';
$h2 = join(' and ', array_column($twoSubjects, 'url')) . ' tutor';

$h2LocNonEncodedCity = $city
    ? ($city->name
        . ', '
        . $city->stateNameShort)
    : '';
$h2LocNonEncoded = $city
    ? ' in ' . $h2LocNonEncodedCity
    : '';
$h2Loc = $h2LocNonEncoded;

$this->title = $account->getDisplayName() . ' - ' . $h2NonEncoded . '' . $h2LocNonEncoded;
$this->registerMetaTag([
    'name' => 'title',
    'content' => $this->title,
]);

$this->registerMetaTag([
    'name' => 'description',
    'content' => 'View '
        . Html::encode($account->getDisplayName())
        . '’s tutor profile on HeyTutor. '
        . Html::encode($account->profile->firstName)
        . (
        $city
            ? (' is located in '
            . $city->name
            . ', '
            . $city->stateNameShort
            . ' and')
            : ''
        )
        . ' can potentially tutor a variety of subjects. Meet our tutors.'
]);

if ($account->isHiddenFromIndexing()) {
    $this->registerMetaTag([
        'name' => 'robots',
        'content' => 'noindex,follow',
    ]);
}

$hideMessageBlock = false;

$signUpBlock = false;
if (Yii::$app->user->isGuest) {
    $signUpBlock = true;
    $hideSignUpBlock = true;
} elseif (Yii::$app->user->identity->roleId == Role::ROLE_SPECIALIST) {
    $hideMessageBlock = true;
}

if (!Yii::$app->user->isGuest && Yii::$app->user->identity->roleId == Role::ROLE_PATIENT) {
    echo '<script>var student = true; var studentEmail = "' . Yii::$app->user->identity->email . '"; var studentZipCode = "' . Yii::$app->user->identity->profile->zipCode . '";</script>';
}

$locationDistance = false;
if (!Yii::$app->user->isGuest) {
    $zipCodeLocation = Yii::$app->user->identity->profile->zipCodeLocation;
    if ($zipCodeLocation) {
        $locationDistance = $profile->locationDistance($zipCodeLocation['latitude'], $zipCodeLocation['longitude']);
    }
}

$tab = Yii::$app->request->get('tab');



$urlParams = [];
if ($data !== null) {
    $urlParams = json_decode($data, true);
    if (isset($urlParams['signUpSubjects'])) {
        $urlParams['subject'] = $urlParams['signUpSubjects'];
    }
    if (isset($urlParams['signUpZipCode'])) {
        $urlParams['zipCode'] = $urlParams['signUpZipCode'];
    }
}
