<?php

namespace api\tests\api;

use api\ApiTester;

/**
 * Class ConstantCest
 */
class ConstantCest
{
    public function getConstantsTest(ApiTester $I)
    {
        $I->wantTo('Get Constants');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/api/constants/');
        $I->canSeeResponseCodeIs(200);
    }
}
