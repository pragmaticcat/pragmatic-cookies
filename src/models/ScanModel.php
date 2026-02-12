<?php

namespace pragmatic\cookies\models;

use craft\base\Model;

class ScanModel extends Model
{
    public ?int $id = null;
    public string $status = 'pending';
    public int $totalPages = 0;
    public int $pagesScanned = 0;
    public int $cookiesFound = 0;
    public ?string $errorMessage = null;
    public ?string $dateCreated = null;
    public ?string $dateUpdated = null;
    public ?string $uid = null;
}
