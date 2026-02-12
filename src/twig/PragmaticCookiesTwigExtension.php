<?php

namespace pragmatic\cookies\twig;

use pragmatic\cookies\PragmaticCookies;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PragmaticCookiesTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('pragmaticCookieTable', [$this, 'renderCookieTable'], ['is_safe' => ['html']]),
            new TwigFunction('pragmaticHasConsent', [$this, 'hasConsent']),
        ];
    }

    public function renderCookieTable(): string
    {
        return PragmaticCookies::$plugin->consent->renderCookieTable();
    }

    public function hasConsent(string $categoryHandle): bool
    {
        return PragmaticCookies::$plugin->consent->hasConsent($categoryHandle);
    }
}
