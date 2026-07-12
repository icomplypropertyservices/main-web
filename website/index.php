<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/seo.php';
$pageTitle = 'Property Compliance North West | EICR & Fire';
$metaDesc = 'Stockport UK compliance for Greater Manchester & North West. EICR, fire alarms, gas, emergency lighting, CCTV. Free quote.';
$metaKeywords = 'property compliance Manchester, EICR Stockport, fire alarm installation Bolton, emergency lighting Oldham, gas safety certificate Rochdale, CCTV Manchester, UK compliance contractors North West';
$canonicalUrl = site_url('index.php');
require 'includes/header.php';
$homeFaqs = [
    ['q' => 'What areas does Icomply cover?', 'a' => 'We cover Greater Manchester and 150+ North West towns from our Stockport SK2 base — including Manchester, Bolton, Oldham, Rochdale, Liverpool, Preston, Warrington, Chester and Blackpool.'],
    ['q' => 'Which compliance services do you offer?', 'a' => 'Electrical (including EICR), fire alarms, emergency lighting, AOV & air handling, nurse call, gas systems, intruder alarms, CCTV, access control, door entry and intercoms.'],
    ['q' => 'How fast can I get a quote?', 'a' => 'Most enquiries receive a response within 2 business hours. Share the postcode, property type and service needed for the quickest fixed-price style quote.'],
    ['q' => 'Do you provide certificates for landlords and insurers?', 'a' => 'Yes. We issue clear documentation and certificates suitable for landlords, managing agents, freeholders and insurers after testing and commissioning.'],
];
?>

<!-- HERO with UK photo -->
<section class="relative min-h-[78vh] flex items-center text-white overflow-hidden">
    <img src="/assets/images/heroes/home-hero.jpg" alt="UK commercial property in Greater Manchester" class="absolute inset-0 w-full h-full object-cover" width="1600" height="900" fetchpriority="high">
    <div class="absolute inset-0 hero-overlay"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-24 w-full">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-xs badge-uk mb-6">UNITED KINGDOM · GREATER MANCHESTER & NORTH WEST</div>
            <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight leading-[1.05] mb-6">North West property compliance.<br><span class="text-[#ff6b00]">EICR, fire, gas &amp; more.</span></h1>
            <p class="text-lg md:text-xl text-white/85 leading-relaxed max-w-xl">Certified UK engineers for EICR, fire alarms, gas safety, emergency lighting, AOV, nurse call, CCTV and access control — across 150+ North West towns.</p>
            <div class="mt-10 flex flex-wrap gap-4">
                <a href="#quote" class="accent-btn px-8 py-4 rounded-2xl font-semibold text-lg">Get a free quote</a>
                <a href="/pages/services/index" class="px-8 py-4 rounded-2xl border border-white/50 font-semibold text-lg hover:bg-white/10">Browse services</a>
                <a href="tel:<?= PHONE ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold text-lg">Call <?= PHONE ?></a>
            </div>
            <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                <span>Based in Stockport SK2</span>
                <span>Same-week appointments</span>
                <span>Fixed-price quotes</span>
            </div>
        </div>
    </div>
</section>

<!-- Trust strip -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
        <div><div class="text-3xl font-extrabold text-[#0a2540]">11</div><div class="text-sm text-zinc-500 mt-1">Compliance services</div></div>
        <div><div class="text-3xl font-extrabold text-[#0a2540]">150+</div><div class="text-sm text-zinc-500 mt-1">North West towns</div></div>
        <div><div class="text-3xl font-extrabold text-[#0a2540]">2hr</div><div class="text-sm text-zinc-500 mt-1">Typical quote response</div></div>
        <div><div class="text-3xl font-extrabold text-[#0a2540]">UK</div><div class="text-sm text-zinc-500 mt-1">British Standards focus</div></div>
    </div>
</section>

<!-- Services with real photos -->
<section class="max-w-7xl mx-auto px-6 py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-12">
        <div>
            <div class="uppercase text-[#ff6b00] tracking-[.2em] text-xs font-semibold">Our expertise</div>
            <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight mt-2">UK compliance services</h2>
            <p class="mt-3 text-zinc-600 max-w-xl">Installation, maintenance and certification for landlords, property managers and businesses across Greater Manchester and the North West.</p>
        </div>
        <a href="/pages/services/index" class="text-[#ff6b00] font-semibold hover:underline">View all services →</a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($services as $slug => $name): ?>
        <a href="/pages/services/<?= htmlspecialchars($slug) ?>" class="service-card bg-white rounded-3xl border group">
            <div class="aspect-[16/10] overflow-hidden bg-zinc-100">
                <img src="/assets/images/services/<?= htmlspecialchars($slug) ?>-photo.jpg"
                     alt="<?= htmlspecialchars($name) ?> services in the UK — North West"
                     class="img-cover group-hover:scale-105 transition duration-500"
                     width="640" height="400" loading="lazy"
                     onerror="this.src='/assets/images/services/<?= htmlspecialchars($slug) ?>.png'">
            </div>
            <div class="p-6">
                <div class="font-bold text-xl tracking-tight"><?= htmlspecialchars($name) ?></div>
                <p class="text-sm text-zinc-600 mt-2 leading-relaxed"><?= htmlspecialchars(service_blurb($slug)) ?></p>
                <div class="mt-4 text-sm font-semibold text-[#ff6b00]">Explore service →</div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- UK coverage band -->
<section class="relative text-white py-20 overflow-hidden">
    <img src="/assets/images/heroes/manchester.jpg" alt="Manchester and North West UK skyline" class="absolute inset-0 w-full h-full object-cover" width="1600" height="900" loading="lazy">
    <div class="absolute inset-0 bg-[#0a2540]/85"></div>
    <div class="relative max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <div class="uppercase text-[#ff6b00] tracking-[.2em] text-xs font-semibold">Local UK coverage</div>
            <h2 class="text-4xl font-extrabold tracking-tight mt-3">Engineers across Greater Manchester &amp; the North West</h2>
            <p class="mt-4 text-white/80 text-lg leading-relaxed">From Stockport and Manchester to Liverpool, Preston, Blackpool and Chester — we send local engineers who know UK building regs and British Standards.</p>
            <div class="mt-8 flex flex-wrap gap-2">
                <?php foreach (['Manchester','Stockport','Bolton','Oldham','Rochdale','Liverpool','Preston','Warrington'] as $a): ?>
                    <a href="/pages/fire-alarms/<?= areaSlug($a) ?>" class="px-4 py-2 rounded-full bg-white/10 border border-white/20 text-sm hover:bg-white/20"><?= $a ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="rounded-3xl overflow-hidden border border-white/20 shadow-2xl">
            <img src="/assets/images/heroes/uk-engineer.jpg" alt="UK property compliance engineer at work" class="w-full h-80 object-cover" width="800" height="520" loading="lazy">
        </div>
    </div>
</section>

<!-- Why us -->
<section class="bg-white py-20 border-y">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
            <div class="uppercase text-[#ff6b00] tracking-[.2em] text-xs font-semibold">Why Icomply</div>
            <h2 class="text-4xl font-extrabold tracking-tight mt-2">Built for UK landlords &amp; businesses</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="p-8 rounded-3xl border bg-zinc-50">
                <img src="/assets/images/services/fire-alarms-photo.jpg" alt="Fire safety compliance" class="w-full h-40 object-cover rounded-2xl mb-6" loading="lazy">
                <h3 class="font-bold text-xl">British Standards first</h3>
                <p class="text-zinc-600 mt-2 text-sm leading-relaxed">Work aligned to BS 7671, BS 5839, BS 5266 and relevant UK gas &amp; fire guidance — documentation ready for insurers.</p>
            </div>
            <div class="p-8 rounded-3xl border bg-zinc-50">
                <img src="/assets/images/heroes/about-team.jpg" alt="UK trades professionals" class="w-full h-40 object-cover rounded-2xl mb-6" loading="lazy">
                <h3 class="font-bold text-xl">Local Stockport base</h3>
                <p class="text-zinc-600 mt-2 text-sm leading-relaxed">North West coverage with a fixed address in Offerton, Stockport. Real engineers, fixed-price quotes, fast call-backs.</p>
            </div>
            <div class="p-8 rounded-3xl border bg-zinc-50">
                <img src="/assets/images/services/cctv-photo.jpg" alt="CCTV and security systems" class="w-full h-40 object-cover rounded-2xl mb-6" loading="lazy">
                <h3 class="font-bold text-xl">One contractor, many systems</h3>
                <p class="text-zinc-600 mt-2 text-sm leading-relaxed">Fire, electrical, gas, emergency lighting, nurse call, CCTV and access — coordinated under one UK compliance partner.</p>
            </div>
        </div>
    </div>
</section>

<!-- About Icomply — expertise & NAP -->
<section id="about" class="bg-white border-y py-20" aria-labelledby="about-heading">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-5 gap-10 lg:gap-14 items-start">
            <div class="lg:col-span-3">
                <div class="uppercase text-[#ff6b00] tracking-[.2em] text-xs font-semibold">About Icomply</div>
                <h2 id="about-heading" class="text-4xl font-extrabold tracking-tight mt-2">UK property compliance expertise from Stockport</h2>
                <p class="mt-5 text-zinc-700 leading-relaxed">Icomply Property Services is a North West compliance contractor helping landlords, letting agents, facilities managers and business owners keep buildings safe and audit-ready. Our engineers deliver installation, servicing and certification across electrical (EICR), fire detection, gas safety, emergency lighting, AOV, nurse call, CCTV, access control, door entry and intercoms.</p>
                <p class="mt-3 text-zinc-700 leading-relaxed">We work to recognised British Standards — including BS 7671, BS 5839 and BS 5266 — and provide clear documentation suitable for insurers, freeholders and managing agents. One coordinated UK team covers Greater Manchester and 150+ surrounding towns, so multi-system sites do not need a stack of separate contractors.</p>
                <div class="mt-8 grid sm:grid-cols-2 gap-4">
                    <div class="p-5 rounded-2xl border bg-zinc-50">
                        <div class="font-bold text-[#0a2540]">Standards-led work</div>
                        <p class="text-sm text-zinc-600 mt-1 leading-relaxed">Inspections, installs and certificates aligned to current UK practice and insurer expectations.</p>
                    </div>
                    <div class="p-5 rounded-2xl border bg-zinc-50">
                        <div class="font-bold text-[#0a2540]">Regional coverage</div>
                        <p class="text-sm text-zinc-600 mt-1 leading-relaxed">Local engineers across Manchester, Bolton, Oldham, Rochdale, Liverpool, Preston, Chester and more.</p>
                    </div>
                    <div class="p-5 rounded-2xl border bg-zinc-50">
                        <div class="font-bold text-[#0a2540]">Multi-system partner</div>
                        <p class="text-sm text-zinc-600 mt-1 leading-relaxed">Fire, electrical, gas, nurse call, CCTV and access coordinated under one compliance contact.</p>
                    </div>
                    <div class="p-5 rounded-2xl border bg-zinc-50">
                        <div class="font-bold text-[#0a2540]">Fast, fixed-price quotes</div>
                        <p class="text-sm text-zinc-600 mt-1 leading-relaxed">Typical response within 2 business hours once postcode, property type and service are shared.</p>
                    </div>
                </div>
            </div>
            <aside class="lg:col-span-2">
                <div class="rounded-3xl border bg-zinc-50 p-8 shadow-sm">
                    <div class="uppercase text-[#ff6b00] tracking-[.2em] text-xs font-semibold">Visit &amp; contact</div>
                    <h3 class="text-2xl font-extrabold tracking-tight mt-2 text-[#0a2540]"><?= SITE_NAME ?></h3>
                    <p class="mt-4 text-sm text-zinc-600 leading-relaxed">Speak to our Stockport team about testing, installation or multi-site compliance programmes.</p>
                    <dl class="mt-6 space-y-5 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Address</dt>
                            <dd class="mt-1 text-zinc-800 leading-relaxed"><?= ADDRESS ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Phone</dt>
                            <dd class="mt-1"><a href="tel:<?= PHONE ?>" class="text-[#ff6b00] font-semibold text-lg hover:underline"><?= PHONE ?></a></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Email</dt>
                            <dd class="mt-1"><a href="mailto:<?= EMAIL ?>" class="text-zinc-800 font-medium hover:text-[#ff6b00]"><?= EMAIL ?></a></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Hours</dt>
                            <dd class="mt-1 text-zinc-800">Mon–Fri 08:00–18:00</dd>
                        </div>
                    </dl>
                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="#quote" class="accent-btn text-center px-6 py-3 rounded-2xl font-semibold">Request a quote</a>
                        <a href="tel:<?= PHONE ?>" class="text-center px-6 py-3 rounded-2xl border border-[#0a2540]/15 font-semibold text-[#0a2540] hover:bg-white">Call now</a>
                    </div>
                </div>
                <div class="mt-6 rounded-3xl overflow-hidden border shadow-sm">
                    <img src="/assets/images/heroes/about-team.jpg" alt="Icomply Property Services team — UK compliance engineers" class="w-full h-48 object-cover" width="640" height="320" loading="lazy">
                </div>
            </aside>
        </div>
        <?= render_faq_section($homeFaqs, 'Common questions about our UK compliance services') ?>
    </div>
</section>

<!-- Quote -->
<section id="quote" class="py-20">
    <div class="max-w-6xl mx-auto px-6 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <div class="uppercase text-[#ff6b00] tracking-[.2em] text-xs font-semibold">Free quote</div>
            <h2 id="quote-heading" class="text-4xl font-extrabold tracking-tight mt-2">Tell us about your property</h2>
            <p class="mt-3 text-zinc-600 text-lg">We respond within 2 hours on business days. Quotes are fixed-price where possible.</p>
            <div class="mt-8 rounded-3xl overflow-hidden shadow-lg">
                <img src="/assets/images/heroes/contact-hero.jpg" alt="Modern UK commercial buildings" class="w-full h-64 object-cover" loading="lazy">
            </div>
        </div>
        <form action="/contact" method="POST" class="bg-white p-8 md:p-10 rounded-3xl border shadow-sm space-y-5" aria-labelledby="quote-heading">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quote-name" class="block text-xs font-semibold text-zinc-500 mb-1">Full name</label>
                    <input type="text" id="quote-name" name="name" required class="w-full border px-4 py-3 rounded-xl">
                </div>
                <div>
                    <label for="quote-email" class="block text-xs font-semibold text-zinc-500 mb-1">Email</label>
                    <input type="email" id="quote-email" name="email" required class="w-full border px-4 py-3 rounded-xl">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quote-phone" class="block text-xs font-semibold text-zinc-500 mb-1">Phone</label>
                    <input type="tel" id="quote-phone" name="phone" class="w-full border px-4 py-3 rounded-xl" placeholder="<?= PHONE ?>">
                </div>
                <div>
                    <label for="quote-service" class="block text-xs font-semibold text-zinc-500 mb-1">Service</label>
                    <select id="quote-service" name="service" required class="w-full border px-4 py-3 rounded-xl bg-white">
                        <option value="">Select service…</option>
                        <?php foreach ($services as $slug => $name): ?>
                            <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label for="quote-message" class="block text-xs font-semibold text-zinc-500 mb-1">Property details &amp; postcode</label>
                <textarea id="quote-message" name="message" rows="4" class="w-full border px-4 py-3 rounded-xl" placeholder="e.g. 3-storey office in Stockport SK2 needing EICR + fire alarms"></textarea>
            </div>
            <button type="submit" class="w-full accent-btn py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">We only use your details to respond to this enquiry.</p>
        </form>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
