<?php
/**
 * Greater Manchester town blurbs. Keys are "{slug}/{town}".
 * Authored rows win over the composed remainder file.
 */
declare(strict_types=1);

/**
 * @return array{h2:string,blurb:string,cta:string}|null
 */
function icomplyGmTownBlurb(string $slug, string $townSlug, string $kind = 'service'): ?array
{
    static $maps = null;
    if ($maps === null) {
        $dir = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/gm-blurbs';
        $load = static function (string $file): array {
            if (!is_file($file)) {
                return [];
            }
            $data = json_decode((string)file_get_contents($file), true);
            return is_array($data) ? $data : [];
        };
        $authored = $load($dir . '/gm-service-town-blurbs.json');
        $remaining = $load($dir . '/gm-service-town-blurbs-remaining.json');
        $maps = [
            'service' => $authored + $remaining,
            'keyword' => $load($dir . '/keyword-gm-blurbs.json'),
        ];
    }
    $key = $slug . '/' . $townSlug;
    $row = $maps[$kind][$key] ?? null;
    if (!is_array($row) || empty($row['blurb'])) {
        return null;
    }
    $strip = static function (string $text): string {
        $text = (string)preg_replace('/\bb\d{4,6}\b/', '', $text);
        $text = (string)preg_replace('/\s{2,}/', ' ', $text);
        $text = (string)preg_replace('/\s+([,.;:])/', '$1', $text);
        return trim($text);
    };
    $blurb = $strip((string)$row['blurb']);
    $h2 = $strip((string)($row['h2'] ?? ''));
    if ($blurb === '') {
        return null;
    }
    if (preg_match('/\bGM\d{2,}[a-z0-9]/i', $blurb) || preg_match('/\b(SK2Base|CityWards|BoltonCentre|WiganPier|SalfordCrescent)\b/', $blurb)) {
        return null;
    }
    if (preg_match('/\bsub-?contract/i', $blurb . ' ' . $h2)) {
        return null;
    }
    if ($kind === 'keyword' && !in_array($townSlug, ['bolton', 'manchester', 'stockport'], true)) {
        return null;
    }
    if ($kind === 'service' && function_exists('icomplyIsGreaterManchesterAreaSlug') && !icomplyIsGreaterManchesterAreaSlug($townSlug)) {
        return null;
    }
    return [
        'h2' => $h2,
        'blurb' => $blurb,
        'cta' => (string)($row['cta'] ?? ''),
    ];
}
