<?php

namespace pragmatic\cookies\models;

use craft\base\Model;

class AppearanceSettings extends Model
{
    public string $popupLayout = 'bar';
    public string $popupPosition = 'bottom';
    public string $primaryColor = '#2563eb';
    public string $backgroundColor = '#ffffff';
    public string $textColor = '#1f2937';

    public function defineRules(): array
    {
        return [
            [['popupLayout'], 'in', 'range' => ['bar', 'box', 'modal']],
            [['popupPosition'], 'in', 'range' => ['bottom', 'top', 'center']],
            [['primaryColor', 'backgroundColor', 'textColor'], 'match', 'pattern' => '/^#[a-fA-F0-9]{6}$/'],
        ];
    }
}
