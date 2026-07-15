<?php
/**
 * Lightweight SEO helpers used by templates and cron.
 */
if (!function_exists('seoShouldNoindex')) {
    function seoShouldNoindex(): bool {
        // Preview/staging hosts
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        return str_contains($host, 'vercel.app') && !str_contains($host, 'icomplypropertyservices');
    }
}

if (!function_exists('seoLastHealthReport')) {
    /** @return array<string,mixed>|null */
    function seoLastHealthReport(): ?array {
        $f = SITE_ROOT . '/data/seo-health.json';
        if (!is_file($f)) {
            return null;
        }
        $j = json_decode((string)file_get_contents($f), true);
        return is_array($j) ? $j : null;
    }
}
