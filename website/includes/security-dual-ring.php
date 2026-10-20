<?php
/**
 * Security P0 (67 intents × the 269-town local list).
 *
 * 2026-10-20 (SEO PR145 scorecard): keyword hubs and keyword×town pages render
 * through the standard site templates and the edge town matrix (site shell,
 * related-link blocks, town facts, contact widgets). This file only:
 *  - adds the security keywords that main does not have yet (existing live
 *    keyword copy is never overwritten),
 *  - lists keyword×town URLs outside Greater Manchester in the sitemap,
 *  - 301s the job twins (/pages/jobs/{slug} and /pages/jobs/{slug}/{town}),
 *    which are 301 on live today, to the live keyword equivalents.
 */
declare(strict_types=1);

function securityDualRingPack(): array
{
    static $pack = null;
    if ($pack !== null) {
        return $pack;
    }
    $file = SITE_ROOT . '/data/security-dual-ring-p0.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
    $pack = is_array($decoded) ? $decoded : ['intents' => [], 'towns' => []];
    return $pack;
}

function securityDualRingIntent(string $slug): ?array
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    $intent = securityDualRingPack()['intents'][$slug] ?? null;
    return is_array($intent) ? $intent : null;
}

function securityDualRingTown(string $slug): ?array
{
    $slug = function_exists('areaSlug') ? areaSlug($slug) : strtolower($slug);
    $town = securityDualRingPack()['towns'][$slug] ?? null;
    return is_array($town) ? $town : null;
}

function securityDualRingSurfaceOk(string $surface, string $slug): bool
{
    // Only keyword surfaces render; job twins 301 (see securityDualRingJobRedirect).
    return $surface === 'keyword' && securityDualRingIntent($slug) !== null;
}

/** Job slugs that 301 to their keyword twin (maglock-installation keeps its live job hub). */
function securityDualRingJobTwinSlugs(): array
{
    $out = [];
    foreach (securityDualRingPack()['intents'] ?? [] as $slug => $intent) {
        if (is_array($intent) && ($intent['kind'] ?? '') === 'both' && $slug !== 'maglock-installation') {
            $out[] = (string)$slug;
        }
    }
    return $out;
}

/** 301 target for a security job twin path, or null. */
function securityDualRingJobRedirect(string $path): ?string
{
    $path = rtrim(preg_replace('#\.php$#i', '', '/' . ltrim(str_replace('\\', '/', $path), '/')) ?? $path, '/') ?: '/';
    if (!preg_match('#^/pages/jobs/([a-z0-9\-]+)(?:/([a-z0-9\-]+))?$#', $path, $m)) {
        return null;
    }
    if (!in_array($m[1], securityDualRingJobTwinSlugs(), true)) {
        return null;
    }
    if (!isset($m[2]) || $m[2] === '') {
        return '/pages/keywords/' . $m[1];
    }
    return securityDualRingTown($m[2]) !== null ? '/pages/keywords/' . $m[1] . '/' . $m[2] : '/pages/keywords/' . $m[1];
}

/** _redirects lines for the job twin hubs and town pages (first match wins). */
function securityDualRingRedirectLines(): string
{
    $lines = "# Security job twins: 301 to the live keyword hub / keyword x town (SEO PR145, one URL per intent).\n";
    foreach (securityDualRingJobTwinSlugs() as $slug) {
        $lines .= "/pages/jobs/{$slug}          /pages/keywords/{$slug}          301!\n";
        $lines .= "/pages/jobs/{$slug}/         /pages/keywords/{$slug}          301!\n";
        $lines .= "/pages/jobs/{$slug}/:town    /pages/keywords/{$slug}/:town    301!\n";
    }
    return $lines;
}

function securityDualRingHandlesPath(string $path): bool
{
    // keyword×town for the 269 local towns is rendered by the shared matrix.
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return securityDualRingSurfaceOk('keyword', $m[1]) && securityDualRingTown($m[2]) !== null;
    }
    return false;
}

/**
 * Sitemap additions that the Greater Manchester matrix does not already emit.
 *
 * @return list<string>
 */
function securityDualRingSitemapPaths(): array
{
    $pack = securityDualRingPack();
    $gm = [];
    if (!function_exists('icomplyCrawlTownNames')) {
        $crawl = __DIR__ . '/gm-crawl.php';
        if (is_file($crawl)) {
            require_once $crawl;
        }
    }
    if (function_exists('icomplyCrawlTownNames') && function_exists('areaSlug')) {
        foreach (icomplyCrawlTownNames() as $name) {
            $gm[areaSlug((string)$name)] = true;
        }
    }
    $paths = [];
    foreach ($pack['intents'] ?? [] as $slug => $intent) {
        if (!is_array($intent)) {
            continue;
        }
        foreach ($pack['towns'] ?? [] as $townSlug => $_town) {
            if ($townSlug === '' || !is_string($townSlug)) {
                continue;
            }
            if (empty($gm[$townSlug])) {
                $paths[] = '/pages/keywords/' . $slug . '/' . $townSlug;
            }
        }
    }
    return $paths;
}

/**
 * Adds the security P0 keywords that main does not publish yet. Existing live
 * keyword rows (title, meta, body, FAQ) are left exactly as they are.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function securityDualRingApplyKeywords(array $keywords): array
{
    foreach (securityDualRingPack()['intents'] ?? [] as $slug => $intent) {
        if (!is_array($intent) || isset($keywords[$slug])) {
            continue;
        }
        $row = [];
        $row['name'] = (string)($intent['name'] ?? $slug);
        $row['service'] = (string)($intent['service'] ?? 'cctv');
        $row['related'] = (string)($intent['related'] ?? $slug);
        $paragraphs = $intent['paragraphs'] ?? [];
        if (is_array($paragraphs) && isset($paragraphs[0]) && is_string($paragraphs[0])) {
            $row['intro'] = $paragraphs[0];
            $row['body'] = implode("\n\n", array_map('strval', $paragraphs));
        }
        if (!empty($intent['meta_hub'])) {
            $row['meta_desc'] = (string)$intent['meta_hub'];
        }
        if (!empty($intent['title_hub'])) {
            $row['seo_title'] = (string)$intent['title_hub'];
        }
        $row['h1'] = (string)($intent['name'] ?? $slug);
        $faqs = [];
        foreach ($intent['faqs'] ?? [] as $faq) {
            if (is_array($faq) && isset($faq['q'], $faq['a'])) {
                $faqs[] = [(string)$faq['q'], (string)$faq['a']];
            }
        }
        if ($faqs) {
            $row['faq'] = $faqs;
        }
        $row['security_dual_ring_p0'] = true;
        $keywords[$slug] = $row;
    }
    return $keywords;
}

function securityDualRingJobIndexHtml(): string
{
    $h = static function (string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    };
    $html = '<section class="related-links mt-12" aria-label="Security jobs"><h2 class="text-2xl font-semibold text-[#0B1F3A]">Security jobs: CCTV, access control, alarms and door entry</h2>';
    $html .= '<p class="mt-2 text-zinc-700">CCTV, access control, intruder alarms, door entry and intercoms across Greater Manchester and the North West, from our Stockport base.</p><ul class="mt-6 grid sm:grid-cols-2 gap-3">';
    foreach (securityDualRingPack()['intents'] ?? [] as $slug => $intent) {
        if (!is_array($intent) || ($intent['kind'] ?? '') !== 'both') {
            continue;
        }
        $name = (string)($intent['name'] ?? $slug);
        $base = $slug === 'maglock-installation' ? '/pages/jobs/' : '/pages/keywords/';
        $html .= '<li class="border border-zinc-200 rounded-2xl p-4 bg-white">';
        $html .= '<a class="font-semibold text-[#0B1F3A] hover:text-[#ff6b00]" href="' . $h(url($base . $slug)) . '">' . $h($name) . '</a>';
        $html .= '<p class="mt-2 text-sm"><a class="text-[#ff6b00] font-medium" href="' . $h(url($base . $slug . '/stockport')) . '">' . $h($name . ' in Stockport') . '</a></p>';
        $html .= '</li>';
    }
    $html .= '</ul></section>';
    return $html;
}
