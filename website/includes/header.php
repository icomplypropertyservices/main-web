<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/seo.php';
$__seoTitle = seo_title($pageTitle ?? SITE_NAME);
$__seoDesc = $metaDesc ?? 'Expert property compliance services across Greater Manchester and North West UK. EICR, Fire Alarms, Gas Safety, Emergency Lighting & more. Get your free quote today.';
$__seoCanon = $canonicalUrl ?? seo_current_url();
$__seoImage = $ogImage ?? site_url('assets/images/og-image.jpg');
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($__seoTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($__seoDesc) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords ?? 'property compliance, EICR, fire alarms, emergency lighting, gas safety, Manchester electrician, North West UK') ?>">
    <meta name="robots" content="<?= htmlspecialchars($metaRobots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1') ?>">
    <meta name="author" content="<?= SITE_NAME ?>">
    <meta name="geo.region" content="GB-MAN">
    <meta name="geo.placename" content="Stockport, Greater Manchester">
    <meta name="theme-color" content="#0a2540">
    <meta name="format-detection" content="telephone=yes">
    <meta http-equiv="x-dns-prefetch-control" content="on">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <meta property="og:locale" content="en_GB">
    <meta property="og:title" content="<?= htmlspecialchars($__seoTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($__seoDesc) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($ogType ?? 'website') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($__seoCanon) ?>">
    <meta property="og:site_name" content="<?= SITE_NAME ?>">
    <meta property="og:image" content="<?= htmlspecialchars($__seoImage) ?>">
    <meta property="og:image:alt" content="<?= htmlspecialchars($pageTitle ?? SITE_NAME) ?> — UK property compliance">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($__seoTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($__seoDesc) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($__seoImage) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($__seoCanon) ?>">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="sitemap.xml">
    <link rel="icon" href="assets/images/heroes/home-hero.jpg" type="image/jpeg">
    <base href="<?= htmlspecialchars(rtrim(SITE_URL, '/') . '/') ?>">
    <script type="application/ld+json"><?= json_encode(organization_schema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <script type="application/ld+json"><?= json_encode(website_schema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <script type="application/ld+json"><?= json_encode(local_business_schema(['parentOrganization' => ['@id' => site_url() . '#organization']]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css">
    <style>
        :root { --brand: #0a2540; --accent: #ff6b00; }
        body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
        .nav-link { transition: color 0.2s; }
        .nav-link:hover { color: #ff6b00; }
        .service-card { transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; }
        .service-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.12); }
        .modern-btn { background: #0a2540; color: #fff; transition: all 0.2s; }
        .modern-btn:hover { background: #ff6b00; transform: translateY(-1px); }
        .accent-btn { background: #ff6b00; color: #fff; transition: all 0.2s; }
        .accent-btn:hover { background: #e05f00; }
        .hero-overlay { background: linear-gradient(105deg, rgba(10,37,64,.92) 0%, rgba(10,37,64,.72) 55%, rgba(10,37,64,.45) 100%); }
        .img-cover { object-fit: cover; width: 100%; height: 100%; }
        .badge-uk { letter-spacing: .18em; }
        .wa-fab { box-shadow: 0 10px 30px rgba(22,163,74,.45); }
    </style>
</head>
<body class="bg-zinc-50 text-zinc-900">
<nav class="bg-white/95 backdrop-blur border-b sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between gap-4">
        <a href="index.php" class="flex items-center gap-3 min-w-0">
            <span class="w-10 h-10 rounded-xl bg-[#0a2540] text-white flex items-center justify-center font-bold text-sm shrink-0">IC</span>
            <span class="font-semibold text-lg tracking-tight truncate"><?= SITE_NAME ?></span>
        </a>

        <div class="hidden lg:flex items-center gap-7 text-sm font-medium">
            <a href="index.php" class="nav-link">Home</a>
            <a href="pages/services/index.php" class="nav-link">All Services</a>

            <div class="relative group">
                <button type="button" class="nav-link flex items-center gap-1">Services <span class="text-xs text-zinc-400">▼</span></button>
                <div class="absolute hidden group-hover:block bg-white shadow-xl rounded-2xl py-2 w-72 mt-2 border z-50">
                    <?php foreach ($services as $slug => $name): ?>
                        <a href="pages/services/<?= htmlspecialchars($slug) ?>.php" class="flex items-center gap-3 px-4 py-2.5 hover:bg-zinc-50">
                            <img src="assets/images/services/<?= htmlspecialchars($slug) ?>-photo.jpg" alt="<?= htmlspecialchars($name) ?>" class="w-10 h-10 rounded-lg object-cover" width="40" height="40" loading="lazy"
                                 onerror="this.src='assets/images/services/<?= htmlspecialchars($slug) ?>.png'">
                            <span><?= htmlspecialchars($name) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="relative group">
                <button type="button" class="nav-link flex items-center gap-1">Areas <span class="text-xs text-zinc-400">▼</span></button>
                <div class="absolute hidden group-hover:block bg-white shadow-xl rounded-2xl py-2 w-72 mt-2 border max-h-96 overflow-auto z-50">
                    <?php
                    $primaryServiceSlug = 'fire-alarms';
                    $primaryServiceName = $services[$primaryServiceSlug] ?? 'Fire Alarms';
                    foreach (array_slice($areas, 0, 40) as $area): ?>
                        <a href="pages/<?= htmlspecialchars($primaryServiceSlug) ?>/<?= areaSlug($area) ?>.php" class="block px-5 py-1.5 hover:bg-zinc-50 text-sm"><?= htmlspecialchars($area) ?> <?= htmlspecialchars($primaryServiceName) ?></a>
                    <?php endforeach; ?>
                    <div class="px-5 py-2 text-xs text-zinc-500 border-t">+ <?= count($areas) - 40 ?> more towns across the North West</div>
                </div>
            </div>

            <a href="contact.php" class="nav-link">Contact</a>
            <a href="https://wa.me/<?= WHATSAPP ?>" target="_blank" rel="noopener" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-full text-xs font-semibold">WhatsApp</a>
            <a href="tel:<?= PHONE ?>" class="px-4 py-2 modern-btn rounded-full text-xs font-semibold"><?= PHONE ?></a>
        </div>

        <div class="lg:hidden flex items-center gap-2">
            <a href="tel:<?= PHONE ?>" class="px-3 py-2 modern-btn rounded-full text-xs font-semibold">Call</a>
            <a href="contact.php" class="px-3 py-2 border rounded-full text-xs font-semibold">Quote</a>
        </div>
    </div>
</nav>
