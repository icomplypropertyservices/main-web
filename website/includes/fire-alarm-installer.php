<?php
/**
 * Fire-alarm-installer keyword family.
 * Hubs are nationwide. P0 keyword×town uses uk-mainland-towns-10k (population > 10,000).
 * Other keyword families stay on the Greater Manchester matrix.
 */
declare(strict_types=1);

require_once __DIR__ . '/uk-towns.php';

function icomplyFireAlarmInstallerFamily(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = SITE_ROOT . '/data/fire-alarm-installer-family.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $data = is_array($decoded) ? $decoded : [];
    return $data;
}

/** @return list<string> */
function icomplyFireAlarmInstallerHubSlugs(): array
{
    $slugs = icomplyFireAlarmInstallerFamily()['hubs'] ?? [];
    return array_values(array_filter($slugs, 'is_string'));
}

/** @return list<string> */
function icomplyFireAlarmInstallerP0Slugs(): array
{
    $slugs = icomplyFireAlarmInstallerFamily()['p0'] ?? [];
    return array_values(array_filter($slugs, 'is_string'));
}

function icomplyFireAlarmInstallerIsFamily(string $slug): bool
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    return in_array($slug, icomplyFireAlarmInstallerHubSlugs(), true);
}

function icomplyFireAlarmInstallerIsP0(string $slug): bool
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    return in_array($slug, icomplyFireAlarmInstallerP0Slugs(), true);
}

function icomplyFireAlarmInstallerNationwidePath(string $path): bool
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';
    if (!preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return false;
    }
    return icomplyFireAlarmInstallerIsP0($m[1]) && icomplyUkTownBySlug($m[2]) !== null;
}

/**
 * P0 keyword × mainland town paths that are not already in the GM place set.
 *
 * @param array<string,mixed> $gmPlaces
 * @return list<string>
 */
function icomplyFireAlarmInstallerExtraTownPaths(array $gmPlaces = []): array
{
    $paths = [];
    foreach (icomplyFireAlarmInstallerP0Slugs() as $keyword) {
        foreach (icomplyUkTowns() as $town) {
            $slug = (string)($town['slug'] ?? '');
            if ($slug === '' || isset($gmPlaces[$slug])) {
                continue;
            }
            $paths[] = '/pages/keywords/' . $keyword . '/' . $slug;
        }
    }
    return $paths;
}

function icomplyFireAlarmInstallerNap(): string
{
    return '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE';
}

function icomplyFireAlarmInstallerDuty(): string
{
    return 'Design, installation and commissioning follow BS 5839. Competent fire alarm engineers carry out the visit. iComply does not claim BAFE or NSI badges.';
}

/**
 * @param list<string> $list
 */
function icomplyFireAlarmInstallerPick(array $list, string $seed): string
{
    if ($list === []) {
        return '';
    }
    $h = (int)sprintf('%u', crc32($seed));
    return $list[$h % count($list)];
}

/**
 * Nationwide P0 keyword × town page. Matrix-style HTML with a town paragraph and parent links.
 */
function icomplyRenderFireAlarmInstallerTown(string $keywordSlug, string $townSlug): void
{
    $keywordSlug = function_exists('keywordSlug') ? keywordSlug($keywordSlug) : strtolower($keywordSlug);
    $town = icomplyUkTownBySlug($townSlug);
    $keywords = function_exists('getMajorKeywords') ? getMajorKeywords() : [];
    if ($town === null || !isset($keywords[$keywordSlug]) || !icomplyFireAlarmInstallerIsP0($keywordSlug)) {
        http_response_code(404);
        echo 'Keyword town page not found';
        return;
    }
    $meta = $keywords[$keywordSlug];
    $name = (string)($meta['name'] ?? $keywordSlug);
    $relatedSlug = function_exists('keywordSlug')
        ? keywordSlug((string)($meta['related'] ?? 'fire-alarm-installation'))
        : (string)($meta['related'] ?? 'fire-alarm-installation');
    $relatedName = (string)($keywords[$relatedSlug]['name'] ?? 'Fire alarm installation');
    if (!function_exists('icomplyTownContext')) {
        require_once __DIR__ . '/town-service-pages.php';
    }
    $ctx = icomplyTownContext($town);
    $intro = trim((string)($meta['intro'] ?? ''));
    $openings = [
        $name . ' in ' . $ctx['name'] . ' is scoped to the building, not copied from a national script with the town name swapped in.',
        'A ' . $ctx['name'] . ' enquiry for ' . $name . ' starts with the panel, the category and who has to keep the logbook.',
        $ctx['name'] . ' sits in ' . $ctx['county'] . '. ' . $name . ' is arranged from Stockport once the BS 5839 scope is written down.',
        'People searching ' . $name . ' in ' . $ctx['name'] . ' usually need a survey, not a catalogue price.',
        'For ' . $ctx['band'] . ' premises in ' . $ctx['name'] . ', ' . $name . ' follows the fire strategy already in the building.',
        $ctx['region'] . ' travel to ' . $ctx['name'] . ' is part of the quote for ' . $name . '. It is not a hidden extra.',
    ];
    $opening = icomplyFireAlarmInstallerPick($openings, $keywordSlug . '|' . $ctx['slug']);
    $local = $ctx['name'] . ' is in ' . $ctx['county'] . ', ' . $ctx['region'] . ', ' . $ctx['nation']
        . '. Published population is ' . $ctx['pop']
        . '. Nearby mainland towns include ' . $ctx['near']
        . '. The workshop is about ' . $ctx['miles'] . ' miles away at ' . icomplyFireAlarmInstallerNap()
        . '. The quote for ' . $name . ' in ' . $ctx['name'] . ' is price on application after scope.';
    $title = $name . ' in ' . $ctx['name'] . ' | Fire alarms | iComply';
    $desc = $name . ' in ' . $ctx['name'] . ', ' . $ctx['region'] . '. BS 5839 design, installation and commissioning. Price on application.';
    if (strlen($desc) > 158) {
        $desc = substr($desc, 0, 155) . '…';
    }
    $canonical = function_exists('url')
        ? url('/pages/keywords/' . $keywordSlug . '/' . $ctx['slug'])
        : '/pages/keywords/' . $keywordSlug . '/' . $ctx['slug'];
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $hrefs = [];
    $add = static function (string $href, string $label) use (&$hrefs, $h): string {
        $hrefs[$href] = $label;
        return '<a href="' . $h($href) . '">' . $h($label) . '</a>';
    };

    $gmNames = function_exists('icomplyCrawlTownNames') ? icomplyCrawlTownNames() : [];
    $blocks = [];
    $blocks[] = '<h2>Hubs</h2><ul>'
        . '<li>' . $add('/pages/keywords/' . $keywordSlug, $name . ' guide') . '</li>'
        . '<li>' . $add('/pages/services/fire-alarms', 'Fire alarms') . '</li>'
        . '<li>' . $add('/pages/keywords/' . $relatedSlug, $relatedName) . '</li>'
        . '<li>' . $add('/pages/keywords', 'All keyword guides') . '</li>'
        . '<li>' . $add('/contact', 'Request a quote') . '</li>'
        . '</ul>';
    if (function_exists('icomplyIsGreaterManchesterAreaSlug') && icomplyIsGreaterManchesterAreaSlug($ctx['slug'])) {
        $blocks[] = '<p>' . $add('/pages/areas/' . $ctx['slug'], 'Property services in ' . $ctx['name']) . '</p>';
    } else {
        $blocks[] = '<p>' . $add('/pages/areas/stockport', 'Stockport area hub') . '</p>';
    }

    $sibling = '<h2>Related fire alarm guides</h2><ul>';
    foreach (icomplyFireAlarmInstallerHubSlugs() as $slug) {
        if ($slug === $keywordSlug || !isset($keywords[$slug])) {
            continue;
        }
        $label = (string)($keywords[$slug]['name'] ?? $slug);
        $sibling .= '<li>' . $add('/pages/keywords/' . $slug, $label) . '</li>';
    }
    $sibling .= '</ul>';
    $blocks[] = $sibling;

    $nearHtml = '<h2>Nearby towns for ' . $h($name) . '</h2><ul>';
    foreach (icomplyUkTownNeighbours($ctx['slug'], 12) as $near) {
        $nearHtml .= '<li>' . $add(
            '/pages/keywords/' . $keywordSlug . '/' . $near['slug'],
            $name . ' in ' . $near['name']
        ) . '</li>';
    }
    $nearHtml .= '</ul>';
    $blocks[] = $nearHtml;

    $gmHtml = '<h2>Same work across Greater Manchester</h2><ul>';
    foreach ($gmNames as $gmName) {
        $gmSlug = function_exists('areaSlug') ? areaSlug((string)$gmName) : strtolower((string)$gmName);
        if ($gmSlug === '' || $gmSlug === $ctx['slug']) {
            continue;
        }
        $gmHtml .= '<li>' . $add(
            '/pages/keywords/' . $keywordSlug . '/' . $gmSlug,
            $name . ' in ' . $gmName
        ) . '</li>';
    }
    $gmHtml .= '</ul>';
    $blocks[] = $gmHtml;

    $blocks[] = '<h2>Quotes and guides</h2><ul>'
        . '<li>' . $add('/pages/jobs/fire-alarms', 'Fire alarm jobs') . '</li>'
        . '<li>' . $add('/pages/care-homes', 'Care homes') . '</li>'
        . '<li>' . $add('/pages/landlords', 'Landlord compliance') . '</li>'
        . '<li>' . $add('/pages/commercial', 'Commercial property services') . '</li>'
        . '<li>' . $add('/pages/packages', 'Compliance packages') . '</li>'
        . '<li>' . $add('/pages/resources/fire-alarm-servicing', 'Fire alarm servicing guide') . '</li>'
        . '<li>' . $add('/pages/areas', 'Greater Manchester areas') . '</li>'
        . '</ul>';

    $focus = $meta['focus_points'] ?? [];
    $focusHtml = '';
    if (is_array($focus)) {
        foreach ($focus as $point) {
            $focusHtml .= '<li>' . $h((string)$point) . '</li>';
        }
    }

    echo '<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . $h($title) . '</title>'
        . '<meta name="description" content="' . $h($desc) . '">'
        . '<meta name="robots" content="index, follow">'
        . '<link rel="canonical" href="' . $h($canonical) . '">'
        . '</head><body>'
        . '<header><p>'
        . $add('/', 'iComply Property Services')
        . ' · ' . $add('/pages/services', 'Services')
        . ' · ' . $add('/pages/keywords', 'Keywords')
        . ' · ' . $add('/pages/areas', 'Areas')
        . ' · ' . $add('/contact', 'Contact')
        . '</p></header><main>'
        . '<nav aria-label="Breadcrumb">'
        . $add('/', 'Home') . ' / '
        . $add('/pages/keywords/' . $keywordSlug, $name) . ' / '
        . '<span>' . $h($name . ' in ' . $ctx['name']) . '</span></nav>'
        . '<h1>' . $h($name . ' in ' . $ctx['name']) . '</h1>'
        . '<article id="local-copy">'
        . '<p>' . $h($opening) . '</p>'
        . '<p>' . $h($local) . '</p>'
        . ($intro !== '' ? '<p>' . $h($intro) . '</p>' : '')
        . '<p>' . $h(icomplyFireAlarmInstallerDuty()) . ' The quote is price on application. There is no catalogue fee on this page.</p>'
        . '<p>Workshop address: ' . $h(icomplyFireAlarmInstallerNap()) . '. Phone 07517806082.</p>'
        . '<h2>What the visit covers in ' . $h($ctx['name']) . '</h2><ul>' . $focusHtml . '</ul>'
        . implode('', $blocks)
        . '</article></main></body></html>';
}
