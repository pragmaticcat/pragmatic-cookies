<?php

namespace pragmatic\cookies\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property int|null $categoryId
 * @property string $name
 * @property string|null $provider
 * @property string|null $description
 * @property string|null $duration
 * @property bool $isRegex
 */
class CookieRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%pragmatic_cookies_cookies}}';
    }
}
