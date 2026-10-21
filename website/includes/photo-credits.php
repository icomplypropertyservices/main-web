<?php
/**
 * Third-party photo credits from data/hero-image-credits.json (Commons etc.).
 * Used for the visible credit under a photo and for /pages/photo-credits.
 */
if (!function_exists('icomplyPhotoCredits')) {
    /** Credits keyed by file path (tags stripped). Own photos ("existing site asset") are skipped. */
    function icomplyPhotoCredits(): array {
        static $c = null;
        if ($c !== null) return $c;
        $c = [];
        $rows = json_decode((string) @file_get_contents(SITE_ROOT . '/data/hero-image-credits.json'), true) ?: [];
        foreach ($rows as $r) {
            $src = (string) ($r['source'] ?? '');
            if (!preg_match('~^https?://~', $src)) continue;
            $artist = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</li>', '<li>'], ['; ', ''], (string) ($r['artist'] ?? ''))), ENT_QUOTES, 'UTF-8')), " ;");
            $c[(string) $r['file']] = [
                'artist' => $artist !== '' ? $artist : 'Unknown author',
                'license' => (string) ($r['license'] ?? ''),
                'source' => $src,
                'title' => preg_replace('/^File:/', '', (string) ($r['title'] ?? '')),
            ];
        }
        return $c;
    }
}
if (!function_exists('icomplyPhotoCreditHtml')) {
    /** Visible credit line for a third-party photo, or '' for our own photos. */
    function icomplyPhotoCreditHtml(string $src, string $class = 'mt-1 px-3 pb-2 text-xs text-zinc-600'): string {
        $path = (string) (parse_url($src, PHP_URL_PATH) ?? $src);
        $c = icomplyPhotoCredits()[$path] ?? null;
        if (!$c) return '';
        $h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        return '<p class="photo-credit ' . $h($class) . '" data-b1="photo-credit">Example photo, not an iComply job. Photo: ' . $h($c['artist']) . ', ' . $h($c['license'])
            . ', via <a class="underline" href="' . $h($c['source']) . '" rel="nofollow noopener" target="_blank">Wikimedia Commons</a>. <a class="underline" href="/pages/photo-credits">All photo credits</a></p>';
    }
}

if (!function_exists('icomplyPhotoCreditInject')) {
    /**
     * Add a visible credit to every <img> showing a credited photo listed in
     * ICOMPLY_VISIBLE_CREDIT (by original path or its /opt/ WebP hash).
     * Inside a <figure>: one <figcaption> per figure. Background hero images: a
     * small badge on top. Otherwise: a credit line right after the image.
     */
    function icomplyPhotoCreditInject(string $html): string {
        static $map = null;
        if ($map === null) {
            $map = [];
            $credits = icomplyPhotoCredits();
            $manifest = json_decode((string) @file_get_contents(SITE_ROOT . '/data/image-manifest.json'), true) ?: [];
            foreach (['/assets/images/services/plastering.jpg', '/assets/images/services/dry-lining.jpg'] as $file) {
                if (!isset($credits[$file])) continue;
                $map[$file] = $file;
                foreach (($manifest[$file]['variants'] ?? []) as $v) {
                    if (preg_match('#/opt/([0-9a-f]+)-#', (string) ($v['webp'] ?? ''), $m)) $map['/opt/' . $m[1] . '-'] = $file;
                }
            }
        }
        if ($map === [] || !preg_match('#' . implode('|', array_map(fn($k) => preg_quote($k, '#'), array_keys($map))) . '#', $html)) return $html;
        $out = '';
        $pos = 0;
        $pendingFig = null;
        while (preg_match('#<img\b[^>]*>#', $html, $m, PREG_OFFSET_CAPTURE, $pos)) {
            [$tag, $at] = $m[0];
            $end = $at + strlen($tag);
            $src = preg_match('#\ssrc="([^"]+)"#', $tag, $s) ? $s[1] : '';
            $file = null;
            foreach ($map as $needle => $f) { if (str_contains($src, $needle)) { $file = $f; break; } }
            $out .= substr($html, $pos, $end - $pos);
            $pos = $end;
            if ($file === null) continue;
            if (str_contains(substr($html, $pos, 700), 'photo-credit')) continue; // template already credits it
            $before = substr($html, max(0, $at - 4000), min(4000, $at));
            $openFig = strrpos($before, '<figure');
            $closeFig = strrpos($before, '</figure>');
            if ($openFig !== false && ($closeFig === false || $closeFig < $openFig)) {
                $close = strpos($html, '</figure>', $pos);
                if ($close !== false) {
                    $chunk = substr($html, $pos, $close - $pos);
                    if (!str_contains($chunk, 'data-photo-credit')) {
                        $out .= $chunk . '<figcaption data-photo-credit="1" style="font-size:.75rem;line-height:1.35;color:#444;margin:4px 2px 0">' . strip_tags(icomplyPhotoCreditHtml($file, ''), '<a>') . '</figcaption>';
                        $pos = $close;
                    }
                }
                continue;
            }
            $bg = str_contains($tag, 'absolute') || str_ends_with(rtrim(substr($before, -200)), '<div class="absolute inset-0">');
            $out .= $bg
                ? '<span data-photo-credit="1" style="position:absolute;right:0;bottom:0;z-index:10;max-width:100%;font-size:11px;line-height:1.3;padding:3px 8px;background:rgba(11,31,58,.85);color:#fff">' . str_replace('<a ', '<a style="color:#fff" ', strip_tags(icomplyPhotoCreditHtml($file, ''), '<a>')) . '</span>'
                : '<span data-photo-credit="1" style="display:block;font-size:.75rem;line-height:1.35;color:#444;padding:4px 12px 8px;background:#fff">' . strip_tags(icomplyPhotoCreditHtml($file, ''), '<a>') . '</span>';
        }
        return $out . substr($html, $pos);
    }
}
