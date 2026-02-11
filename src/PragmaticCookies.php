<?php

namespace pragmatic\cookies;

use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

class PragmaticCookies extends Plugin
{
    public bool $hasCpSection = true;
    public string $templateRoot = 'src/templates';

    public function init(): void
    {
        parent::init();

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['pragmatic-cookies'] = 'pragmatic-cookies/default/index';
                $event->rules['pragmatic-cookies/general'] = 'pragmatic-cookies/default/general';
                $event->rules['pragmatic-cookies/options'] = 'pragmatic-cookies/default/options';
            }
        );
    }

    public function getCpNavItem(): array
    {
        $item = parent::getCpNavItem();
        $item['label'] = 'Pragmatic';
        $item['subnav'] = [
            'cookies' => [
                'label' => 'Cookies',
                'url' => 'pragmatic-cookies/general',
            ],
        ];

        return $item;
    }
}
