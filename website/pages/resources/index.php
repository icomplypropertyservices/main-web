<?php
/**
 * Resources & guides hub — articles, keyword guides and audience landing links.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Resources & Guides | Property Compliance North West';
$metaDesc = 'Free property compliance guides for landlords, facilities managers and commercial sites — EICR, fire alarm servicing, emergency lighting testing, CCTV, access control, landlord checklists and keyword guides across the North West.';
$metaKeywords = 'property compliance guides, EICR guide, fire alarm servicing, emergency lighting testing, CCTV for business, access control guide, landlord compliance checklist, commercial fire safety North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/resources/index.php');

$services = getServices();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$featuredGuides = [
    [
        'slug' => 'eicr-guide',
        'title' => 'EICR guide for landlords & commercial sites',
        'blurb' => 'What an Electrical Installation Condition Report covers, typical intervals, report codes and how to book testing.',
        'tag' => 'Electrical',
        'img' => '/assets/images/services/electrical.jpg',
    ],
    [
        'slug' => 'fire-alarm-servicing',
        'title' => 'Fire alarm servicing explained',
        'blurb' => 'BS 5839 high-level guidance on user tests, periodic servicing, logbooks and when to upgrade.',
        'tag' => 'Fire alarms',
        'img' => '/assets/images/services/fire-alarms.jpg',
    ],
    [
        'slug' => 'landlord-compliance-checklist',
        'title' => 'Landlord compliance checklist',
        'blurb' => 'A practical overview of common safety certificates and checks for UK rented property portfolios.',
        'tag' => 'Landlords',
        'img' => '/assets/images/services/gas-systems.jpg',
    ],
    [
        'slug' => 'emergency-lighting-testing',
        'title' => 'Emergency lighting testing explained',
        'blurb' => 'Monthly function checks, annual full-duration tests, BS 5266 practice, logbooks and upgrade triggers.',
        'tag' => 'Emergency lighting',
        'img' => '/assets/images/services/emergency-lighting.jpg',
    ],
    [
        'slug' => 'cctv-for-business',
        'title' => 'CCTV for business',
        'blurb' => 'Planning commercial camera coverage, recording, remote viewing, maintenance and practical privacy steps.',
        'tag' => 'CCTV',
        'img' => '/assets/images/services/cctv.jpg',
    ],
    [
        'slug' => 'access-control-guide',
        'title' => 'Access control guide for business',
        'blurb' => 'Fobs, cards, biometrics, fire-safe door design, audit trails and day-to-day system management.',
        'tag' => 'Access control',
        'img' => '/assets/images/services/access-control.jpg',
    ],
];

$hubLinks = [
    [
        'href' => url('/pages/keywords/index.php'),
        'title' => 'Keyword guides',
        'blurb' => 'Browse installation, maintenance and certification topics across every service.',
        'cta' => 'Browse guides →',
    ],
    [
        'href' => url('/faq.php'),
        'title' => 'FAQ',
        'blurb' => 'Answers to common questions about testing intervals, certification and appointments.',
        'cta' => 'Read FAQ →',
    ],
    [
        'href' => url('/packages.php'),
        'title' => 'Packages',
        'blurb' => 'Multi-service compliance packages for landlords and facilities teams.',
        'cta' => 'View packages →',
    ],
    [
        'href' => url('/landlords.php'),
        'title' => 'Landlords',
        'blurb' => 'EICR, gas safety, fire alarms and emergency lighting for private rented stock.',
        'cta' => 'Landlord services →',
    ],
    [
        'href' => url('/commercial.php'),
        'title' => 'Commercial',
        'blurb' => 'Fire, electrical, gas and security compliance for commercial and multi-let sites.',
        'cta' => 'Commercial services →',
    ],
    [
        'href' => url('/pages/services/index.php'),
        'title' => 'All services',
        'blurb' => 'Full service directory — electrical, fire, gas, emergency lighting, CCTV and more.',
        'cta' => 'View services →',
    ],
];

require SITE_ROOT . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">Resources</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Guides · Checklists · Keyword topics
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Resources &amp;<br>
                <span class="text-[#ff6b00]">compliance guides</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
                Practical, high-level guidance for landlords, agents and facilities managers across Greater Manchester
                and the North West — plus links to packages, services and detailed keyword guides.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#articles" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Featured articles</a>
                <a href="#explore" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">Explore hub</a>
                <a href="#quote" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Free quote</a>
            </div>
        </div>
    </div>
</section>

<!-- DISCLAIMER STRIP -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-6">
        <p class="text-sm text-zinc-600 leading-relaxed max-w-4xl">
            <strong class="text-black">Important:</strong> Articles on this site provide general UK property compliance information only.
            They are not legal advice, a fire risk assessment or a substitute for competent person inspection of your premises.
            Always check current regulations for your property type and seek professional advice where required.
        </p>
    </div>
</section>

<!-- FEATURED ARTICLES -->
<section id="articles" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Featured articles</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Start with these guides</h2>
            <p class="mt-2 text-zinc-600 max-w-xl">Short, plain-English overviews you can share with owners, agents and FM teams.</p>
        </div>
        <a href="<?= url('/pages/keywords/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All keyword guides →</a>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
        <?php foreach ($featuredGuides as $g): ?>
        <a href="<?= url('/pages/resources/' . $g['slug'] . '.php') ?>"
           class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
            <div class="h-44 bg-zinc-100 overflow-hidden">
                <img src="<?= htmlspecialchars(url($g['img']), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($g['title'], ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                     loading="lazy"
                     onerror="this.parentElement.style.display='none'">
            </div>
            <div class="p-6 flex-1 flex flex-col">
                <div class="text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold"><?= htmlspecialchars($g['tag'], ENT_QUOTES, 'UTF-8') ?></div>
                <h2 class="font-semibold text-xl text-black tracking-tight mt-2"><?= htmlspecialchars($g['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($g['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="mt-5 text-sm font-semibold text-[#ff6b00]">Read article →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- EXPLORE HUB -->
<section id="explore" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Explore</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">More from Icomply</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">Jump to keyword guides, FAQ, packages and dedicated landlord or commercial pages — or browse the full service list.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($hubLinks as $link): ?>
            <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"
               class="bg-white border border-zinc-200 rounded-3xl p-7 hover:border-[#ff6b00] hover:shadow-lg transition group">
                <h3 class="font-semibold text-xl text-black tracking-tight group-hover:text-[#ff6b00] transition"><?= htmlspecialchars($link['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2"><?= htmlspecialchars($link['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="inline-block mt-5 text-sm font-semibold text-[#ff6b00]"><?= htmlspecialchars($link['cta'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- POPULAR KEYWORD TOPICS -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Keyword guides</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Popular compliance topics</h2>
        </div>
        <a href="<?= url('/pages/keywords/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Full keyword index →</a>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php
        $popularKw = [
            'eicr' => 'EICR',
            'electrical-installation-condition-report' => 'Electrical Installation Condition Report',
            'landlord-electrical-certificate' => 'Landlord electrical certificate',
            'fire-alarm-servicing' => 'Fire alarm servicing',
            'fire-alarm-maintenance' => 'Fire alarm maintenance',
            'bs-5839' => 'BS 5839',
            'bs-7671' => 'BS 7671',
            'emergency-lighting-testing' => 'Emergency lighting testing',
            'gas-safety-certificate' => 'Gas safety certificate',
            'landlord-gas-safety-certificate' => 'Landlord gas safety certificate',
            'pat-testing' => 'PAT testing',
            'commercial-fire-alarm-system' => 'Commercial fire alarm system',
        ];
        foreach ($popularKw as $slug => $label):
        ?>
            <a href="<?= url('/pages/keywords/' . $slug . '.php') ?>"
               class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- SERVICES STRIP -->
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Services</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Need work completing?</h2>
                <p class="mt-4 text-zinc-600 text-lg">
                    We install, maintain, test and certify electrical, fire, gas, emergency lighting, AOV, nurse call,
                    CCTV and access control systems across the North West.
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <?php foreach (array_slice($services, 0, 6, true) as $sSlug => $sName): ?>
                        <a href="<?= url('/pages/services/' . $sSlug . '.php') ?>"
                           class="px-4 py-2 bg-zinc-50 border rounded-full text-sm hover:border-[#ff6b00]">
                            <?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <a href="<?= url('/pages/services/index.php') ?>" class="inline-block mt-6 text-sm font-semibold text-[#ff6b00]">All services →</a>
            </div>
            <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
                <h3 class="text-2xl font-semibold">Talk to a local engineer</h3>
                <p class="mt-3 text-white/80">Fixed-price quotes, clear documentation and same-week appointments where capacity allows.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                       class="px-6 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener"
                       class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
                    <a href="#quote" class="px-6 py-3 rounded-2xl border border-white/30 font-semibold hover:bg-white/10">Quote form</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request your free quote</h2>
            <p class="mt-3 text-zinc-600">Tell us the property type, postcode and what needs testing or installing.</p>
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
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                    <option value="Landlord compliance">Landlord compliance</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Postcode, property type, certificates needed…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
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
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
