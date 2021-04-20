<?php

namespace modules\account\helpers;

use yii\authclient\OAuthToken;
use yii\httpclient\Response;

class SignupHelper
{
    /**
     * @param string $clientId
     * @param string $accessToken
     *
     * @return array|mixed
     */
    public static function getSocialData(string $clientId, string $accessToken): ?array
    {
        try {
            $collection = \Yii::$app->authClientCollection;
            $client = $collection->getClient($clientId);
            $client->validateAuthState = false;
            $token = new OAuthToken();
            $token->setToken($accessToken);
            $request = $client->createRequest()
                ->setMethod('GET')
                ->setUrl($clientId === 'facebook' ? 'me' : 'userinfo');

            if (isset($client->attributeNames)) {
                $request->setData([
                    'fields' => implode(',', $client->attributeNames),
                ]);
            }

            $client->applyAccessTokenToRequest($request, $token);
            /** @var Response $response */
            $response = $request->send();

            return $response->getData();
        } catch (\Exception $e) {
        };

        return null;
    }
}
