<?php

namespace pragmatic\cookies\services;

use pragmatic\cookies\models\CookieModel;
use pragmatic\cookies\PragmaticCookies;
use pragmatic\cookies\records\CookieRecord;
use yii\base\Component;

class CookiesService extends Component
{
    public function getAllCookies(): array
    {
        $records = CookieRecord::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return array_map(fn($record) => $this->_createModelFromRecord($record), $records);
    }

    public function getCookieById(int $id): ?CookieModel
    {
        $record = CookieRecord::findOne($id);

        return $record ? $this->_createModelFromRecord($record) : null;
    }

    public function getCookiesByCategory(int $categoryId): array
    {
        $records = CookieRecord::find()
            ->where(['categoryId' => $categoryId])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return array_map(fn($record) => $this->_createModelFromRecord($record), $records);
    }

    public function getCookiesGroupedByCategory(): array
    {
        $categories = PragmaticCookies::$plugin->categories->getAllCategories();
        $grouped = [];

        foreach ($categories as $category) {
            $cookies = $this->getCookiesByCategory($category->id);
            $grouped[] = [
                'category' => $category,
                'cookies' => $cookies,
            ];
        }

        // Uncategorized cookies
        $uncategorized = CookieRecord::find()
            ->where(['categoryId' => null])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        if (!empty($uncategorized)) {
            $grouped[] = [
                'category' => null,
                'cookies' => array_map(fn($r) => $this->_createModelFromRecord($r), $uncategorized),
            ];
        }

        return $grouped;
    }

    public function saveCookie(CookieModel $model): bool
    {
        if (!$model->validate()) {
            return false;
        }

        if ($model->id) {
            $record = CookieRecord::findOne($model->id);
            if (!$record) {
                return false;
            }
        } else {
            $record = new CookieRecord();
        }

        $record->categoryId = $model->categoryId;
        $record->name = $model->name;
        $record->provider = $model->provider;
        $record->description = $model->description;
        $record->duration = $model->duration;
        $record->isRegex = $model->isRegex;

        if (!$record->save()) {
            $model->addErrors($record->getErrors());
            return false;
        }

        $model->id = $record->id;

        return true;
    }

    public function deleteCookie(int $id): bool
    {
        $record = CookieRecord::findOne($id);

        if (!$record) {
            return false;
        }

        return (bool)$record->delete();
    }

    public function matchCookieToKnown(string $cookieName): ?CookieModel
    {
        // Try exact match first
        $record = CookieRecord::findOne(['name' => $cookieName]);
        if ($record) {
            return $this->_createModelFromRecord($record);
        }

        // Try regex matches
        $regexRecords = CookieRecord::find()
            ->where(['isRegex' => true])
            ->all();

        foreach ($regexRecords as $regexRecord) {
            if (@preg_match('/' . $regexRecord->name . '/', $cookieName)) {
                return $this->_createModelFromRecord($regexRecord);
            }
        }

        return null;
    }

    private function _createModelFromRecord(CookieRecord $record): CookieModel
    {
        $model = new CookieModel();
        $model->id = $record->id;
        $model->categoryId = $record->categoryId;
        $model->name = $record->name;
        $model->provider = $record->provider;
        $model->description = $record->description;
        $model->duration = $record->duration;
        $model->isRegex = (bool)$record->isRegex;
        $model->uid = $record->uid;

        return $model;
    }
}
