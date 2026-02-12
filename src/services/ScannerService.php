<?php

namespace pragmatic\cookies\services;

use Craft;
use craft\elements\Entry;
use pragmatic\cookies\models\ScanModel;
use pragmatic\cookies\records\ScanRecord;
use pragmatic\cookies\records\ScanResultRecord;
use yii\base\Component;

class ScannerService extends Component
{
    public function createScan(): ScanModel
    {
        $urls = $this->discoverUrls();

        $record = new ScanRecord();
        $record->status = 'pending';
        $record->totalPages = count($urls);
        $record->pagesScanned = 0;
        $record->cookiesFound = 0;
        $record->save();

        $model = $this->_createModelFromRecord($record);

        return $model;
    }

    public function getScanById(int $id): ?ScanModel
    {
        $record = ScanRecord::findOne($id);

        return $record ? $this->_createModelFromRecord($record) : null;
    }

    public function getAllScans(): array
    {
        $records = ScanRecord::find()
            ->orderBy(['dateCreated' => SORT_DESC])
            ->all();

        return array_map(fn($r) => $this->_createModelFromRecord($r), $records);
    }

    public function getScanResults(int $scanId): array
    {
        return ScanResultRecord::find()
            ->where(['scanId' => $scanId])
            ->orderBy(['cookieName' => SORT_ASC])
            ->all();
    }

    public function updateScanStatus(int $scanId, string $status, array $extra = []): void
    {
        $record = ScanRecord::findOne($scanId);
        if (!$record) {
            return;
        }

        $record->status = $status;

        foreach ($extra as $key => $value) {
            if ($record->hasAttribute($key)) {
                $record->$key = $value;
            }
        }

        $record->save(false);
    }

    public function addScanResult(int $scanId, string $cookieName, ?string $domain, ?string $path, ?string $pageUrl, ?int $cookieId = null): void
    {
        $record = new ScanResultRecord();
        $record->scanId = $scanId;
        $record->cookieName = $cookieName;
        $record->cookieDomain = $domain;
        $record->cookiePath = $path;
        $record->pageUrl = $pageUrl;
        $record->cookieId = $cookieId;
        $record->save();
    }

    public function discoverUrls(): array
    {
        $urls = [];

        // Get site base URL
        $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
        if ($siteUrl) {
            $urls[] = $siteUrl;
        }

        // Discover URLs from live entries
        $entries = Entry::find()
            ->status('live')
            ->uri(':notempty:')
            ->all();

        foreach ($entries as $entry) {
            $url = $entry->getUrl();
            if ($url && !in_array($url, $urls)) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    public function linkResultToCookie(int $resultId, int $cookieId): bool
    {
        $record = ScanResultRecord::findOne($resultId);
        if (!$record) {
            return false;
        }

        $record->cookieId = $cookieId;
        return $record->save(false);
    }

    private function _createModelFromRecord(ScanRecord $record): ScanModel
    {
        $model = new ScanModel();
        $model->id = $record->id;
        $model->status = $record->status;
        $model->totalPages = (int)$record->totalPages;
        $model->pagesScanned = (int)$record->pagesScanned;
        $model->cookiesFound = (int)$record->cookiesFound;
        $model->errorMessage = $record->errorMessage;
        $model->dateCreated = $record->dateCreated;
        $model->dateUpdated = $record->dateUpdated;
        $model->uid = $record->uid;

        return $model;
    }
}
