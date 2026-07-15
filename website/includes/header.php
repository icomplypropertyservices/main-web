<?php
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}
$services = getServices();
$areas = getAreas();
$pageTitleSafe = htmlspecialchars($pageTitle ?? SITE_NAME, ENT_QUOTES, 'UTF-8');
$metaDescSafe = htmlspecialchars(
    $metaDesc ?? 'Expert property compliance services across Greater Manchester and North West UK. EICR, Fire Alarms, Gas Safety, Emergency Lighting & more. Get your free quote today.',
    ENT_QUOTES,
    'UTF-8'
);
$metaKeywordsSafe = htmlspecialchars(
    $metaKeywords ?? 'property compliance, EICR, fire alarms, emergency lighting, gas safety, Manchester electrician',
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
    <meta name="theme-color" content="#0a2540">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= $pageTitleSafe ?> | Property Compliance Experts</title>
    <meta name="description" content="<?= $metaDescSafe ?>">
    <meta name="keywords" content="<?= $metaKeywordsSafe ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_GB">
    <meta property="og:site_name" content="<?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= $pageTitleSafe ?> | Property Compliance Experts">
    <meta property="og:description" content="<?= $metaDescSafe ?>">
    <meta property="og:url" content="<?= htmlspecialchars(url($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
    <?php
    // Default OG image when page does not set $ogImage
    if (empty($ogImage)) {
        $ogImage = url('/assets/images/services/fire-alarms.jpg');
    }
    $ogImageSafe = htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8');
    ?>
    <meta property="og:image" content="<?= $ogImageSafe ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $pageTitleSafe ?> | Property Compliance Experts">
    <meta name="twitter:description" content="<?= $metaDescSafe ?>">
    <meta name="twitter:image" content="<?= $ogImageSafe ?>">
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
    <?php
    // Canonical URL — prefer explicit $canonicalUrl, else derive from SITE_URL + path
    if (empty($canonicalUrl)) {
        require_once __DIR__ . '/share.php';
        $canonicalUrl = function_exists('currentPageUrl') ? currentPageUrl() : url('/');
    }
    ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" href="<?= htmlspecialchars(url('/assets/images/favicon.svg'), ENT_QUOTES, 'UTF-8') ?>" type="image/svg+xml">
    <link rel="manifest" href="<?= htmlspecialchars(url('/manifest.json'), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= htmlspecialchars($metaRobots ?? 'index, follow, max-image-preview:large', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="<?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="geo.region" content="GB-MAN">
    <meta name="geo.placename" content="Stockport">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css">
    <style>
        :root { --brand: #0a2540; --accent: #ff6b00; }
        body { color: #000; }
        .nav-link { transition: color 0.15s ease; color: #111; }
        .nav-link:hover { color: #ff6b00; }
        .service-card { transition: transform 0.2s, box-shadow 0.2s; }
        .service-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); }
        .modern-btn { background: #0a2540; transition: all 0.2s; }
        .modern-btn:hover { background: #ff6b00; transform: translateY(-1px); }
        /* Keep dropdowns open while hovering panel */
        .nav-drop { position: relative; }
        .nav-drop > .nav-panel {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            padding-top: 0.5rem;
            z-index: 60;
        }
        .nav-drop:hover > .nav-panel,
        .nav-drop:focus-within > .nav-panel,
        .nav-drop.is-open > .nav-panel { display: block; }
        .nav-panel-inner {
            background: #fff;
            border: 1px solid #e4e4e7;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0,0,0,.12);
            overflow: hidden;
        }
        #mobile-nav { display: none; }
        #mobile-nav.is-open { display: block; }
        @media (max-width: 1023px) {
            .desktop-nav { display: none !important; }
            .desktop-phone { display: none !important; }
        }
        @media (min-width: 1024px) {
            .mobile-toggle { display: none !important; }
            #mobile-nav { display: none !important; }
        }
        /* Print: clean quote / content pages */
        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
                font-size: 11pt;
                line-height: 1.45;
            }
            .skip-to-content,
            nav .desktop-nav,
            nav .desktop-phone,
            nav .mobile-toggle,
            nav #mobile-nav,
            footer,
            #cookie-banner,
            .share-bar,
            a.fixed.bottom-6.right-6,
            a[aria-label="WhatsApp"].fixed {
                display: none !important;
            }
            nav {
                position: static !important;
                top: auto !important;
                border: none !important;
                border-bottom: 2px solid #0a2540 !important;
                background: #fff !important;
                box-shadow: none !important;
                margin-bottom: 1rem;
                padding: 0 0 0.75rem !important;
            }
            nav > div {
                padding: 0 !important;
                max-width: none !important;
            }
            /* Logo-ish site name only */
            nav a.font-semibold.shrink-0,
            nav a.font-semibold.text-lg {
                display: inline-block !important;
                font-size: 16pt !important;
                font-weight: 700 !important;
                color: #0a2540 !important;
                letter-spacing: -0.02em;
                text-decoration: none !important;
            }
            #main-content {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            a {
                color: #000 !important;
                text-decoration: none !important;
            }
            a[href^="http"]::after,
            a[href^="tel:"]::after,
            a[href^="mailto:"]::after {
                content: none !important;
            }
            .shadow-xl, .shadow-lg, .shadow-md, .shadow {
                box-shadow: none !important;
            }
            img {
                max-width: 100% !important;
                page-break-inside: avoid;
            }
            h1, h2, h3 {
                color: #0a2540 !important;
                page-break-after: avoid;
            }
            section, article, .rounded-3xl, .rounded-2xl {
                break-inside: avoid;
            }
            @page {
                margin: 1.5cm;
            }
        }
    </style>
    <?php if (GA_MEASUREMENT_ID !== '' || AW_CONVERSION_ID !== ''): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars(GA_MEASUREMENT_ID !== '' ? GA_MEASUREMENT_ID : AW_CONVERSION_ID, ENT_QUOTES, 'UTF-8') ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        <?php if (GA_MEASUREMENT_ID !== ''): ?>gtag('config', <?= json_encode(GA_MEASUREMENT_ID) ?>);<?php endif; ?>
        <?php if (AW_CONVERSION_ID !== ''): ?>gtag('config', <?= json_encode(AW_CONVERSION_ID) ?>);<?php endif; ?>
    </script>
    <?php endif; ?>
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
<body class="bg-zinc-50 text-black">
<a href="#main-content" class="skip-to-content">Skip to main content</a>
<style>
    .skip-to-content {
        position: absolute;
        left: -9999px;
        top: 0.5rem;
        z-index: 100;
        padding: 0.5rem 1rem;
        background: #fff;
        color: #000;
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,.15);
        text-decoration: none;
    }
    .skip-to-content:focus {
        left: 0.5rem;
        outline: 2px solid #ff6b00;
        outline-offset: 2px;
    }
</style>
<nav class="bg-white border-b sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 lg:py-4 flex items-center justify-between gap-4">
        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-lg sm:text-2xl tracking-tight text-black shrink-0">
            <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>
        </a>

        <!-- Desktop nav -->
        <div class="desktop-nav flex items-center gap-5 xl:gap-7 text-sm font-medium">
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="nav-link">Home</a>

            <div class="nav-drop">
                <a href="<?= url('/pages/services/index.php') ?>" class="nav-link flex items-center gap-1">
                    Services <span class="text-xs opacity-60">▼</span>
                </a>
                <div class="nav-panel">
                    <div class="nav-panel-inner w-72 max-h-96 overflow-auto py-2">
                        <a href="<?= url('/pages/services/index.php') ?>" class="block px-5 py-2.5 font-semibold text-[#ff6b00] hover:bg-zinc-50 border-b">View all services →</a>
                        <?php foreach ($services as $slug => $name): ?>
                            <a href="<?= url('/pages/services/' . rawurlencode($slug) . '.php') ?>" class="block px-5 py-2.5 hover:bg-zinc-50 text-black"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="nav-drop">
                <a href="<?= url('/pages/areas/index.php') ?>" class="nav-link flex items-center gap-1">
                    Areas <span class="text-xs opacity-60">▼</span>
                </a>
                <div class="nav-panel">
                    <div class="nav-panel-inner w-80 max-h-96 overflow-auto py-2">
                        <a href="<?= url('/pages/areas/index.php') ?>" class="block px-5 py-2.5 font-semibold text-[#ff6b00] hover:bg-zinc-50 border-b">All areas we cover →</a>
                        <?php foreach (array_slice($areas, 0, 50) as $area): ?>
                            <a href="<?= url('/pages/areas/' . areaSlug($area) . '.php') ?>" class="block px-5 py-1.5 hover:bg-zinc-50 text-sm text-black"><?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endforeach; ?>
                        <?php if (count($areas) > 50): ?>
                            <a href="<?= url('/pages/areas/index.php') ?>" class="block px-5 py-2 text-xs text-zinc-500 border-t hover:bg-zinc-50">+ <?= count($areas) - 50 ?> more areas — view full list</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="nav-drop">
                <a href="<?= url('/pages/manufacturers/index.php') ?>" class="nav-link flex items-center gap-1">
                    Brands <span class="text-xs opacity-60">▼</span>
                </a>
                <div class="nav-panel">
                    <div class="nav-panel-inner w-80 max-h-96 overflow-auto py-2">
                        <a href="<?= url('/pages/manufacturers/index.php') ?>" class="block px-5 py-2.5 font-semibold text-[#ff6b00] hover:bg-zinc-50 border-b">All manufacturers →</a>
                        <?php
                        $navMfr = array_filter(getManufacturerCatalog(), fn($c) => !empty($c['featured']));
                        if (!$navMfr) {
                            $navMfr = array_slice(getManufacturerCatalog(), 0, 16, true);
                        }
                        foreach (array_slice($navMfr, 0, 16, true) as $mSlug => $mEntry):
                        ?>
                            <a href="<?= url('/pages/manufacturers/' . rawurlencode($mSlug) . '.php') ?>" class="block px-5 py-1.5 hover:bg-zinc-50 text-sm text-black"><?= htmlspecialchars($mEntry['name'], ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <a href="<?= url('/shop/index.php') ?>" class="nav-link font-semibold text-[#ff6b00]">Shop</a>

            <div class="nav-drop">
                <a href="<?= url('/pages/site-map.php') ?>" class="nav-link flex items-center gap-1" aria-haspopup="true">
                    More <span class="text-xs opacity-60">▼</span>
                </a>
                <div class="nav-panel">
                    <div class="nav-panel-inner w-64 max-h-96 overflow-auto py-2">
                        <a href="<?= url('/pages/about.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">About</a>
                        <a href="<?= url('/pages/packages.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">Packages</a>
                        <a href="<?= url('/pages/pricing.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">Pricing guide</a>
                        <a href="<?= url('/pages/landlords.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">Landlords</a>
                        <a href="<?= url('/pages/commercial.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">Commercial / FM</a>
                        <a href="<?= url('/pages/care-homes.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">Care homes</a>
                        <a href="<?= url('/pages/ev-chargers.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">EV chargers</a>
                        <a href="<?= url('/pages/maintenance.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">Maintenance contracts</a>
                        <a href="<?= url('/pages/emergency.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">Emergency call-out</a>
                        <a href="<?= url('/pages/resources/index.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black border-t">Resources</a>
                        <a href="<?= url('/pages/keywords/index.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">Keyword guides</a>
                        <a href="<?= url('/pages/faq.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">FAQ</a>
                        <a href="<?= url('/pages/reviews.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-black">Reviews</a>
                        <a href="<?= url('/pages/site-map.php') ?>" class="block px-5 py-2 hover:bg-zinc-50 text-[#ff6b00] font-semibold border-t">Full site map →</a>
                    </div>
                </div>
            </div>

            <a href="<?= url('/contact.php') ?>" class="nav-link">Contact</a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-full text-xs font-semibold whitespace-nowrap">
                WhatsApp
            </a>
        </div>

        <div class="desktop-phone text-right text-xs shrink-0">
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-black hover:text-[#ff6b00] block"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <div class="text-zinc-500 max-w-[11rem] truncate" title="<?= htmlspecialchars(ADDRESS, ENT_QUOTES, 'UTF-8') ?>">Stockport SK2 5DE</div>
        </div>

        <!-- Mobile toggle -->
        <button type="button" id="nav-toggle" class="mobile-toggle p-2 rounded-xl border border-zinc-200 text-black" aria-label="Open menu" aria-expanded="false">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-nav" class="border-t bg-white">
        <div class="max-w-7xl mx-auto px-4 py-4 space-y-1 text-sm font-medium">
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="block px-3 py-3 rounded-xl hover:bg-zinc-50">Home</a>
            <a href="<?= url('/pages/services/index.php') ?>" class="block px-3 py-3 rounded-xl hover:bg-zinc-50 font-semibold">All Services</a>
            <?php foreach ($services as $slug => $name): ?>
                <a href="<?= url('/pages/services/' . rawurlencode($slug) . '.php') ?>" class="block px-6 py-2 text-zinc-700 hover:bg-zinc-50"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
            <a href="<?= url('/pages/areas/index.php') ?>" class="block px-3 py-3 rounded-xl hover:bg-zinc-50 font-semibold mt-2">Areas We Cover</a>
            <?php foreach (array_slice($areas, 0, 12) as $area): ?>
                <a href="<?= url('/pages/areas/' . areaSlug($area) . '.php') ?>" class="block px-6 py-2 text-zinc-700 hover:bg-zinc-50"><?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
            <a href="<?= url('/pages/areas/index.php') ?>" class="block px-6 py-2 text-[#ff6b00] text-xs">View all <?= count($areas) ?> areas →</a>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="block px-3 py-3 rounded-xl hover:bg-zinc-50 font-semibold">Manufacturers / Brands</a>
            <a href="<?= url('/shop/index.php') ?>" class="block px-3 py-3 rounded-xl hover:bg-zinc-50 font-semibold text-[#ff6b00]">Shop</a>
            <div class="px-3 pt-3 pb-1 text-xs uppercase tracking-wider text-zinc-400 font-semibold">Explore</div>
            <a href="<?= url('/pages/about.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">About</a>
            <a href="<?= url('/pages/packages.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Packages</a>
            <a href="<?= url('/pages/pricing.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Pricing guide</a>
            <a href="<?= url('/pages/landlords.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Landlords</a>
            <a href="<?= url('/pages/commercial.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Commercial / FM</a>
            <a href="<?= url('/pages/care-homes.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Care homes</a>
            <a href="<?= url('/pages/ev-chargers.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">EV chargers</a>
            <a href="<?= url('/pages/maintenance.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Maintenance contracts</a>
            <a href="<?= url('/pages/emergency.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Emergency call-out</a>
            <a href="<?= url('/pages/resources/index.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Resources</a>
            <a href="<?= url('/pages/keywords/index.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Keyword guides</a>
            <a href="<?= url('/pages/faq.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">FAQ</a>
            <a href="<?= url('/pages/reviews.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50">Reviews</a>
            <a href="<?= url('/pages/site-map.php') ?>" class="block px-3 py-2.5 rounded-xl hover:bg-zinc-50 font-semibold text-[#ff6b00]">Full site map</a>
            <a href="<?= url('/contact.php') ?>" class="block px-3 py-3 rounded-xl hover:bg-zinc-50 font-semibold mt-1">Contact</a>
            <a href="<?= url('/privacy.php') ?>" class="block px-3 py-2 text-zinc-500 text-xs">Privacy</a>
            <a href="<?= url('/terms.php') ?>" class="block px-3 py-2 text-zinc-500 text-xs">Terms</a>
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="block px-3 py-3 rounded-xl hover:bg-zinc-50"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
               class="block text-center mt-2 px-4 py-3 bg-green-600 text-white rounded-2xl font-semibold">WhatsApp Us</a>
        </div>
    </div>
</nav>
<script>
(function () {
    var btn = document.getElementById('nav-toggle');
    var panel = document.getElementById('mobile-nav');
    if (!btn || !panel) return;
    btn.addEventListener('click', function () {
        var open = panel.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });
})();
</script>
<div id="main-content" tabindex="-1">
