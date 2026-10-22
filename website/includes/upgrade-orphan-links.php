<?php
/**
 * Internal links for keyword×town pages that had almost no impressions
 * because nothing on the site pointed at them.
 */
declare(strict_types=1);

function icomplyUpgradeOrphanRows(): array
{
    $file = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/seo-upgrade/orphans.json';
    if (!is_file($file)) {
        return [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return is_array($decoded['rows'] ?? null) ? $decoded['rows'] : [];
}

function icomplyUpgradeOrphanLinksForTown(string $townSlug): string
{
    $townSlug = strtolower(trim($townSlug));
    if ($townSlug === '') {
        return '';
    }
    $items = [];
    foreach (icomplyUpgradeOrphanRows() as $row) {
        if (!is_array($row)) {
            continue;
        }
        if (strtolower((string)($row['town'] ?? '')) !== $townSlug) {
            continue;
        }
        $href = (string)($row['href'] ?? '');
        $label = icomplyUpgradeOrphanLabel($row);
        if ($href === '' || $label === '') {
            continue;
        }
        $items[] = '<li><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    if ($items === []) {
        return '';
    }
    return '<section class="section" data-orphan-links="town">'
        . '<h2>Local guides for this town</h2>'
        . '<ul>' . implode('', $items) . '</ul></section>';
}

function icomplyUpgradeOrphanLabel(array $row): string
{
    $href = (string)($row['href'] ?? '');
    if (preg_match('#/pages/keywords/([a-z0-9-]+)(?:/([a-z0-9-]+))?$#', $href, $match)) {
        if (!function_exists('icomplySpokenLinkLabel')) {
            require_once __DIR__ . '/related.php';
        }
        $name = function_exists('keywordDisplayName') ? keywordDisplayName($match[1]) : '';
        return icomplySpokenLinkLabel($match[1], $name, $match[2] ?? '');
    }
    return trim((string)($row['label'] ?? ''));
}

function icomplyUpgradeOrphanLinksForHub(): string
{
    $items = [];
    foreach (icomplyUpgradeOrphanRows() as $row) {
        if (!is_array($row)) {
            continue;
        }
        $href = (string)($row['href'] ?? '');
        $label = icomplyUpgradeOrphanLabel($row);
        if ($href === '' || $label === '') {
            continue;
        }
        $items[] = '<li><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    if ($items === []) {
        return '';
    }
    return '<section class="section" data-orphan-links="hub" id="local-guides">'
        . '<h2>Local guides linked from this hub</h2>'
        . '<p>These town pages had almost no internal links. They stay on the site as full local pages. Call 07517 806082 for a written quote.</p>'
        . '<ul>' . implode('', $items) . '</ul></section>';
}
