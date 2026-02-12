<?php

namespace pragmatic\cookies\services;

use craft\db\Query;
use pragmatic\cookies\models\CookieCategoryModel;
use pragmatic\cookies\records\CookieCategoryRecord;
use yii\base\Component;

class CategoriesService extends Component
{
    public function getAllCategories(): array
    {
        $records = CookieCategoryRecord::find()
            ->orderBy(['sortOrder' => SORT_ASC])
            ->all();

        return array_map(fn($record) => $this->_createModelFromRecord($record), $records);
    }

    public function getCategoryById(int $id): ?CookieCategoryModel
    {
        $record = CookieCategoryRecord::findOne($id);

        return $record ? $this->_createModelFromRecord($record) : null;
    }

    public function getCategoryByHandle(string $handle): ?CookieCategoryModel
    {
        $record = CookieCategoryRecord::findOne(['handle' => $handle]);

        return $record ? $this->_createModelFromRecord($record) : null;
    }

    public function saveCategory(CookieCategoryModel $model): bool
    {
        if (!$model->validate()) {
            return false;
        }

        if ($model->id) {
            $record = CookieCategoryRecord::findOne($model->id);
            if (!$record) {
                return false;
            }
        } else {
            $record = new CookieCategoryRecord();

            // Set sortOrder to next available
            $maxSort = (new Query())
                ->from(CookieCategoryRecord::tableName())
                ->max('sortOrder');
            $model->sortOrder = ($maxSort ?? 0) + 1;
        }

        $record->name = $model->name;
        $record->handle = $model->handle;
        $record->description = $model->description;
        $record->isRequired = $model->isRequired;
        $record->sortOrder = $model->sortOrder;

        if (!$record->save()) {
            $model->addErrors($record->getErrors());
            return false;
        }

        $model->id = $record->id;

        return true;
    }

    public function deleteCategory(int $id): bool
    {
        $record = CookieCategoryRecord::findOne($id);

        if (!$record) {
            return false;
        }

        return (bool)$record->delete();
    }

    public function reorderCategories(array $ids): bool
    {
        foreach ($ids as $order => $id) {
            $record = CookieCategoryRecord::findOne($id);
            if ($record) {
                $record->sortOrder = $order + 1;
                $record->save(false);
            }
        }

        return true;
    }

    private function _createModelFromRecord(CookieCategoryRecord $record): CookieCategoryModel
    {
        $model = new CookieCategoryModel();
        $model->id = $record->id;
        $model->name = $record->name;
        $model->handle = $record->handle;
        $model->description = $record->description;
        $model->isRequired = (bool)$record->isRequired;
        $model->sortOrder = (int)$record->sortOrder;
        $model->uid = $record->uid;

        return $model;
    }
}
