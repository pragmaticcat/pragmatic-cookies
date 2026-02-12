<?php

namespace pragmatic\cookies\controllers;

use Craft;
use craft\web\Controller;
use pragmatic\cookies\PragmaticCookies;
use yii\web\Response;

class ConsentController extends Controller
{
    protected int|bool|array $allowAnonymous = ['save'];

    public function actionSave(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $consentJson = $request->getBodyParam('consent', '{}');
        $visitorId = $request->getBodyParam('visitorId', '');

        $consent = json_decode($consentJson, true);
        if (!is_array($consent)) {
            $consent = [];
        }

        $settings = PragmaticCookies::$plugin->getSettings();

        if ($settings->logConsent === 'true') {
            $ipAddress = $request->getUserIP();
            $userAgent = $request->getUserAgent();

            PragmaticCookies::$plugin->consent->logConsent(
                $visitorId,
                $consent,
                $ipAddress,
                $userAgent,
            );
        }

        return $this->asJson(['success' => true]);
    }
}
