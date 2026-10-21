<?php
/**
 * Lightweight SEO helpers used by templates and cron.
 */
if (!function_exists('seoShouldNoindex')) {
    /** Kept for old callers. Nothing is ever noindexed (Jack 2026-10-19, GSC 21 Oct A1). */
    function seoShouldNoindex(): bool {
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
