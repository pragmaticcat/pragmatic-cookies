<?php

namespace pragmatic\cookies\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property int $scanId
 * @property string $cookieName
 * @property string|null $cookieDomain
 * @property string|null $cookiePath
 * @property string|null $pageUrl
 * @property int|null $cookieId
 */
class ScanResultRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%pragmatic_cookies_scan_results}}';
    }
}
