<?php
/** Twin of netlify/lib/analytics.js: guarantee every exported page carries the single
 * consent-gated gtag.js loader with gtag('config') for both GA4 properties. Idempotent. */
declare(strict_types=1);

function icomplyGaIds(): array
{
    $a = defined('GA_MEASUREMENT_ID') ? trim((string)GA_MEASUREMENT_ID) : 'G-RN048B7861';
    $b = defined('GA_MEASUREMENT_ID_2') ? trim((string)GA_MEASUREMENT_ID_2) : 'G-G8NTX4JMC9';
    return [$a !== '' ? $a : 'G-RN048B7861', $b !== '' ? $b : 'G-G8NTX4JMC9'];
}

function icomplyInjectAnalytics(string $html): string
{
    [$ga, $ga2] = icomplyGaIds();
    if (stripos($html, '</head>') === false) return $html;
    if (str_contains($html, $ga) && str_contains($html, $ga2)) return $html;
    if (str_contains($html, '__icomplyAnalytics')) {
        $html = preg_replace('/(ga:\s*"G-[A-Z0-9]+",)/', '$1 ga2: ' . json_encode($ga2) . ',', $html, 1) ?? $html;
        return str_replace("if (this.ga) gtag('config', this.ga);", "if (this.ga) gtag('config', this.ga); if (this.ga2 && this.ga2 !== this.ga) gtag('config', this.ga2);", $html);
    }
    $snip = '<script id="icomply-ga">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}'
        . "gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',wait_for_update:500});"
        . 'window.__icomplyAnalytics={ga:' . json_encode($ga) . ',ga2:' . json_encode($ga2) . ',aw:"",gtm:"",load:function(){if(window.__icomplyAnalyticsLoaded)return;window.__icomplyAnalyticsLoaded=true;'
        . "gtag('consent','update',{ad_storage:'granted',ad_user_data:'granted',ad_personalization:'granted',analytics_storage:'granted'});"
        . "var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(this.ga);document.head.appendChild(s);gtag('js',new Date());gtag('config',this.ga);if(this.ga2&&this.ga2!==this.ga)gtag('config',this.ga2);}};"
        . "try{if(localStorage.getItem('icomply_cookie_consent')==='accepted')window.__icomplyAnalytics.load();}catch(e){}</script>";
    return preg_replace('~</head>~i', $snip . '</head>', $html, 1) ?? $html;
}
