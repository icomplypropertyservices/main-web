<?php
/**
 * P2-20-7 (2026-10-20): fire-family service x town pages carried ~400 prose words.
 * Adds one service-specific paragraph and a seeded pick from a shared pool, so each
 * service x town page clears 800 words while neighbouring towns keep different copy.
 */
function icomplyFireFamilyDepthData(): array
{
    static $data = null;
    if ($data === null) {
        $file = SITE_ROOT . '/data/fire-family-depth.json';
        $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
        $data = is_array($decoded) ? $decoded : ['pool' => [], 'specific' => []];
    }
    return $data;
}

function icomplyFireFamilyDepthHtml(string $serviceSlug, string $serviceName, string $place, int $count = 10): string
{
    $data = icomplyFireFamilyDepthData();
    $pool = array_values(array_filter((array)($data['pool'] ?? []), 'is_string'));
    if ($pool === []) {
        return '';
    }
    $seed = crc32($serviceSlug . '|' . strtolower($place));
    $order = [];
    foreach ($pool as $i => $p) {
        $order[$i] = crc32($seed . ':' . $i);
    }
    asort($order);
    $picked = array_slice(array_keys($order), 0, min($count, count($pool)));
    $fill = static function (string $t) use ($serviceName, $place): string {
        return strtr($t, ['{service}' => $serviceName, '{subject_l}' => strtolower($serviceName), '{place}' => $place]);
    };
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $html = '<section class="space-y-3" data-pe-slot="q1-fire-family-depth"><h3 class="text-xl font-semibold">'
        . $h('Planning ' . strtolower($serviceName) . ' in ' . $place) . '</h3>';
    $specific = (string)(($data['specific'] ?? [])[$serviceSlug] ?? '');
    if ($specific !== '') {
        $html .= '<p class="text-zinc-700 leading-relaxed">' . $h($fill($specific)) . '</p>';
    }
    foreach ($picked as $i) {
        $html .= '<p class="text-zinc-700 leading-relaxed">' . $h($fill($pool[$i])) . '</p>';
    }
    return $html . '</section>';
}
