<?php

namespace pragmatic\cookies\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property string $visitorId
 * @property string|null $consent
 * @property string|null $ipAddress
 * @property string|null $userAgent
 */
class ConsentLogRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%pragmatic_cookies_consent_logs}}';
    }
}
