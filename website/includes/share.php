<?php
/**
 * Social share buttons + Open Graph helpers.
 * Include near end of main content: <?= shareButtonsHtml($pageTitle ?? SITE_NAME) ?>
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * Current page absolute URL (no query string).
 */
function currentPageUrl(): string {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    // Strip /icomply if SITE_URL already includes it — url() handles site root
    $base = rtrim(SITE_URL, '/');
    // Prefer configured SITE_URL + relative path under site
    $sitePath = parse_url(SITE_URL, PHP_URL_PATH) ?: '';
    if ($sitePath !== '' && $sitePath !== '/' && str_starts_with($path, $sitePath)) {
        $rel = substr($path, strlen(rtrim($sitePath, '/'))) ?: '/';
        return $base . ($rel === '' ? '' : $rel);
    }
    // Fallback: full SITE_URL host + path
    $host = parse_url(SITE_URL, PHP_URL_SCHEME) . '://' . parse_url(SITE_URL, PHP_URL_HOST);
    $port = parse_url(SITE_URL, PHP_URL_PORT);
    if ($port) {
        $host .= ':' . $port;
    }
    return $host . $path;
}

/**
 * Social profile URLs from config (empty string = hide).
 * @return array<string,string>
 */
function getSocialLinks(): array {
    $links = [
        'facebook' => defined('SOCIAL_FACEBOOK') ? (string)SOCIAL_FACEBOOK : '',
        'instagram' => defined('SOCIAL_INSTAGRAM') ? (string)SOCIAL_INSTAGRAM : '',
        'linkedin' => defined('SOCIAL_LINKEDIN') ? (string)SOCIAL_LINKEDIN : '',
        'twitter' => defined('SOCIAL_TWITTER') ? (string)SOCIAL_TWITTER : '',
        'youtube' => defined('SOCIAL_YOUTUBE') ? (string)SOCIAL_YOUTUBE : '',
        'tiktok' => defined('SOCIAL_TIKTOK') ? (string)SOCIAL_TIKTOK : '',
        'google' => defined('SOCIAL_GOOGLE') ? (string)SOCIAL_GOOGLE : '',
    ];
    return array_filter($links, fn($u) => $u !== '');
}

/**
 * Share row HTML (Facebook, X/Twitter, LinkedIn, WhatsApp, Email, copy).
 */
function shareButtonsHtml(string $title = '', string $summary = ''): string {
    $url = rawurlencode(currentPageUrl());
    $titleEnc = rawurlencode($title !== '' ? $title : (SITE_NAME . ' — Property Compliance'));
    $summaryEnc = rawurlencode($summary !== '' ? $summary : 'Property compliance specialists serving the North West.');
    $waText = rawurlencode(($title !== '' ? $title . ' — ' : '') . currentPageUrl());

    $btn = 'inline-flex items-center gap-1.5 px-3 py-2 rounded-full text-xs font-semibold border bg-white hover:border-[#ff6b00] transition text-black';

    $html = '<div class="share-bar mt-10 pt-8 border-t" role="group" aria-label="Share this page">';
    $html .= '<div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-3">Share</div>';
    $html .= '<div class="flex flex-wrap gap-2">';

    $html .= '<a class="' . $btn . '" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u=' . $url . '" aria-label="Share on Facebook">Facebook</a>';
    $html .= '<a class="' . $btn . '" target="_blank" rel="noopener noreferrer" href="https://twitter.com/intent/tweet?url=' . $url . '&text=' . $titleEnc . '" aria-label="Share on X">X / Twitter</a>';
    $html .= '<a class="' . $btn . '" target="_blank" rel="noopener noreferrer" href="https://www.linkedin.com/sharing/share-offsite/?url=' . $url . '" aria-label="Share on LinkedIn">LinkedIn</a>';
    $html .= '<a class="' . $btn . '" target="_blank" rel="noopener noreferrer" href="https://wa.me/?text=' . $waText . '" aria-label="Share on WhatsApp">WhatsApp</a>';
    $html .= '<a class="' . $btn . '" href="mailto:?subject=' . $titleEnc . '&body=' . $summaryEnc . '%20' . $url . '" aria-label="Share by email">Email</a>';
    $html .= '<button type="button" class="' . $btn . ' cursor-pointer" data-copy-url="' . htmlspecialchars(currentPageUrl(), ENT_QUOTES, 'UTF-8') . '" onclick="(function(b){navigator.clipboard&&navigator.clipboard.writeText(b.getAttribute(\'data-copy-url\')).then(function(){b.textContent=\'Copied!\';setTimeout(function(){b.textContent=\'Copy link\'},1500)})})(this)" aria-label="Copy link">Copy link</button>';

    $html .= '</div></div>';
    return $html;
}

/**
 * Footer / header social icons row.
 */
function socialIconsHtml(string $variant = 'dark'): string {
    $links = getSocialLinks();
    if (!$links) {
        // Still show WhatsApp as primary social channel
        $links = ['whatsapp' => 'https://wa.me/' . WHATSAPP];
    } else {
        $links['whatsapp'] = 'https://wa.me/' . WHATSAPP;
    }

    $labels = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'twitter' => 'X / Twitter',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'google' => 'Google Business',
        'whatsapp' => 'WhatsApp',
    ];

    if ($variant === 'dark') {
        $cls = 'inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/10 hover:bg-[#ff6b00] text-white text-xs font-bold transition';
    } else {
        $cls = 'inline-flex items-center justify-center w-10 h-10 rounded-full bg-zinc-100 hover:bg-[#ff6b00] hover:text-white text-zinc-700 text-xs font-bold transition';
    }

    $html = '<div class="flex flex-wrap gap-2" role="list" aria-label="Social media">';
    foreach ($links as $key => $href) {
        $label = $labels[$key] ?? ucfirst($key);
        $short = [
            'facebook' => 'f', 'instagram' => 'Ig', 'linkedin' => 'in', 'twitter' => 'X',
            'youtube' => 'YT', 'tiktok' => 'Tk', 'google' => 'G', 'whatsapp' => 'Wa',
        ][$key] ?? substr($label, 0, 2);
        $html .= '<a role="listitem" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" '
            . 'target="_blank" rel="noopener noreferrer me" class="' . $cls . '" '
            . 'title="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '" '
            . 'aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($short, ENT_QUOTES, 'UTF-8') . '</a>';
    }
    $html .= '</div>';
    return $html;
}
