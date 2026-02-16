<?php

namespace pragmatic\cookies\controllers;

use Craft;
use craft\web\Controller;
use pragmatic\cookies\jobs\CookieScanJob;
use pragmatic\cookies\PragmaticCookies;
use yii\web\Response;

class ScanController extends Controller
{
    protected int|bool|array $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $scans = PragmaticCookies::$plugin->scanner->getAllScans();

        return $this->renderTemplate('pragmatic-cookies/scanner/index', [
            'scans' => $scans,
        ]);
    }

    public function actionStart(): Response
    {
        $this->requirePostRequest();

        $scanner = PragmaticCookies::$plugin->scanner;
        $scan = $scanner->createScan();
        $urls = $scanner->discoverUrls();

        Craft::$app->getQueue()->push(new CookieScanJob([
            'scanId' => $scan->id,
            'urls' => $urls,
        ]));

        Craft::$app->getSession()->setNotice('Cookie scan started. Results will appear when complete.');

        return $this->redirectToPostedUrl(null, 'pragmatic-cookies/scanner');
    }

    public function actionResults(int $scanId): Response
    {
        $scan = PragmaticCookies::$plugin->scanner->getScanById($scanId);

        if (!$scan) {
            throw new \yii\web\NotFoundHttpException('Scan not found');
        }

        $results = PragmaticCookies::$plugin->scanner->getScanResults($scanId);
        $cookies = PragmaticCookies::$plugin->cookies->getAllCookies();
        $categories = PragmaticCookies::$plugin->categories->getAllCategories();

        return $this->renderTemplate('pragmatic-cookies/scanner/results', [
            'scan' => $scan,
            'results' => $results,
            'cookies' => $cookies,
            'categories' => $categories,
        ]);
    }

    public function actionStatus(): Response
    {
        $this->requireAcceptsJson();

        $scanId = Craft::$app->getRequest()->getRequiredParam('scanId');
        $scan = PragmaticCookies::$plugin->scanner->getScanById($scanId);

        if (!$scan) {
            return $this->asJson(['error' => 'Scan not found']);
        }

        return $this->asJson([
            'status' => $scan->status,
            'totalPages' => $scan->totalPages,
            'pagesScanned' => $scan->pagesScanned,
            'cookiesFound' => $scan->cookiesFound,
            'errorMessage' => $scan->errorMessage,
        ]);
    }

    public function actionLinkCookie(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $resultId = Craft::$app->getRequest()->getRequiredBodyParam('resultId');
        $cookieId = Craft::$app->getRequest()->getRequiredBodyParam('cookieId');

        $success = PragmaticCookies::$plugin->scanner->linkResultToCookie($resultId, $cookieId);

        return $this->asJson(['success' => $success]);
    }
}
