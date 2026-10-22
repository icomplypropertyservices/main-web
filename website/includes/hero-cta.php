<?php
/**
 * Sitewide call + WhatsApp row. One component, used under the H1.
 * Phone 07517 806082. Never 07471. Email stays icomplypropertyservices@gmail.com.
 */
declare(strict_types=1);

function icomplyHeroCtaHtml(string $label = 'Property services'): string
{
    $label = trim(preg_replace('/\s+/', ' ', $label) ?? $label);
    if ($label === '') {
        $label = 'Property services';
    }
    if (function_exists('mb_substr')) {
        $label = mb_substr($label, 0, 80);
    } else {
        $label = substr($label, 0, 80);
    }
    $wa = 'https://wa.me/447517806082?text=' . rawurlencode($label) . '%20quote';
    return '<div class="hero-cta" data-cta="hero">'
        . '<a class="call" href="tel:07517806082">Call 07517 806082</a>'
        . '<a class="wa" href="' . htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener">WhatsApp</a>'
        . '</div>';
}

/**
 * Insert the shared CTA immediately after the first H1 when the page does not already have one.
 */
function icomplyInjectHeroCta(string $html, string $label = ''): string
{
    if ($html === '' || !preg_match('/<h1\b/i', $html)) {
        return $html;
    }
    if (str_contains($html, 'data-cta="hero"')) {
        return $html;
    }
    if ($label === '' && preg_match('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $m)) {
        $label = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $label = trim(preg_replace('/\s+/', ' ', $label) ?? $label);
    }
    $cta = icomplyHeroCtaHtml($label !== '' ? $label : 'Property services');
    $out = preg_replace('/<\/h1>/i', '</h1>' . $cta, $html, 1);
    return is_string($out) ? $out : $html;
}
