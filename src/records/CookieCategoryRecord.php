<?php

namespace pragmatic\cookies\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property string|null $description
 * @property bool $isRequired
 * @property int $sortOrder
 */
class CookieCategoryRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%pragmatic_cookies_categories}}';
    }
}
