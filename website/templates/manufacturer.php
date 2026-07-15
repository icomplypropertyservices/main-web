<?php
/**
 * Manufacturer hub template.
 * Placeholders: MFR_NAME, MFR_SLUG, MFR_BLURB, MFR_SEO_KEYWORDS, MFR_SERVICES_HTML,
 * MFR_PRODUCTS_HTML, MFR_RELATED_HTML, SERVICE_NAME (primary)
 */
$pageTitle = '{{MFR_NAME}} Products & Service';
$metaDesc = '{{MFR_BLURB}}';
$metaKeywords = '{{MFR_SEO_KEYWORDS}}';
$ogImage = url('/assets/images/manufacturers/{{MFR_SLUG}}.jpg');
$canonicalUrl = url('/pages/manufacturers/{{MFR_SLUG}}.php');

require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/shopify.php';

$mfrSlug = '{{MFR_SLUG}}';
$mfrName = '{{MFR_NAME}}';
$entry = getManufacturerBySlug($mfrSlug) ?? [];
$services = getServices();
$mfrServices = $entry['services'] ?? [];
$products = $entry['products'] ?? [];
$primaryService = $mfrServices[0] ?? 'fire-alarms';
$primaryServiceName = $services[$primaryService] ?? 'Compliance';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Brand',
            'name' => $mfrName,
            'url' => $canonicalUrl,
        ],
        [
            '@type' => 'Store',
            'name' => SITE_NAME . ' — ' . $mfrName,
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'telephone' => PHONE,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '17 Woodlands Park Road, Offerton',
                'addressLocality' => 'Stockport',
                'postalCode' => 'SK2 5DE',
                'addressCountry' => 'GB',
            ],
            'brand' => ['@type' => 'Brand', 'name' => $mfrName],
        ],
        [
            '@type' => 'ItemList',
            'name' => $mfrName . ' products',
            'itemListElement' => array_values(array_map(function ($p, $i) use ($mfrName) {
                return [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $p['title'] ?? ($mfrName . ' product'),
                    'description' => $p['blurb'] ?? '',
                ];
            }, $products, array_keys($products))),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Manufacturers', 'item' => url('/pages/manufacturers/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $mfrName, 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Do you install and service ' . $mfrName . ' systems?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Yes. Icomply Property Services installs, commissions, maintains and certifies ' . $mfrName . ' equipment across Greater Manchester and the North West.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Can I buy ' . $mfrName . ' parts and kits from you?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'We supply trade kits and accessories for ' . $mfrName . ' via our shop (Shopify when live) and can quote project-specific equipment for install jobs.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Which areas do you cover for ' . $mfrName . ' work?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'We cover 150+ towns including Manchester, Stockport, Bolton, Liverpool, Preston and the wider North West from our Stockport base.',
                    ],
                ],
            ],
        ],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 15% 30%,#ff6b00,transparent 42%),radial-gradient(circle at 85% 10%,#3b82f6,transparent 38%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="hover:text-white">Manufacturers</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    Brand · Trade shop · Install &amp; service
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?><br>
                    <span class="text-[#ff6b00]">products &amp; service</span>
                </h1>
                <p class="mt-6 text-lg text-white/80 max-w-xl">{{MFR_BLURB}}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#products" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Shop products</a>
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">Install quote</a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($mfrName . ' enquiry') ?>"
                       target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                </div>
            </div>
            <div class="relative rounded-3xl overflow-hidden border border-white/10 min-h-[260px] bg-white/5">
                <img src="<?= url('/assets/images/manufacturers/{{MFR_SLUG}}.jpg') ?>"
                     alt="<?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> equipment — Icomply Property Services"
                     class="absolute inset-0 w-full h-full object-cover opacity-70"
                     loading="eager"
                     onerror="this.src='<?= url('/assets/images/services/' . htmlspecialchars($primaryService, ENT_QUOTES, 'UTF-8') . '.jpg') ?>'">
                <div class="relative p-6 md:p-8 flex flex-col justify-end min-h-[260px] bg-gradient-to-t from-[#0a2540]/90 via-transparent to-transparent">
                    <div class="text-sm text-white/70">Authorised install &amp; trade supply</div>
                    <div class="text-2xl font-semibold mt-1"><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> · North West</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SERVICES FOR THIS BRAND -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-3">Related services</div>
        <div class="flex flex-wrap gap-2">
            {{MFR_SERVICES_HTML}}
        </div>
    </div>
</section>

<!-- INTRO -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-5 gap-12">
        <div class="lg:col-span-3">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">About <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?></div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                Install, service &amp; buy <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="mt-5 text-lg text-zinc-700 leading-relaxed">{{MFR_BLURB}}</p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                Whether you need a new <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> system designed and commissioned,
                planned maintenance on existing equipment, or trade kits and spares, our Stockport-based engineers
                cover Manchester, Bolton, Liverpool, Preston and 150+ North West towns. All work is documented for
                landlords, insurers and facilities managers.
            </p>
            <ul class="mt-6 space-y-2 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> New installs &amp; system upgrades</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Servicing, repairs &amp; certification</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Trade products &amp; engineer kits (Shopify-ready)</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Multi-site &amp; landlord packages</li>
            </ul>
        </div>
        <div class="lg:col-span-2 bg-[#0a2540] text-white rounded-3xl p-8">
            <h3 class="text-xl font-semibold">Need <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> support?</h3>
            <p class="mt-3 text-white/75 text-sm">Tell us your panel model, postcode and whether you need install, service or parts.</p>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="block mt-6 px-5 py-3 bg-white text-[#0a2540] rounded-2xl font-semibold text-center"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($mfrName . ' quote') ?>"
               target="_blank" rel="noopener"
               class="block mt-3 px-5 py-3 bg-green-600 rounded-2xl font-semibold text-center">WhatsApp</a>
            <a href="<?= url('/shop/index.php') ?>" class="block mt-3 px-5 py-3 border border-white/30 rounded-2xl font-semibold text-center hover:bg-white/10">Full shop</a>
        </div>
    </div>
</section>

<!-- PRODUCTS -->
<section id="products" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Shop</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
                    <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> products &amp; kits
                </h2>
                <p class="mt-2 text-zinc-600">Trade-oriented kits ready for Shopify Buy Buttons when credentials are configured.</p>
            </div>
            <a href="<?= url('/shop/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All shop products →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {{MFR_PRODUCTS_HTML}}
        </div>
        <p class="mt-8 text-sm text-zinc-600">
            Looking for more trade kits?
            <a href="<?= url('/shop/index.php') ?>" class="text-[#ff6b00] font-semibold">Open the full shop</a>
            <?php if (function_exists('shopifyStoreUrl') && shopifyStoreUrl()): ?>
                or
                <a href="<?= htmlspecialchars(shopifyStoreUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-[#ff6b00] font-semibold">checkout on Shopify →</a>
            <?php endif; ?>
        </p>
    </div>
</section>

<!-- LOCAL AREAS -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Local install</div>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
        <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> near you
    </h2>
    <p class="mt-2 text-zinc-600 mb-6">Open a local <?= htmlspecialchars($primaryServiceName, ENT_QUOTES, 'UTF-8') ?> page for dedicated SEO and quotes.</p>
    <div class="flex flex-wrap gap-2">
        <?php
        $towns = array_values(array_filter(
            ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool'],
            fn($t) => in_array($t, getAreas(), true)
        ));
        foreach ($towns as $t):
        ?>
            <a href="<?= url('/pages/' . htmlspecialchars($primaryService, ENT_QUOTES, 'UTF-8') . '/' . areaSlug($t) . '.php') ?>"
               class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">
                <?= htmlspecialchars($primaryServiceName . ' in ' . $t, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- RELATED BRANDS -->
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Also stocked</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Related manufacturers</h2>
            </div>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All manufacturers →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{MFR_RELATED_HTML}}
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-10">
            <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> FAQ
        </h2>
        <div class="space-y-4">
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-semibold cursor-pointer">Do you install <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?>?</summary>
                <p class="mt-3 text-sm text-zinc-600">Yes — design, supply, install, commission and certificate across the North West.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-semibold cursor-pointer">Can I buy <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> parts online?</summary>
                <p class="mt-3 text-sm text-zinc-600">Browse kits above or our shop. Shopify checkout activates when store credentials are set in config.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-semibold cursor-pointer">Do you maintain existing <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> systems?</summary>
                <p class="mt-3 text-sm text-zinc-600">We service, repair, reprogram and upgrade systems already on site, with full documentation.</p>
            </details>
        </div>
        <?= shareButtonsHtml($mfrName . ' Products & Service', $metaDesc) ?>
    </div>
</section>

<!-- CTA BAND -->
<section class="bg-[#0a2540] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Need <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?>?</h2>
            <p class="mt-3 text-white/75">Free fixed-price quotes. Install, service and trade products across the North West.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="#quote" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Request quote</a>
                <a href="<?= url('/shop/index.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Trade shop</a>
                <a href="<?= url('/pages/packages.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Packages</a>
                <a href="<?= url('/pages/landlords.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Landlords</a>
            </div>
        </div>
        <ul class="space-y-3 text-sm text-white/90">
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Based in Stockport — North West coverage</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Install, service &amp; certification for <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?></li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Multi-service packages for landlords &amp; FM teams</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Response aim: within 2 hours on business days</li>
        </ul>
    </div>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Quote</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
                <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> enquiry
            </h2>
        </div>
        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="<?= htmlspecialchars($mfrName . ' — install / service', ENT_QUOTES, 'UTF-8') ?>" selected><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> — install / service</option>
                    <option value="<?= htmlspecialchars($mfrName . ' — products / parts', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> — products / parts</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000"
                      placeholder="Model / panel type, postcode, install or parts needed…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit enquiry</button>
        </form>
    </div>
</section>
<?= shopifyBuyButtonScript() ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
