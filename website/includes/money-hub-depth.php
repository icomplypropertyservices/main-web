<?php
/**
 * P1-20-2 (SEO FLAW-QUEUE 2026-10-20): extra customer copy + FAQs for money
 * hubs that were under 800 prose words. Data: data/money-hub-depth.json.
 */
declare(strict_types=1);

function icomplyMoneyHubDepth(): array
{
    static $data = null;
    if ($data === null) {
        $file = SITE_ROOT . '/data/money-hub-depth.json';
        $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
        $data = is_array($decoded) ? $decoded : [];
    }
    return $data;
}

/** @return array{sections: list<array>, faq: list<array>} */
function icomplyMoneyHubDepthService(string $slug): array
{
    $row = icomplyMoneyHubDepth()['services'][$slug] ?? [];
    return [
        'sections' => is_array($row['sections'] ?? null) ? $row['sections'] : [],
        'faq' => is_array($row['faq'] ?? null) ? $row['faq'] : [],
    ];
}

/**
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function icomplyMoneyHubDepthApplyKeywords(array $keywords): array
{
    foreach (icomplyMoneyHubDepth()['keywords'] ?? [] as $slug => $add) {
        if (!isset($keywords[$slug]) || !is_array($keywords[$slug]) || !is_array($add)) {
            continue;
        }
        $row = $keywords[$slug];
        $paras = array_values(array_filter(array_map('strval', (array)($add['body_add'] ?? []))));
        if ($paras) {
            $row['body'] = trim((string)($row['body'] ?? '')) . "\n\n" . implode("\n\n", $paras);
        }
        $faq = is_array($row['faq'] ?? null) ? $row['faq'] : [];
        foreach ((array)($add['faq_add'] ?? []) as $pair) {
            if (is_array($pair) && count($pair) >= 2) {
                $faq[] = [(string)$pair[0], (string)$pair[1]];
            }
        }
        $row['faq'] = $faq;
        $keywords[$slug] = $row;
    }
    return $keywords;
}
