<?php
/**
 * Lightweight SEO helpers used by templates and cron.
 */
if (!function_exists('seoShouldNoindex')) {
    function seoShouldNoindex(): bool {
        // Preview/staging hosts
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        if (str_contains($host, 'vercel.app')) {
            // Own preview deploys (host contains icomply) stay indexable
            if (str_contains($host, 'icomply')) {
                return false;
            }
            return true;
        }
        return false;
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
