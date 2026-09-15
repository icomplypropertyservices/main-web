<?php
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}
$services = getServices();
$areas = getAreas();
$rawPageTitle = trim((string)($pageTitle ?? SITE_NAME));
if ($rawPageTitle === '') {
    $rawPageTitle = SITE_NAME;
}
$hasBrandInTitle = (stripos($rawPageTitle, 'Icomply') !== false)
    || (stripos($rawPageTitle, (string)SITE_NAME) !== false);
$documentTitle = $hasBrandInTitle ? $rawPageTitle : ($rawPageTitle . ' | Icomply Property Services');
$pageTitleSafe = htmlspecialchars($documentTitle, ENT_QUOTES, 'UTF-8');
$ogTitleSafe = htmlspecialchars($rawPageTitle, ENT_QUOTES, 'UTF-8');
$metaDescSafe = htmlspecialchars(
    $metaDesc ?? 'Icomply Property Services — property maintenance and compliance across Greater Manchester and the North West: EICR, gas, fire, kitchens, renovations, CCTV, Legionella and asbestos. Offerton, Stockport SK2 5DE.',
    ENT_QUOTES,
    'UTF-8'
);
$metaKeywordsSafe = htmlspecialchars(
    $metaKeywords ?? 'property maintenance, landlord compliance, EICR, gas safety, fire risk assessment, kitchens, renovations, CCTV, legionella, asbestos, Stockport, Manchester',
    ENT_QUOTES,
    'UTF-8'
);
$homeUrl = rtrim(SITE_URL, '/') . '/';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0B1F3A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <?php if (defined('GOOGLE_SITE_VERIFICATION') && GOOGLE_SITE_VERIFICATION !== ''): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars(GOOGLE_SITE_VERIFICATION, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php
    require_once __DIR__ . '/seo-monitor.php';
    $robotsContent = $metaRobots ?? 'index, follow, max-image-preview:large';
    if (function_exists('seoShouldNoindex') && seoShouldNoindex()) {
        $robotsContent = 'noindex,nofollow';
    }
    ?>
    <meta name="robots" content="<?= htmlspecialchars($robotsContent, ENT_QUOTES, 'UTF-8') ?>">
    <?php
    if (empty($canonicalUrl)) {
        require_once __DIR__ . '/share.php';
        $canonicalUrl = function_exists('currentPageUrl') ? currentPageUrl() : url('/');
    }
    ?>
    <link rel="sitemap" type="application/xml" title="Sitemap" href="<?= htmlspecialchars(url('/sitemap.xml'), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= $pageTitleSafe ?></title>
    <meta name="description" content="<?= $metaDescSafe ?>">
    <meta name="keywords" content="<?= $metaKeywordsSafe ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_GB">
    <meta property="og:locale:alternate" content="en_US">
    <meta property="og:site_name" content="<?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= $ogTitleSafe ?>">
    <meta property="og:description" content="<?= $metaDescSafe ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php
    // Default OG image when page does not set $ogImage
    if (empty($ogImage)) {
        $ogImage = url('/assets/images/og-image.jpg');
        if (!is_file(SITE_ROOT . '/assets/images/og-image.jpg')) {
            $ogImage = url('/assets/images/services/fire-alarms.jpg');
        }
    }
    $ogImageSafe = htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8');
    $ogAltSafe = htmlspecialchars(
        $ogImageAlt ?? ($rawPageTitle . ' — Icomply Property Services, Stockport and the North West'),
        ENT_QUOTES,
        'UTF-8'
    );
    ?>
    <meta property="og:image" content="<?= $ogImageSafe ?>">
    <meta property="og:image:secure_url" content="<?= $ogImageSafe ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= $ogAltSafe ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $ogTitleSafe ?>">
    <meta name="twitter:description" content="<?= $metaDescSafe ?>">
    <meta name="twitter:image" content="<?= $ogImageSafe ?>">
    <meta name="twitter:image:alt" content="<?= $ogAltSafe ?>">
    <?php if (defined('SOCIAL_TWITTER') && SOCIAL_TWITTER !== ''): ?>
    <?php
    $twitterSite = SOCIAL_TWITTER;
    if (preg_match('~(?:twitter\.com|x\.com)/@?([A-Za-z0-9_]+)~', $twitterSite, $twMatch)) {
        $twitterSite = '@' . $twMatch[1];
    } elseif ($twitterSite[0] !== '@') {
        $twitterSite = '@' . ltrim($twitterSite, '@');
    }
    ?>
    <meta name="twitter:site" content="<?= htmlspecialchars($twitterSite, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" href="<?= htmlspecialchars(assetUrl('/favicon.ico'), ENT_QUOTES, 'UTF-8') ?>" sizes="any">
    <link rel="icon" href="<?= htmlspecialchars(assetUrl('/assets/images/favicon.ico'), ENT_QUOTES, 'UTF-8') ?>" sizes="any">
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars(assetUrl('/assets/images/favicon.svg'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= htmlspecialchars(assetUrl('/assets/images/favicon-16.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars(assetUrl('/assets/images/favicon-32.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= htmlspecialchars(assetUrl('/assets/images/android-chrome-192.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= htmlspecialchars(assetUrl('/assets/images/android-chrome-512.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars(assetUrl('/assets/images/apple-touch-icon.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="manifest" href="<?= htmlspecialchars(assetUrl('/manifest.webmanifest'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="manifest" href="<?= htmlspecialchars(assetUrl('/site.webmanifest'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="manifest" href="<?= htmlspecialchars(assetUrl('/manifest.json'), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="msapplication-TileColor" content="#0B1F3A">
    <meta name="msapplication-TileImage" content="<?= htmlspecialchars(assetUrl('/assets/images/android-chrome-192.png'), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="<?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="geo.region" content="GB-MAN">
    <meta name="geo.placename" content="Stockport">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(assetUrl('/assets/css/site.css'), ENT_QUOTES, 'UTF-8') ?>">
    <?php foreach ((array)($extraStylesheets ?? []) as $sheet): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(assetUrl((string)$sheet), ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <?php
    $gaId = defined('GA_MEASUREMENT_ID') ? trim((string)GA_MEASUREMENT_ID) : '';
    $awId = defined('AW_CONVERSION_ID') ? trim((string)AW_CONVERSION_ID) : '';
    $gtmId = defined('GTM_CONTAINER_ID') ? trim((string)GTM_CONTAINER_ID) : '';
    ?>
    <link rel="preconnect" href="https://www.googletagmanager.com">
    <!-- Google tag (gtag.js) / Google Tag Manager — cookie-gated via #cookie-banner -->
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        window.__icomplyAnalytics = {
            ga: <?= json_encode($gaId) ?>,
            aw: <?= json_encode($awId) ?>,
            gtm: <?= json_encode($gtmId) ?>,
            load: function () {
                if (window.__icomplyAnalyticsLoaded) return;
                window.__icomplyAnalyticsLoaded = true;
                if (this.gtm) {
                    var gtmS = document.createElement('script');
                    gtmS.async = true;
                    gtmS.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(this.gtm);
                    document.head.appendChild(gtmS);
                    window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                }
                var id = this.ga || this.aw;
                if (!id) return;
                var s = document.createElement('script');
                s.async = true;
                s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
                document.head.appendChild(s);
                gtag('js', new Date());
                if (this.ga) gtag('config', this.ga);
                if (this.aw) gtag('config', this.aw);
            }
        };
        try {
            if (localStorage.getItem('icomply_cookie_consent') === 'accepted') {
                window.__icomplyAnalytics.load();
            }
        } catch (e) {}
    </script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": <?= json_encode(SITE_NAME) ?>,
      "description": "Expert property compliance services including electrical, fire alarms, emergency lighting, gas safety, CCTV, access control and more across Greater Manchester and North West UK.",
      "url": <?= json_encode(SITE_URL) ?>,
      "telephone": <?= json_encode('+' . (strpos(WHATSAPP, '44') === 0 ? WHATSAPP : '44' . ltrim(PHONE, '0'))) ?>,
      "email": <?= json_encode(EMAIL) ?>,
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "17 Woodlands Park Road",
        "addressLocality": "Offerton, Stockport",
        "addressRegion": "Greater Manchester",
        "postalCode": "SK2 5DE",
        "addressCountry": "GB"
      },
      "geo": { "@type": "GeoCoordinates", "latitude": "53.3904", "longitude": "-2.1219" },
      "areaServed": ["Greater Manchester", "North West England", "Cheshire", "Lancashire", "Merseyside"],
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
        "opens": "08:00",
        "closes": "18:00"
      },
      "priceRange": "££",
      "sameAs": <?= json_encode(array_values(array_filter([
          defined('SOCIAL_FACEBOOK') ? SOCIAL_FACEBOOK : '',
          defined('SOCIAL_INSTAGRAM') ? SOCIAL_INSTAGRAM : '',
          defined('SOCIAL_LINKEDIN') ? SOCIAL_LINKEDIN : '',
          defined('SOCIAL_TWITTER') ? SOCIAL_TWITTER : '',
          defined('SOCIAL_YOUTUBE') ? SOCIAL_YOUTUBE : '',
          defined('SOCIAL_GOOGLE') ? SOCIAL_GOOGLE : '',
          'https://wa.me/' . WHATSAPP,
      ]))) ?>
    }
    </script>
</head>
<body class="theme-dark bg-zinc-50 text-black">
<a href="#main-content" class="skip-to-content">Skip to main content</a>
<?php
require_once __DIR__ . '/site-nav.php';
echo icomplyMegaHeaderHtml();
?>
<div id="main-content" tabindex="-1">
