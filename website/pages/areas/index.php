<?php
/**
 * Areas directory — lists every town hub with conversion-focused layout.
 */
require_once __DIR__ . '/../../config.php';

$areas = getAreas();
$services = getServices();

$pageTitle = 'Areas We Cover | ' . count($areas) . '+ North West Towns';
$metaDesc = 'Icomply covers ' . count($areas) . '+ towns across Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria. Find EICR, fire alarms, gas safety and more near you.';
$metaKeywords = 'property compliance North West, electrician Manchester, fire alarms Stockport, gas safety Bolton, emergency lighting Liverpool, CCTV Preston';
$ogImage = url('/assets/images/services/fire-alarms.jpg');

$serviceBlurbs = [
    'electrical' => 'EICR, rewires, EV chargers',
    'fire-alarms' => 'BS 5839 install & service',
    'emergency-lighting' => 'BS 5266 testing',
    'gas-systems' => 'CP12 / CP44 certificates',
];

$featured = array_values(array_filter(
    ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool'],
    function ($t) use ($areas) {
        return in_array($t, $areas, true);
    }
));

// Group areas alphabetically for scannability
$byLetter = [];
foreach ($areas as $area) {
    $letter = strtoupper(substr($area, 0, 1));
    if (!isset($byLetter[$letter])) {
        $byLetter[$letter] = [];
    }
    $byLetter[$letter][] = $area;
}
ksort($byLetter);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">Areas</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    North West coverage
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Areas we<br>
                    <span class="text-[#ff6b00]">cover</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    Local engineers serving <strong class="text-white"><?= count($areas) ?> towns</strong> across
                    Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria. Pick your area for full service links.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#directory" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Browse towns</a>
                    <a href="<?= url('/pages/services/index.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">Browse services</a>
                    <a href="#quote" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Free quote</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block"><?= count($areas) ?>+</span> towns</div>
                    <div><span class="text-white font-semibold text-xl block"><?= count($services) ?></span> services each</div>
                    <div><span class="text-white font-semibold text-xl block">Stockport</span> base (SK2)</div>
                </div>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-3xl p-6 md:p-8">
                <div class="text-xs uppercase tracking-[3px] text-white/50 mb-4">Popular towns</div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($featured as $town): ?>
                        <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
                           class="px-4 py-2 rounded-full bg-white/10 text-sm font-medium hover:bg-[#ff6b00] transition">
                            <?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <p class="mt-6 text-sm text-white/50">Plus <?= max(0, count($areas) - count($featured)) ?> more towns in the full directory below.</p>
            </div>
        </div>
    </div>
</section>

<!-- TRUST -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['Local response', 'Stockport engineers covering your postcode'],
            ['All major services', count($services) . ' compliance services in every town hub'],
            ['Fixed-price quotes', 'Clear scope before work starts'],
            ['Full certification', 'Paperwork for landlords, insurers & FM'],
        ];
        foreach ($trust as [$t, $d]): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars((string)$d, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- FEATURED AREA CARDS -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Featured</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Major towns</h2>
            <p class="mt-2 text-zinc-600">High-demand coverage areas with full service menus.</p>
        </div>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach (array_slice($featured, 0, 8) as $town): ?>
            <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
               class="group bg-white border rounded-3xl p-6 hover:border-[#ff6b00] hover:shadow-lg transition">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540] text-white flex items-center justify-center font-bold text-sm mb-4">
                    <?= htmlspecialchars(strtoupper(substr($town, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <h3 class="font-semibold text-xl text-black"><?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2"><?= count($services) ?> services · local engineers</p>
                <span class="mt-4 inline-block text-sm font-semibold text-[#ff6b00] group-hover:underline">View <?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?> →</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- POPULAR COMBOS -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Service × area</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Popular local pages</h2>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php
            $showcase = array_slice($featured, 0, 5);
            $topServices = array_slice($services, 0, 4, true);
            foreach ($showcase as $a):
                foreach ($topServices as $sSlug => $sName):
            ?>
                <a href="<?= url('/pages/' . $sSlug . '/' . areaSlug($a) . '.php') ?>"
                   class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($sName . ' in ' . $a, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; endforeach; ?>
        </div>
    </div>
</section>

<!-- FULL DIRECTORY A–Z -->
<section id="directory" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="mb-10">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Directory</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">All <?= count($areas) ?> towns</h2>
        <p class="mt-2 text-zinc-600">A–Z list of every area hub. Each page links to all <?= count($services) ?> services for that town.</p>
    </div>

    <!-- Letter jump -->
    <div class="flex flex-wrap gap-1.5 mb-10 sticky top-0 bg-zinc-50/95 backdrop-blur py-3 z-10 border-b">
        <?php foreach (array_keys($byLetter) as $letter): ?>
            <a href="#letter-<?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>"
               class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-semibold bg-white border hover:border-[#ff6b00] hover:text-[#ff6b00] transition">
                <?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="space-y-12">
        <?php foreach ($byLetter as $letter => $towns): ?>
            <div id="letter-<?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-[#0a2540] text-white font-bold text-xl flex items-center justify-center"><?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="h-px flex-1 bg-zinc-200"></div>
                    <div class="text-xs text-zinc-400"><?= count($towns) ?> town<?= count($towns) === 1 ? '' : 's' ?></div>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <?php foreach ($towns as $area): ?>
                        <a href="<?= url('/pages/areas/' . areaSlug($area) . '.php') ?>"
                           class="px-4 py-3 bg-white border rounded-2xl text-sm font-medium text-black hover:border-[#ff6b00] hover:shadow-sm transition flex justify-between items-center gap-2">
                            <span><?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-[#ff6b00] opacity-0 group-hover:opacity-100">→</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CTA -->
<section class="bg-[#0a2540] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Don't see your town?</h2>
            <p class="mt-2 text-white/75">We still cover most of the North West — message us with your postcode and we'll confirm.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>"
               target="_blank" rel="noopener"
               class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
               class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request your free quote</h2>
            <p class="mt-3 text-zinc-600">Include your town or postcode so we can book the nearest engineer.</p>
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
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Town / postcode, property type, panel brand…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
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
