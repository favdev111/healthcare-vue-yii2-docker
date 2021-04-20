<?php

namespace common\components;

use common\helpers\UrlFrontend;
use yii\base\Component;
use Yii;

/**
 * Class PhoneRequiredBlocker
 * @package common\components
 */
class PhoneRequiredBlocker extends Component
{

    const DESKTOP_PROFILE = '/profile/';
    const MOBILE_PROFILE = '/profile/personal-info/';

    /**
     * Redirect to profile if user is a student without phone
     * @todo: need refactoring
     */
    public function checkPhoneAndRedirect()
    {
        $app = Yii::$app;
        if ($app->user->isGuest) {
            return true;
        }

        $request = $app->request;
        if ($request->isAjax) {
            return true;
        }

        $allowedUrl = self::DESKTOP_PROFILE;
        $identity = $app->user->identity;
        $compareUrl = UrlFrontend::to($allowedUrl, true);

        if (
            $request->url != $compareUrl
            && $identity->isPatient()
            && !$identity->profile->phoneNumber
        ) {
            $app->response->redirect($allowedUrl);
            return false;
        }

        return true;
    }
}
