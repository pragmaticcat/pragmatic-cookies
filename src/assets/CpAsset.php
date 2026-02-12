<?php

namespace pragmatic\cookies\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset as CraftCpAsset;

class CpAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/cp-dist';
        $this->depends = [CraftCpAsset::class];
        $this->js = ['scanner.js'];

        parent::init();
    }
}
