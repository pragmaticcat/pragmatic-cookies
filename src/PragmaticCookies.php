<?php

namespace pragmatic\cookies;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use pragmatic\cookies\models\Settings;
use pragmatic\cookies\services\CookiesAppearanceProvider;
use pragmatic\webtoolkit\PragmaticWebToolkit;
use pragmatic\webtoolkit\domains\cookies\events\RegisterCookiesAppearanceProviderEvent;
use pragmatic\webtoolkit\domains\cookies\services\CookiesExtensionRegistry;
use yii\base\Event;

/**
 * @property CookiesAppearanceProvider $appearanceProvider
 */
class PragmaticCookies extends Plugin
{
    public static PragmaticCookies $plugin;

    public bool $hasCpSection = false;
    public bool $hasCpSettings = false;
    public string $schemaVersion = '1.0.0';

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->i18n->translations['pragmatic-cookies'] = [
            'class' => \yii\i18n\PhpMessageSource::class,
            'basePath' => __DIR__ . '/translations',
            'forceTranslation' => true,
        ];

        $this->setComponents([
            'appearanceProvider' => CookiesAppearanceProvider::class,
        ]);

        if (!$this->isToolkitAvailable()) {
            Craft::warning('Pragmatic Cookies requires Pragmatic Web Toolkit to be installed and enabled.', __METHOD__);
            return;
        }

        $this->registerAppearanceProvider();
        $this->maybeImportLegacyAppearanceSettings();
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    public function getCpNavItem(): ?array
    {
        return null;
    }

    private function registerAppearanceProvider(): void
    {
        Event::on(
            CookiesExtensionRegistry::class,
            CookiesExtensionRegistry::EVENT_REGISTER_APPEARANCE_PROVIDER,
            function (RegisterCookiesAppearanceProviderEvent $event) {
                $event->providers[] = CookiesAppearanceProvider::class;
            }
        );
    }

    private function maybeImportLegacyAppearanceSettings(): void
    {
        $settings = $this->getSettings();
        if ($settings->appearanceMigrated) {
            return;
        }

        $defaults = $this->appearanceProvider->getAppearanceDefaults();
        $hasCustomValues = $settings->popupLayout !== $defaults['popupLayout']
            || $settings->popupPosition !== $defaults['popupPosition']
            || $settings->primaryColor !== $defaults['primaryColor']
            || $settings->backgroundColor !== $defaults['backgroundColor']
            || $settings->textColor !== $defaults['textColor'];

        $core = Craft::$app->getPlugins()->getPlugin('pragmatic-web-toolkit');
        if ($core instanceof PragmaticWebToolkit && !$hasCustomValues) {
            $coreCookies = (array)($core->getSettings()->cookies ?? []);

            $settings->popupLayout = (string)($coreCookies['popupLayout'] ?? $settings->popupLayout);
            $settings->popupPosition = (string)($coreCookies['popupPosition'] ?? $settings->popupPosition);
            $settings->primaryColor = (string)($coreCookies['primaryColor'] ?? $settings->primaryColor);
            $settings->backgroundColor = (string)($coreCookies['backgroundColor'] ?? $settings->backgroundColor);
            $settings->textColor = (string)($coreCookies['textColor'] ?? $settings->textColor);
        }

        $settings->appearanceMigrated = true;
        Craft::$app->getPlugins()->savePluginSettings($this, $settings->toArray());
    }

    private function isToolkitAvailable(): bool
    {
        return Craft::$app->getPlugins()->isPluginInstalled('pragmatic-web-toolkit')
            && Craft::$app->getPlugins()->isPluginEnabled('pragmatic-web-toolkit');
    }
}
