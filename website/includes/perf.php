<?php
/**
 * Public-page performance helpers.
 *
 * Replaces local service/keyword/hero <img> tags with resized WebP srcset
 * entries from data/image-manifest.json, and fills in lazy-loading,
 * dimensions, and fetchpriority when templates omit them.
 */
declare(strict_types=1);

function icomplyPerfManifest(): array
{
    static $manifest = null;
    if ($manifest !== null) {
        return $manifest;
    }
    $path = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/image-manifest.json';
    if (!is_file($path)) {
        $manifest = [];
        return $manifest;
    }
    $decoded = json_decode((string)file_get_contents($path), true);
    $manifest = is_array($decoded) ? $decoded : [];
    return $manifest;
}

function icomplyPerfRel(string $src): string
{
    $src = html_entity_decode(trim($src), ENT_QUOTES, 'UTF-8');
    if ($src === '') {
        return '';
    }
    $path = parse_url($src, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        $path = $src;
    }
    $pos = strpos($path, '/assets/images/');
    if ($pos === false) {
        return '';
    }
    return substr($path, $pos);
}

/** @param array<string,string> $attrs */
function icomplyPerfSizes(array $attrs): string
{
    $class = $attrs['class'] ?? '';
    if (str_contains($class, 'absolute') || preg_match('/\bopacity-\d+/', $class)) {
        return '100vw';
    }
    // Card thumbs use h-full/object-cover inside a fixed-height frame, or an
    // explicit h-* utility. The frame height is not on the <img> itself.
    if (preg_match('/\b(h-24|h-28|h-32|h-36|h-40|h-44|h-48|h-80|h-full)\b/', $class)) {
        return '(min-width: 1280px) 300px, (min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw';
    }
    if (($attrs['loading'] ?? '') === 'eager') {
        return '(min-width: 1024px) 50vw, 100vw';
    }
    return '(min-width: 1024px) 480px, 100vw';
}

/**
 * @param array<string,string> $attrs
 */
function icomplyPerfRenderTag(string $tag, array $attrs): string
{
    $selfClose = str_ends_with(rtrim($tag), '/>');
    $parts = [];
    foreach ($attrs as $name => $value) {
        if ($value === '' && $name !== 'alt') {
            continue;
        }
        $parts[] = $name . '="' . htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '"';
    }
    return '<img ' . implode(' ', $parts) . ($selfClose ? ' />' : '>');
}

/**
 * @param array<string,true> $seen
 */
function icomplyPerfRewriteImg(string $tag, bool &$gaveHigh, array &$seen): string
{
    if (stripos($tag, 'data-perf=') !== false) {
        return $tag;
    }
    if (!preg_match_all('/([:\w-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+))/', $tag, $matches, PREG_SET_ORDER)) {
        return $tag;
    }
    $attrs = [];
    foreach ($matches as $one) {
        $name = strtolower($one[1]);
        $raw = $one[2] !== '' ? $one[2] : ($one[3] !== '' ? $one[3] : ($one[4] ?? ''));
        $attrs[$name] = html_entity_decode((string)$raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    if (!isset($attrs['src']) || $attrs['src'] === '') {
        return $tag;
    }

    $rel = icomplyPerfRel($attrs['src']);
    $widthAttr = (int)($attrs['width'] ?? 0);
    $isLogo = $widthAttr > 0 && $widthAttr <= 64;
    $manifest = $rel !== '' ? (icomplyPerfManifest()[$rel] ?? null) : null;

    if (!is_array($manifest) || empty($manifest['variants']) || !is_array($manifest['variants'])) {
        if ($isLogo || $rel === '' || str_ends_with(strtolower($rel), '.svg')) {
            return $tag;
        }
        if (!isset($attrs['loading'])) {
            $attrs['loading'] = 'lazy';
        }
        if (!isset($attrs['decoding'])) {
            $attrs['decoding'] = 'async';
        }
        $attrs['data-perf'] = '1';
        return icomplyPerfRenderTag($tag, $attrs);
    }

    $variants = array_values(array_filter($manifest['variants'], static function ($v) {
        return is_array($v) && !empty($v['webp']) && !empty($v['w']);
    }));
    if (!$variants) {
        return $tag;
    }
    usort($variants, static function ($a, $b) {
        return ((int)$a['w']) <=> ((int)$b['w']);
    });
    $largest = $variants[count($variants) - 1];
    $srcset = [];
    foreach ($variants as $variant) {
        $srcset[] = assetUrl((string)$variant['webp']) . ' ' . (int)$variant['w'] . 'w';
    }
    $attrs['src'] = assetUrl((string)$largest['webp']);
    $attrs['srcset'] = implode(', ', $srcset);
    if (!isset($attrs['loading'])) {
        $attrs['loading'] = 'lazy';
    }
    if ($rel !== '' && isset($seen[$rel]) && $attrs['loading'] === 'eager') {
        $attrs['loading'] = 'lazy';
    }
    $seen[$rel] = true;
    $attrs['sizes'] = icomplyPerfSizes($attrs);
    $attrs['width'] = (string)max(1, (int)($manifest['width'] ?? $largest['w']));
    $attrs['height'] = (string)max(1, (int)($manifest['height'] ?? $largest['h'] ?? 1));
    if (!isset($attrs['decoding'])) {
        $attrs['decoding'] = $attrs['loading'] === 'eager' ? 'auto' : 'async';
    }
    if ($attrs['loading'] === 'eager' && !$gaveHigh) {
        $attrs['fetchpriority'] = 'high';
        $gaveHigh = true;
    } elseif (!isset($attrs['fetchpriority'])) {
        $attrs['fetchpriority'] = 'low';
    }
    if (!isset($attrs['onerror']) || $attrs['onerror'] === '') {
        $fallback = assetUrl($rel);
        $attrs['onerror'] = "this.onerror=null;this.src='" . $fallback . "'";
    }
    $attrs['data-perf'] = '1';
    return icomplyPerfRenderTag($tag, $attrs);
}

function icomplyPerfRewriteHtml(string $html): string
{
    if ($html === '' || stripos($html, '<img') === false) {
        return $html;
    }
    $gaveHigh = false;
    $seen = [];
    $rewritten = preg_replace_callback(
        '/<img\b[^>]*>/i',
        static function (array $match) use (&$gaveHigh, &$seen): string {
            return icomplyPerfRewriteImg($match[0], $gaveHigh, $seen);
        },
        $html
    );
    return is_string($rewritten) ? $rewritten : $html;
}
