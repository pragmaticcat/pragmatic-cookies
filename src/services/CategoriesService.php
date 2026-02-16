<?php

namespace pragmatic\cookies\services;

use Craft;
use craft\db\Query;
use craft\helpers\Cp;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use pragmatic\cookies\models\CookieCategoryModel;
use pragmatic\cookies\records\CookieCategoryRecord;
use yii\base\Component;
use yii\db\Expression;
use yii\db\Schema;

class CategoriesService extends Component
{
    private const SITE_VALUES_TABLE = '{{%pragmatic_cookies_category_site_values}}';
    private static bool $siteValuesTableReady = false;

    public function getAllCategories(?int $siteId = null): array
    {
        $this->ensureSiteValuesTable();
        $siteId = $this->resolveSiteId($siteId);

        $rows = (new Query())
            ->from(['c' => CookieCategoryRecord::tableName()])
            ->leftJoin(
                ['sv' => self::SITE_VALUES_TABLE],
                '[[sv.categoryId]] = [[c.id]] AND [[sv.siteId]] = :siteId',
                [':siteId' => $siteId]
            )
            ->select([
                'id' => '[[c.id]]',
                'name' => new Expression('COALESCE([[sv.name]], [[c.name]])'),
                'handle' => '[[c.handle]]',
                'description' => new Expression('COALESCE([[sv.description]], [[c.description]])'),
                'isRequired' => '[[c.isRequired]]',
                'sortOrder' => '[[c.sortOrder]]',
                'uid' => '[[c.uid]]',
            ])
            ->orderBy(['sortOrder' => SORT_ASC])
            ->all();

        return array_map(fn(array $row) => $this->_createModelFromRow($row), $rows);
    }

    public function getCategoryById(int $id, ?int $siteId = null): ?CookieCategoryModel
    {
        $this->ensureSiteValuesTable();
        $siteId = $this->resolveSiteId($siteId);

        $row = (new Query())
            ->from(['c' => CookieCategoryRecord::tableName()])
            ->leftJoin(
                ['sv' => self::SITE_VALUES_TABLE],
                '[[sv.categoryId]] = [[c.id]] AND [[sv.siteId]] = :siteId',
                [':siteId' => $siteId]
            )
            ->select([
                'id' => '[[c.id]]',
                'name' => new Expression('COALESCE([[sv.name]], [[c.name]])'),
                'handle' => '[[c.handle]]',
                'description' => new Expression('COALESCE([[sv.description]], [[c.description]])'),
                'isRequired' => '[[c.isRequired]]',
                'sortOrder' => '[[c.sortOrder]]',
                'uid' => '[[c.uid]]',
            ])
            ->where(['c.id' => $id])
            ->one();

        return $row ? $this->_createModelFromRow($row) : null;
    }

    public function getCategoryByHandle(string $handle): ?CookieCategoryModel
    {
        $record = CookieCategoryRecord::findOne(['handle' => $handle]);

        return $record ? $this->_createModelFromRecord($record) : null;
    }

    public function saveCategory(CookieCategoryModel $model, ?int $siteId = null): bool
    {
        $this->ensureSiteValuesTable();
        $siteId = $this->resolveSiteId($siteId);

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

        // Base values act as fallback for sites without localized values.
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

        $now = Db::prepareDateForDb(new \DateTime());
        Craft::$app->getDb()->createCommand()->upsert(self::SITE_VALUES_TABLE, [
            'categoryId' => $record->id,
            'siteId' => $siteId,
            'name' => $model->name,
            'description' => $model->description,
            'dateCreated' => $now,
            'dateUpdated' => $now,
            'uid' => StringHelper::UUID(),
        ], [
            'name' => $model->name,
            'description' => $model->description,
            'dateUpdated' => $now,
        ])->execute();

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

    private function _createModelFromRow(array $row): CookieCategoryModel
    {
        $model = new CookieCategoryModel();
        $model->id = (int)$row['id'];
        $model->name = (string)$row['name'];
        $model->handle = (string)$row['handle'];
        $model->description = $row['description'] !== null ? (string)$row['description'] : null;
        $model->isRequired = (bool)$row['isRequired'];
        $model->sortOrder = (int)$row['sortOrder'];
        $model->uid = (string)$row['uid'];

        return $model;
    }

    private function _createModelFromRecord(CookieCategoryRecord $record): CookieCategoryModel
    {
        return $this->_createModelFromRow([
            'id' => $record->id,
            'name' => $record->name,
            'handle' => $record->handle,
            'description' => $record->description,
            'isRequired' => $record->isRequired,
            'sortOrder' => $record->sortOrder,
            'uid' => $record->uid,
        ]);
    }

    private function resolveSiteId(?int $siteId): int
    {
        if ($siteId) {
            return $siteId;
        }

        $requestedSite = Cp::requestedSite();
        if ($requestedSite) {
            return (int)$requestedSite->id;
        }

        return (int)Craft::$app->getSites()->getCurrentSite()->id;
    }

    private function ensureSiteValuesTable(): void
    {
        if (self::$siteValuesTableReady) {
            return;
        }
        self::$siteValuesTableReady = true;

        $db = Craft::$app->getDb();
        if (!$db->tableExists(self::SITE_VALUES_TABLE)) {
            $db->createCommand()->createTable(self::SITE_VALUES_TABLE, [
                'id' => Schema::TYPE_PK,
                'categoryId' => Schema::TYPE_INTEGER . ' NOT NULL',
                'siteId' => Schema::TYPE_INTEGER . ' NOT NULL',
                'name' => Schema::TYPE_STRING . '(255) NOT NULL',
                'description' => Schema::TYPE_TEXT,
                'dateCreated' => Schema::TYPE_DATETIME . ' NOT NULL',
                'dateUpdated' => Schema::TYPE_DATETIME . ' NOT NULL',
                'uid' => 'char(36) NOT NULL',
            ])->execute();
        }

        try {
            $db->createCommand()->createIndex(
                'pragmatic_cookies_category_site_values_category_site_unique',
                self::SITE_VALUES_TABLE,
                ['categoryId', 'siteId'],
                true
            )->execute();
        } catch (\Throwable) {
        }

        try {
            $db->createCommand()->createIndex(
                'pragmatic_cookies_category_site_values_site_idx',
                self::SITE_VALUES_TABLE,
                ['siteId'],
                false
            )->execute();
        } catch (\Throwable) {
        }

        try {
            $db->createCommand()->addForeignKey(
                'pragmatic_cookies_category_site_values_category_fk',
                self::SITE_VALUES_TABLE,
                ['categoryId'],
                CookieCategoryRecord::tableName(),
                ['id'],
                'CASCADE',
                'CASCADE'
            )->execute();
        } catch (\Throwable) {
        }

        try {
            $db->createCommand()->addForeignKey(
                'pragmatic_cookies_category_site_values_site_fk',
                self::SITE_VALUES_TABLE,
                ['siteId'],
                '{{%sites}}',
                ['id'],
                'CASCADE',
                'CASCADE'
            )->execute();
        } catch (\Throwable) {
        }
    }
}
