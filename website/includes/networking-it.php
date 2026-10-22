<?php
/**
 * Property networking / Wi-Fi / Bluetooth + IT support pack (P0).
 *
 * SEO lock: icomply-ops/seo/NETWORKING-IT-DUAL-RING-LOCK-2026-10-05.md
 * Data: data/networking-it-pack.json, built by bin/build-networking-it-pack.py.
 * Hubs are /pages/keywords/{slug}, /pages/jobs/{slug} and /pages/services/{slug}.
 * Town pages come from the edge matrix on the dual-ring 269 (no HTML per town).
 * POA only. No attendance-time promises. No Gas Safe wording.
 */
declare(strict_types=1);

/** @return array<string,mixed> */
function icomplyNetworkingItPack(): array
{
    static $pack = null;
    if ($pack !== null) {
        return $pack;
    }
    $root = defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__);
    $file = $root . '/data/networking-it-pack.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
    $pack = is_array($decoded) ? $decoded : [];
    return $pack;
}

/** @return list<string> */
function icomplyNetworkingItServiceSlugs(): array
{
    return array_keys((array)(icomplyNetworkingItPack()['services'] ?? []));
}

function icomplyNetworkingItIsService(string $slug): bool
{
    return in_array($slug, icomplyNetworkingItServiceSlugs(), true);
}

/** @return array<string,array<string,mixed>> */
function icomplyNetworkingItKeywords(): array
{
    $rows = icomplyNetworkingItPack()['keywords'] ?? [];
    return is_array($rows) ? $rows : [];
}

/** @return array<string,array<string,mixed>> */
function icomplyNetworkingItJobs(): array
{
    $rows = icomplyNetworkingItPack()['jobs'] ?? [];
    return is_array($rows) ? $rows : [];
}

function icomplyNetworkingItIsKeyword(string $slug): bool
{
    return isset(icomplyNetworkingItKeywords()[$slug]);
}

/** @return array<string,mixed>|null */
function icomplyNetworkingItServiceHubCopy(string $slug): ?array
{
    $svc = icomplyNetworkingItPack()['services'][$slug] ?? null;
    if (!is_array($svc) || !is_array($svc['hub'] ?? null)) {
        return null;
    }
    return $svc['hub'];
}

/** @return list<array{0:string,1:string}> */
function icomplyNetworkingItServiceHubLinks(string $slug): array
{
    $copy = icomplyNetworkingItServiceHubCopy($slug);
    $out = [];
    foreach ((array)($copy['links'] ?? []) as $link) {
        if (is_array($link) && count($link) >= 2) {
            $out[] = [(string)$link[0], (string)$link[1]];
        }
    }
    return $out;
}

/**
 * Matrix job records for job×town on the dual-ring 269.
 *
 * @return array<string,array{name:string,service:string,gas:bool,nationwide:bool,focus:list<string>}>
 */
function icomplyNetworkingItMatrixJobs(): array
{
    $out = [];
    foreach (icomplyNetworkingItJobs() as $slug => $job) {
        $out[(string)$slug] = [
            'name' => (string)($job['name'] ?? $slug),
            'service' => (string)($job['service'] ?? 'it-support'),
            'gas' => false,
            'nationwide' => false,
            'focus' => array_values(array_map('strval', (array)($job['focus'] ?? []))),
        ];
    }
    return $out;
}

/**
 * Prose sections for keyword hubs (h2 + paragraphs).
 *
 * @param mixed $sections
 */
function icomplyNetworkingItSectionsHtml($sections): string
{
    if (!is_array($sections) || $sections === []) {
        return '';
    }
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $html = '<div class="mt-8 space-y-8" data-pe-slot="q1-keyword-hub-prose">';
    foreach ($sections as $section) {
        if (!is_array($section)) {
            continue;
        }
        $h2 = trim((string)($section['h2'] ?? ''));
        $paras = array_values(array_filter(array_map('strval', (array)($section['p'] ?? [])), static fn($p) => trim($p) !== ''));
        if ($h2 === '' || $paras === []) {
            continue;
        }
        $html .= '<div><h2 class="text-xl md:text-2xl font-bold text-[#061828] tracking-tight">' . $h($h2) . '</h2>';
        foreach ($paras as $p) {
            $html .= '<p class="mt-3 text-base md:text-lg text-zinc-900 leading-relaxed">' . $h($p) . '</p>';
        }
        $html .= '</div>';
    }
    return $html . '</div>';
}

function renderNetworkingItJob(string $slug): void
{
    require_once SITE_ROOT . '/includes/job-article.php';
    $job = icomplyNetworkingItJobs()[$slug] ?? null;
    if (!is_array($job)) {
        http_response_code(404);
        require SITE_ROOT . '/404.php';
        return;
    }
    renderJobArticle($job);
}
