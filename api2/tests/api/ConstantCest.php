<?php

namespace api2\tests\api;

use api2\ApiTester;

/**
 * Class ConstantCest
 */
class ConstantCest
{
    public function getConstantsTest(ApiTester $I)
    {
        $I->wantTo('Get Constants');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('X-Platform', 'ios');
        $I->haveHttpHeader('X-Device-Token', \Yii::$app->security->generateRandomString(152));
        $I->sendGET('/api/v1/constants/');
        $I->canSeeResponseCodeIs(200);
    }
}
