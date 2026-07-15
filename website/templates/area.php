<?php
/**
 * Area hub template. Placeholders: AREA, AREA_SLUG, AREA_URL
 */
$pageTitle = '{{AREA}} Property Compliance Services';
$metaDesc = '{{AREA}} experts for EICR, fire alarms, gas safety, emergency lighting, CCTV and access control. Fast local response from Stockport-based engineers. Free quotes.';
$metaKeywords = '{{AREA}} electrician, {{AREA}} fire alarm installation, {{AREA}} EICR, {{AREA}} gas safety certificate, property compliance {{AREA}}, emergency lighting {{AREA}}';
$ogImage = url('/assets/images/services/fire-alarms.jpg');

$allServices = getServices();
$allAreas = getAreas();
$areaName = '{{AREA}}';
$areaSlugVal = '{{AREA_SLUG}}';

$nearby = [];
$idx = array_search($areaName, $allAreas, true);
if ($idx === false) {
    $nearby = array_slice($allAreas, 0, 12);
} else {
    $start = max(0, $idx - 6);
    $nearby = array_slice($allAreas, $start, 14);
    $nearby = array_values(array_filter($nearby, function ($a) use ($areaName) {
        return $a !== $areaName;
    }));
    $nearby = array_slice($nearby, 0, 12);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require_once SITE_ROOT . '/includes/share.php';
$canonicalUrl = url('/pages/areas/' . $areaSlugVal . '.php');
require SITE_ROOT . '/includes/header.php';

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'LocalBusiness',
            'name' => SITE_NAME . ' — ' . $areaName,
            'description' => $metaDesc,
            'url' => url('/pages/areas/' . $areaSlugVal . '.php'),
            'telephone' => PHONE,
            'email' => EMAIL,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '17 Woodlands Park Road, Offerton',
                'addressLocality' => 'Stockport',
                'postalCode' => 'SK2 5DE',
                'addressCountry' => 'GB',
            ],
            'areaServed' => [
                '@type' => 'City',
                'name' => $areaName,
            ],
            'priceRange' => '££',
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Areas', 'item' => url('/pages/areas/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $areaName, 'item' => url('/pages/areas/' . $areaSlugVal . '.php')],
            ],
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Compliance services in ' . $areaName,
            'itemListElement' => (function () use ($allServices, $areaSlugVal, $areaName) {
                $items = [];
                $i = 0;
                foreach ($allServices as $slug => $name) {
                    $items[] = [
                        '@type' => 'ListItem',
                        'position' => ++$i,
                        'name' => $name . ' in ' . $areaName,
                        'url' => url('/pages/' . $slug . '/' . $areaSlugVal . '.php'),
                    ];
                }
                return $items;
            })(),
        ],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/areas/index.php') ?>" class="hover:text-white">Areas</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    Local engineers · {{AREA}}
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Property compliance in<br>
                    <span class="text-[#ff6b00]">{{AREA}}</span>
                </h1>
                <p class="mt-6 text-lg text-white/80 max-w-xl">
                    Electrical, fire alarms, gas safety, emergency lighting, CCTV and access control —
                    installed, tested and certified for properties in {{AREA}} and nearby postcodes.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get free quote</a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20{{AREA_URL}}"
                       target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                    <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                       class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block"><?= count($allServices) ?></span> core services</div>
                    <div><span class="text-white font-semibold text-xl block">Same-week</span> appointments*</div>
                    <div><span class="text-white font-semibold text-xl block">Fixed-price</span> quotes</div>
                </div>
                <p class="mt-3 text-[11px] text-white/40">*Subject to engineer capacity and site access.</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <?php
                $heroCards = array_slice($allServices, 0, 4, true);
                foreach ($heroCards as $slug => $name):
                ?>
                <a href="<?= url('/pages/' . $slug . '/{{AREA_SLUG}}.php') ?>"
                   class="group relative rounded-3xl overflow-hidden border border-white/10 min-h-[130px] bg-white/5 hover:border-[#ff6b00] transition">
                    <img src="<?= url('/assets/images/services/' . $slug . '.jpg') ?>" alt=""
                         class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-55 transition"
                         loading="lazy" onerror="this.style.display='none'">
                    <div class="relative p-4 h-full flex flex-col justify-end min-h-[130px]">
                        <div class="font-semibold text-white leading-tight"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-xs text-white/70 mt-1">in {{AREA}} →</div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- TRUST -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['Local to {{AREA}}', 'Engineers covering {{AREA}} and surrounding postcodes from Stockport'],
            ['Standards-led', 'BS 5839, BS 5266, BS 7671, gas safety & more'],
            ['Full paperwork', 'Certificates and logbooks for landlords, insurers & FM'],
            ['One team', 'Multi-service packages in a single visit schedule'],
        ];
        foreach ($trust as [$t, $d]): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- INTRO -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">About our {{AREA}} cover</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                Compliance engineers for {{AREA}}
            </h2>
            <p class="mt-5 text-lg text-zinc-700 leading-relaxed">
                Icomply Property Services provides complete property compliance for landlords, facilities managers,
                care providers and commercial occupiers in <strong>{{AREA}}</strong>. Whether you need an EICR,
                fire alarm service, gas safety certificate, emergency lighting test or a full multi-system install,
                we book local engineers with fixed-price quotes and clear documentation.
            </p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                Based in Offerton, Stockport (SK2 5DE), we routinely serve {{AREA}} and the wider North West with
                same-week appointments where capacity allows. Choose a service below for a dedicated
                <strong>{{AREA}}</strong> landing page, or request a package quote for several services at once.
            </p>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight">{{AREA}} compliance package</h3>
            <p class="mt-3 text-white/80">Combine EICR, fire alarms, emergency lighting and gas safety into one visit schedule for landlords and FM teams in {{AREA}}.</p>
            <ul class="mt-6 space-y-3 text-sm text-white/90">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Fixed-price multi-service quotes</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Full documentation for audits &amp; insurers</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Maintenance contracts available</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> WhatsApp or phone for a fast response</li>
            </ul>
            <a href="#quote" class="inline-block mt-8 px-6 py-3 bg-[#ff6b00] rounded-2xl font-semibold hover:bg-orange-600">Start your quote</a>
        </div>
    </div>
</section>

<!-- ALL SERVICES -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Services in {{AREA}}</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Everything we do locally</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Tap a service for install, service and certification details specific to {{AREA}}.</p>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All service hubs →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($allServices as $slug => $name):
                $blurb = getServiceBlurb($slug, true);
            ?>
            <a href="<?= url('/pages/' . $slug . '/{{AREA_SLUG}}.php') ?>"
               class="group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="h-36 bg-zinc-100 overflow-hidden">
                    <img src="<?= url('/assets/images/services/' . $slug . '.jpg') ?>"
                         alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> in {{AREA}}"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         loading="lazy"
                         onerror="this.parentElement.style.display='none'">
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="font-semibold text-lg text-black">
                        <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                        <span class="text-zinc-400 font-normal text-base">in {{AREA}}</span>
                    </h3>
                    <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="mt-4 text-sm font-semibold text-[#ff6b00]">View {{AREA}} page →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-12">How it works in {{AREA}}</h2>
    <div class="grid md:grid-cols-3 gap-8">
        <?php
        $steps = [
            ['1', 'Tell us the job', 'Service, postcode in or near {{AREA}}, panel brand or system type — form, phone or WhatsApp.'],
            ['2', 'Get a fixed quote', 'We confirm scope, standards and timeline for your {{AREA}} site. Clear price, no jargon.'],
            ['3', 'We deliver & certify', 'Engineers attend, complete the work and issue documentation for compliance.'],
        ];
        foreach ($steps as [$n, $t, $d]): ?>
        <div class="text-center px-4">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center text-lg"><?= $n ?></div>
            <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- NEARBY -->
<?php if (!empty($nearby)): ?>
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Nearby</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Other towns we cover</h2>
                <p class="mt-2 text-zinc-600">Also serving areas near {{AREA}} across the North West.</p>
            </div>
            <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All <?= count($allAreas) ?> areas →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($nearby as $town): ?>
                <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
                   class="px-5 py-2.5 bg-zinc-50 border rounded-full text-sm font-medium text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="bg-[#0a2540] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Ready for a {{AREA}} quote?</h2>
            <p class="mt-2 text-white/75">Chat on WhatsApp or call — we aim to respond within 2 hours on business days.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20{{AREA_URL}}"
               target="_blank" rel="noopener"
               class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp for {{AREA}}</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
               class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 pt-8">
    <?= shareButtonsHtml($areaName . ' Property Compliance', $metaDesc) ?>
</section>

<?php
require_once SITE_ROOT . '/includes/testimonials.php';
echo testimonialsSectionHtml();
?>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                Request your {{AREA}} quote
            </h2>
            <p class="mt-3 text-zinc-600">Include your {{AREA}} postcode, property type and any panel brands already on site.</p>
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
                    <option value="">Select service…</option>
                    <?php foreach ($allServices as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($name . ' in ' . $areaName, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package — {{AREA}}</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000"
                      placeholder="{{AREA}} postcode, property type, panel brand / system details…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
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
<?php require SITE_ROOT . '/includes/footer.php'; ?>
