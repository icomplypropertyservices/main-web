<?php
/**
 * Shared content helpers entry point.
 *
 * Canonical service copy lives in data/service-meta.json and is exposed via
 * config.php:
 *   - getServiceBlurb(string $slug, bool $short = false): string
 *   - getServiceStandards(string $slug): string
 *   - getServiceMeta(string $slug = ''): array
 *
 * Templates and pages MUST call getServiceBlurb() / getServiceStandards().
 * Do not define local $serviceBlurbs or $serviceStandards arrays.
 *
 * Usage (after config.php, or stand-alone):
 *   require_once SITE_ROOT . '/includes/content.php';
 *   $blurb  = getServiceBlurb($slug);       // full card/hero blurb
 *   $short  = getServiceBlurb($slug, true); // short listing blurb
 *   $std    = getServiceStandards($slug);
 */

if (!function_exists('getServiceBlurb')) {
    require_once dirname(__DIR__) . '/config.php';
}
