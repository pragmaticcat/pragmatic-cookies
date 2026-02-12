<?php

namespace pragmatic\cookies\variables;

use pragmatic\cookies\PragmaticCookies;

class PragmaticCookiesVariable
{
    public function hasConsent(string $categoryHandle): bool
    {
        return PragmaticCookies::$plugin->consent->hasConsent($categoryHandle);
    }

    public function getCategories(): array
    {
        return PragmaticCookies::$plugin->categories->getAllCategories();
    }

    public function getCookies(): array
    {
        return PragmaticCookies::$plugin->cookies->getAllCookies();
    }

    public function getCookiesGroupedByCategory(): array
    {
        return PragmaticCookies::$plugin->cookies->getCookiesGroupedByCategory();
    }

    public function getCurrentConsent(): array
    {
        return PragmaticCookies::$plugin->consent->getCurrentConsent();
    }
}
