<?php

namespace pragmatic\cookies\services;

use Craft;
use pragmatic\cookies\PragmaticCookies;
use pragmatic\cookies\models\AppearanceSettings;
use pragmatic\webtoolkit\domains\cookies\interfaces\CookiesAppearanceProviderInterface;

class CookiesAppearanceProvider implements CookiesAppearanceProviderInterface
{
    public function getAppearanceSettings(): array
    {
        $settings = PragmaticCookies::$plugin->getSettings();

        return [
            'popupLayout' => (string)$settings->popupLayout,
            'popupPosition' => (string)$settings->popupPosition,
            'primaryColor' => (string)$settings->primaryColor,
            'backgroundColor' => (string)$settings->backgroundColor,
            'textColor' => (string)$settings->textColor,
        ];
    }

    public function saveAppearanceSettings(array $input): bool
    {
        $model = new AppearanceSettings();
        $model->setAttributes([
            'popupLayout' => (string)($input['popupLayout'] ?? ''),
            'popupPosition' => (string)($input['popupPosition'] ?? ''),
            'primaryColor' => (string)($input['primaryColor'] ?? ''),
            'backgroundColor' => (string)($input['backgroundColor'] ?? ''),
            'textColor' => (string)($input['textColor'] ?? ''),
        ], false);

        if (!$model->validate()) {
            return false;
        }

        $settings = PragmaticCookies::$plugin->getSettings();
        $settings->popupLayout = $model->popupLayout;
        $settings->popupPosition = $model->popupPosition;
        $settings->primaryColor = $model->primaryColor;
        $settings->backgroundColor = $model->backgroundColor;
        $settings->textColor = $model->textColor;

        return Craft::$app->getPlugins()->savePluginSettings(PragmaticCookies::$plugin, $settings->toArray());
    }

    public function getAppearanceDefaults(): array
    {
        $defaults = new AppearanceSettings();

        return $defaults->toArray();
    }
}
