<?php

namespace pragmatic\cookies\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property string $status
 * @property int $totalPages
 * @property int $pagesScanned
 * @property int $cookiesFound
 * @property string|null $errorMessage
 */
class ScanRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%pragmatic_cookies_scans}}';
    }
}
