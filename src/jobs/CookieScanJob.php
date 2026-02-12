<?php

namespace pragmatic\cookies\jobs;

use craft\queue\BaseJob;
use pragmatic\cookies\PragmaticCookies;

class CookieScanJob extends BaseJob
{
    public int $scanId;
    public array $urls = [];

    public function execute($queue): void
    {
        $scanner = PragmaticCookies::$plugin->scanner;
        $cookiesService = PragmaticCookies::$plugin->cookies;

        $scanner->updateScanStatus($this->scanId, 'running');

        $totalPages = count($this->urls);
        $pagesScanned = 0;
        $cookiesFound = 0;

        foreach ($this->urls as $i => $url) {
            try {
                $cookies = $this->_scanUrl($url);

                foreach ($cookies as $cookie) {
                    $cookiesFound++;

                    // Try to match to existing cookie definition
                    $knownCookie = $cookiesService->matchCookieToKnown($cookie['name']);
                    $cookieId = $knownCookie ? $knownCookie->id : null;

                    $scanner->addScanResult(
                        $this->scanId,
                        $cookie['name'],
                        $cookie['domain'] ?? null,
                        $cookie['path'] ?? null,
                        $url,
                        $cookieId,
                    );
                }

                $pagesScanned++;

                $scanner->updateScanStatus($this->scanId, 'running', [
                    'pagesScanned' => $pagesScanned,
                    'cookiesFound' => $cookiesFound,
                ]);

                $this->setProgress($queue, ($i + 1) / $totalPages);
            } catch (\Throwable $e) {
                // Log error but continue scanning other pages
                \Craft::warning("Cookie scan error for URL {$url}: " . $e->getMessage(), __METHOD__);
                $pagesScanned++;
            }
        }

        $scanner->updateScanStatus($this->scanId, 'completed', [
            'pagesScanned' => $pagesScanned,
            'cookiesFound' => $cookiesFound,
        ]);
    }

    protected function defaultDescription(): ?string
    {
        return 'Scanning pages for cookies';
    }

    private function _scanUrl(string $url): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'PragmaticCookies Scanner/1.0',
            CURLOPT_COOKIEJAR => '/dev/null',
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return [];
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerString = substr($response, 0, $headerSize);
        curl_close($ch);

        return $this->_parseSetCookieHeaders($headerString);
    }

    private function _parseSetCookieHeaders(string $headers): array
    {
        $cookies = [];
        $lines = explode("\r\n", $headers);

        foreach ($lines as $line) {
            if (stripos($line, 'Set-Cookie:') === 0) {
                $cookieString = trim(substr($line, 11));
                $cookie = $this->_parseCookieString($cookieString);
                if ($cookie) {
                    $cookies[] = $cookie;
                }
            }
        }

        return $cookies;
    }

    private function _parseCookieString(string $cookieString): ?array
    {
        $parts = explode(';', $cookieString);
        $nameValue = trim($parts[0]);

        $eqPos = strpos($nameValue, '=');
        if ($eqPos === false) {
            return null;
        }

        $name = trim(substr($nameValue, 0, $eqPos));
        if (empty($name)) {
            return null;
        }

        $cookie = [
            'name' => $name,
            'domain' => null,
            'path' => null,
        ];

        for ($i = 1; $i < count($parts); $i++) {
            $attr = trim($parts[$i]);
            if (stripos($attr, 'domain=') === 0) {
                $cookie['domain'] = trim(substr($attr, 7));
            } elseif (stripos($attr, 'path=') === 0) {
                $cookie['path'] = trim(substr($attr, 5));
            }
        }

        return $cookie;
    }
}
