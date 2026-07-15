<?php
/**
 * About — conversion-focused company page: who we are, services, coverage, standards, shop, CTAs.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/shopify.php';

$pageTitle = 'About Us | Stockport Property Compliance';
$metaDesc = 'About Icomply Property Services — Stockport SK2 5DE based engineers delivering electrical, fire, gas, emergency lighting, CCTV and access control across Greater Manchester and the North West. Free quotes.';
$metaKeywords = 'about Icomply, property compliance Stockport, EICR North West, fire alarm engineers Greater Manchester, gas safety Stockport';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/about.php');

$services = getServices();
$areas = getAreas();
$catalog = getShopCatalog();
$featuredProducts = array_slice($catalog['products'], 0, 4);

// Short blurbs via getServiceBlurb($slug, true) — see includes/content.php / config.php

$trust = [
    ['title' => 'Stockport based', 'text' => 'SK2 5DE HQ — local engineers, North West coverage'],
    ['title' => 'Standards-led', 'text' => 'BS 5839, BS 5266, BS 7671, gas safety & more'],
    ['title' => 'Fixed-price quotes', 'text' => 'Clear scope, documentation and certification'],
    ['title' => 'Trade shop', 'text' => 'Kits & parts with Shopify checkout when live'],
];

$standards = [
    ['code' => 'BS 7671', 'label' => 'Electrical wiring & EICR'],
    ['code' => 'BS 5839', 'label' => 'Fire detection & alarms'],
    ['code' => 'BS 5266', 'label' => 'Emergency lighting'],
    ['code' => 'Gas Safe', 'label' => 'Landlord & commercial gas'],
    ['code' => 'PD 6662', 'label' => 'Intruder alarm systems'],
    ['code' => 'BS EN 50131', 'label' => 'Security systems'],
];

$popularTowns = array_values(array_filter(
    ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool'],
    function ($t) use ($areas) {
        return in_array($t, $areas, true);
    }
));

$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';

$aboutSchema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'AboutPage',
            '@id' => url('/pages/about.php') . '#webpage',
            'url' => url('/pages/about.php'),
            'name' => 'About ' . SITE_NAME,
            'description' => $metaDesc,
            'isPartOf' => ['@type' => 'WebSite', 'name' => SITE_NAME, 'url' => SITE_URL],
            'about' => ['@id' => url('/pages/about.php') . '#business'],
        ],
        [
            '@type' => 'LocalBusiness',
            '@id' => url('/pages/about.php') . '#business',
            'name' => SITE_NAME,
            'url' => SITE_URL,
            'telephone' => PHONE,
            'email' => EMAIL,
            'image' => $ogImage,
            'description' => 'Property compliance specialists — electrical, fire alarms, gas, emergency lighting, CCTV and access control across Greater Manchester and the North West.',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '17 Woodlands Park Road',
                'addressLocality' => 'Offerton, Stockport',
                'addressRegion' => 'Greater Manchester',
                'postalCode' => 'SK2 5DE',
                'addressCountry' => 'GB',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => '53.3904',
                'longitude' => '-2.1219',
            ],
            'areaServed' => [
                'Greater Manchester',
                'North West England',
                'Cheshire',
                'Lancashire',
                'Merseyside',
                'Cumbria',
            ],
            'priceRange' => '££',
            'sameAs' => array_values(array_filter([
                defined('SOCIAL_FACEBOOK') ? SOCIAL_FACEBOOK : '',
                defined('SOCIAL_INSTAGRAM') ? SOCIAL_INSTAGRAM : '',
                defined('SOCIAL_LINKEDIN') ? SOCIAL_LINKEDIN : '',
                defined('SOCIAL_TWITTER') ? SOCIAL_TWITTER : '',
                defined('SOCIAL_GOOGLE') ? SOCIAL_GOOGLE : '',
            ])),
        ],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($aboutSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">About</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    Stockport SK2 5DE · North West
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    About<br>
                    <span class="text-[#ff6b00]">Icomply Property Services</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    Local engineers delivering installation, testing and certification for electrical, fire, gas,
                    emergency lighting, security and care systems — plus a trade shop for kits and parts.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get free quote</a>
                    <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block"><?= count($services) ?></span> core services</div>
                    <div><span class="text-white font-semibold text-xl block"><?= count($areas) ?>+</span> towns covered</div>
                    <div><span class="text-white font-semibold text-xl block">SK2 5DE</span> base</div>
                </div>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-3xl p-8 md:p-10 backdrop-blur-sm">
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Contact</div>
                <h2 class="text-2xl font-semibold tracking-tight">Talk to the team</h2>
                <dl class="mt-6 space-y-4 text-sm">
                    <div>
                        <dt class="text-white/50 text-xs uppercase tracking-wider">Phone</dt>
                        <dd class="mt-1"><a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="text-white hover:text-[#ff6b00] font-medium text-lg"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a></dd>
                    </div>
                    <div>
                        <dt class="text-white/50 text-xs uppercase tracking-wider">WhatsApp</dt>
                        <dd class="mt-1"><a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-white hover:text-[#ff6b00] font-medium">+<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?></a></dd>
                    </div>
                    <div>
                        <dt class="text-white/50 text-xs uppercase tracking-wider">Email</dt>
                        <dd class="mt-1"><a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="text-white hover:text-[#ff6b00] break-all"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a></dd>
                    </div>
                    <div>
                        <dt class="text-white/50 text-xs uppercase tracking-wider">Address</dt>
                        <dd class="mt-1 text-white/90">17 Woodlands Park Road<br>Offerton, Stockport SK2 5DE</dd>
                    </div>
                </dl>
                <a href="<?= url('/contact.php') ?>" class="inline-block mt-8 px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Request a quote →</a>
            </div>
        </div>
    </div>
</section>

<!-- TRUST STRIP -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($trust as $t): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t['title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($t['text'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- WHO WE ARE -->
<section id="who-we-are" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Who we are</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Compliance specialists you can call today</h2>
            <p class="mt-4 text-zinc-600 text-lg leading-relaxed">
                <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> is a Stockport-based property compliance company
                helping landlords, facilities managers, care providers and commercial clients keep buildings safe,
                legal and audit-ready.
            </p>
            <p class="mt-4 text-zinc-600 leading-relaxed">
                From single EICR certificates to multi-site fire alarm programmes, we install, maintain, test and
                certify systems to the relevant British Standards — with fixed-price quotes and clear documentation.
            </p>
            <ul class="mt-6 space-y-3 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> One point of contact for multi-service packages</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Local response across <?= count($areas) ?>+ North West towns</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Manufacturer-aware installs (Paxton, Hikvision, C-TEC &amp; more)</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Trade shop for kits, parts and install accessories</li>
            </ul>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="bg-zinc-50 border rounded-3xl p-6">
                <div class="text-3xl font-semibold text-[#0a2540]"><?= count($services) ?></div>
                <div class="mt-1 font-semibold text-black">Core services</div>
                <p class="mt-2 text-sm text-zinc-600">Electrical, fire, gas, lighting, AOV, nurse call, CCTV &amp; access.</p>
            </div>
            <div class="bg-zinc-50 border rounded-3xl p-6">
                <div class="text-3xl font-semibold text-[#0a2540]"><?= count($areas) ?>+</div>
                <div class="mt-1 font-semibold text-black">Towns covered</div>
                <p class="mt-2 text-sm text-zinc-600">Greater Manchester, Lancashire, Cheshire, Merseyside &amp; Cumbria.</p>
            </div>
            <div class="bg-zinc-50 border rounded-3xl p-6">
                <div class="text-3xl font-semibold text-[#0a2540]">SK2</div>
                <div class="mt-1 font-semibold text-black">Stockport base</div>
                <p class="mt-2 text-sm text-zinc-600">17 Woodlands Park Road, Offerton, Stockport SK2 5DE.</p>
            </div>
            <div class="bg-[#0a2540] text-white rounded-3xl p-6">
                <div class="text-3xl font-semibold text-[#ff6b00]">2 hrs</div>
                <div class="mt-1 font-semibold">Typical response*</div>
                <p class="mt-2 text-sm text-white/75">We aim to reply to quote requests within 2 hours on business days.</p>
            </div>
        </div>
    </div>
    <p class="mt-4 text-[11px] text-zinc-400">*Subject to engineer capacity and site access.</p>
</section>

<!-- SERVICES -->
<section id="services" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Services</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What we deliver</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Install, maintain, test and certify — open any service for local pages, manufacturers and a free quote.</p>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <?php foreach ($services as $slug => $name):
                $blurb = getServiceBlurb($slug, true);
                $img = url('/assets/images/services/' . $slug . '.jpg');
            ?>
            <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
               class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="h-36 bg-zinc-100 overflow-hidden">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy"
                         onerror="this.parentElement.style.display='none'">
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="mt-4 text-sm font-semibold text-[#ff6b00]">Explore →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- COVERAGE -->
<section id="coverage" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Coverage</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Serving <?= count($areas) ?>+ towns across the North West</h2>
            <p class="mt-3 text-zinc-600">
                Based in Offerton, Stockport, we cover Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria.
                Pick a town for service links and a local quote.
            </p>
            <div class="mt-6 flex flex-wrap gap-2">
                <?php foreach ($popularTowns as $town): ?>
                    <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
                       class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]"><?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </div>
            <a href="<?= url('/pages/areas/index.php') ?>" class="inline-block mt-6 text-sm font-semibold text-[#ff6b00]">View all areas →</a>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight">Need a compliance package?</h3>
            <p class="mt-3 text-white/80">Combine EICR, fire alarms, emergency lighting and gas safety into one visit schedule for landlords and facilities teams.</p>
            <ul class="mt-6 space-y-3 text-sm text-white/90">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Fixed-price multi-service quotes</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Full documentation for audits &amp; insurers</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Maintenance contracts available</li>
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#quote" class="px-6 py-3 bg-[#ff6b00] rounded-2xl font-semibold">Start your quote</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Icomply%2C%20I%20need%20a%20quote"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 border border-white/30 rounded-2xl font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<!-- STANDARDS -->
<section id="standards" class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Standards</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Built around the codes that matter</h2>
            <p class="mt-3 text-zinc-600">We design, install and certify against the British Standards and industry schemes your insurers, auditors and tenants expect.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($standards as $s): ?>
            <div class="border border-zinc-200 rounded-3xl p-6 hover:border-[#ff6b00] transition bg-zinc-50">
                <div class="text-xl font-semibold text-[#0a2540] tracking-tight"><?= htmlspecialchars($s['code'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($s['label'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-10 text-center">
            <a href="<?= url('/pages/services/index.php') ?>" class="inline-flex px-6 py-3 rounded-2xl bg-[#0a2540] text-white font-semibold hover:bg-[#ff6b00] transition">Browse services by standard →</a>
        </div>
    </div>
</section>

<!-- SHOP -->
<section id="shop" class="bg-zinc-100 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Shop</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Trade kits &amp; install parts</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Fire, electrical, security and emergency lighting gear — Shopify-ready for direct checkout when connected.</p>
            </div>
            <a href="<?= url('/shop/index.php') ?>" class="inline-flex px-5 py-2.5 rounded-full bg-[#0a2540] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">Visit shop</a>
        </div>
        <?php if ($featuredProducts): ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($featuredProducts as $p): ?>
                <?= shopifyProductCardHtml($p, true) ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <a href="<?= url('/shop/index.php') ?>" class="bg-white border rounded-3xl p-8 hover:border-[#ff6b00] transition">
                <div class="font-semibold text-lg text-black">Fire &amp; detection</div>
                <p class="mt-2 text-sm text-zinc-600">Panels, detectors, call points and service kits.</p>
                <span class="mt-4 inline-block text-sm font-semibold text-[#ff6b00]">Shop →</span>
            </a>
            <a href="<?= url('/shop/index.php') ?>" class="bg-white border rounded-3xl p-8 hover:border-[#ff6b00] transition">
                <div class="font-semibold text-lg text-black">Electrical &amp; lighting</div>
                <p class="mt-2 text-sm text-zinc-600">Emergency lighting, test kits and accessories.</p>
                <span class="mt-4 inline-block text-sm font-semibold text-[#ff6b00]">Shop →</span>
            </a>
            <a href="<?= url('/shop/index.php') ?>" class="bg-white border rounded-3xl p-8 hover:border-[#ff6b00] transition">
                <div class="font-semibold text-lg text-black">Security &amp; access</div>
                <p class="mt-2 text-sm text-zinc-600">CCTV, access control and door entry parts.</p>
                <span class="mt-4 inline-block text-sm font-semibold text-[#ff6b00]">Shop →</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-12">How it works</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <?php
            $steps = [
                ['1', 'Tell us the job', 'Service, postcode, panel brand or system type — via form, phone or WhatsApp.'],
                ['2', 'Get a fixed quote', 'We confirm scope, standards and timeline. No jargon, clear price.'],
                ['3', 'We deliver & certify', 'Engineers attend, complete the work and issue documentation.'],
            ];
            foreach ($steps as [$n, $t, $d]): ?>
            <div class="text-center px-4">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center text-lg"><?= $n ?></div>
                <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FINAL CTA + QUOTE -->
<section id="quote" class="bg-zinc-50">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Work with Icomply</h2>
            <p class="mt-3 text-zinc-600">Call <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="text-[#ff6b00] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>,
                WhatsApp, or send the form — we aim to respond within 2 hours on business days.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-6 py-3 rounded-2xl bg-[#0a2540] text-white font-semibold hover:bg-[#ff6b00] transition">Call now</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-semibold">WhatsApp</a>
                <a href="<?= url('/contact.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#ff6b00]">Contact page</a>
            </div>
        </div>

        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                    <option value="Shop / products">Shop / products</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Postcode, property type, panel brand / system details…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?= shopifyBuyButtonScript() ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
