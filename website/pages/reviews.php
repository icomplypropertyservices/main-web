<?php
/**
 * Reviews & testimonials — expanded client feedback, how we work, leave a Google review.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/testimonials.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Reviews & Testimonials | Icomply Property Services';
$metaDesc = 'Read client reviews of Icomply Property Services — landlords, agents and facilities teams on EICR, fire alarms, gas safety, CCTV and compliance work across Greater Manchester and the North West. Leave a Google review.';
$metaKeywords = 'Icomply reviews, property compliance testimonials, EICR reviews Stockport, fire alarm engineer reviews Manchester, Google review Icomply, landlord compliance feedback North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/reviews.php');

$services = getServices();
$areas = getAreas();
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);

// Google Business profile — config SOCIAL_GOOGLE, with safe placeholder fallback
$googleReviewUrl = (defined('SOCIAL_GOOGLE') && SOCIAL_GOOGLE !== '')
    ? (string) SOCIAL_GOOGLE
    : 'https://g.page/icomply-property-services';

/**
 * Extra reviews beyond includes/testimonials.php (same shape: quote, name, role, rating).
 *
 * @return list<array{quote:string, name:string, role:string, rating:int}>
 */
$extraTestimonials = [
    [
        'quote' => 'Emergency lighting duration test and logbook update for our office block in Salford. Engineer knew BS 5266 inside out, replaced two failed fittings on the spot and emailed the certificates the same afternoon.',
        'name' => 'Helen',
        'role' => 'Office manager, Salford',
        'rating' => 5,
    ],
    [
        'quote' => 'Portfolio of 12 HMOs needed EICRs and smoke alarm checks before renewals. Icomply scheduled two days of visits, kept our agent portal updated and chased nothing — the paperwork just arrived.',
        'name' => 'David',
        'role' => 'Portfolio landlord, Oldham',
        'rating' => 5,
    ],
    [
        'quote' => 'Paxton access control for a care home entrance plus nurse-call survey on the same trip. Clear quotation, tidy cabling and handover training for night staff. Highly recommended for care operators.',
        'name' => 'Aisha',
        'role' => 'Care home manager, Rochdale',
        'rating' => 5,
    ],
    [
        'quote' => 'Reactive fire panel fault on a retail unit in Preston — they got an engineer out the next morning, found a device issue and left us with a clean service ticket for the insurer.',
        'name' => 'Tom',
        'role' => 'Retail facilities, Lancashire',
        'rating' => 5,
    ],
    [
        'quote' => 'Intruder alarm and CCTV refresh for our industrial unit near Wigan. Scope matched the quote, no extras, and remote viewing works on both directors’ phones. Solid job from start to finish.',
        'name' => 'Claire',
        'role' => 'Operations director, Wigan',
        'rating' => 5,
    ],
    [
        'quote' => 'Annual gas safety (CP12) across our agency’s Stockport stock. Fixed diary each quarter, polite engineers and certificates that land in the right inbox first time.',
        'name' => 'Ben',
        'role' => 'Lettings branch manager, Stockport',
        'rating' => 5,
    ],
    [
        'quote' => 'AOV smoke vent service for a residential block in Liverpool. They explained the defects in plain English for the freeholder meeting and priced the remediations fairly.',
        'name' => 'Louise',
        'role' => 'Block manager, Merseyside',
        'rating' => 5,
    ],
    [
        'quote' => 'Consumer unit upgrade and EICR remedial after a C2 on a Victorian terrace in Chester. Neat install, full certification and they protected the décor better than most sparks we’ve used.',
        'name' => 'Neil',
        'role' => 'Homeowner / landlord, Cheshire',
        'rating' => 5,
    ],
];

$allTestimonials = array_merge(getTestimonials(), $extraTestimonials);

$howWeWork = [
    [
        'n' => '1',
        'title' => 'Tell us the job',
        'text' => 'Service, postcode, panel brand or system type — via form, phone or WhatsApp. Photos of consumer units, fire panels or plant rooms help us quote faster.',
    ],
    [
        'n' => '2',
        'title' => 'Fixed-price quote',
        'text' => 'We confirm scope, standards and timeline. Clear price, no jargon — revised only if site conditions differ from what was described.',
    ],
    [
        'n' => '3',
        'title' => 'Engineers attend',
        'text' => 'Local Stockport-based engineers complete install, service or certification work to the relevant British Standards.',
    ],
    [
        'n' => '4',
        'title' => 'Docs & follow-up',
        'text' => 'Certificates, logbook updates and remedial advice land promptly so insurers, agents and auditors have what they need.',
    ],
];

$trust = [
    ['title' => '5★ client feedback', 'text' => 'Landlords, agents and FM teams across the North West'],
    ['title' => 'Standards-led', 'text' => 'BS 7671, BS 5839, BS 5266, gas safety & more'],
    ['title' => 'Fixed-price quotes', 'text' => 'Clear scope, documentation and certification'],
    ['title' => count($areas) . '+ towns', 'text' => 'Greater Manchester, Cheshire, Lancs, Merseyside & Cumbria'],
];

// AggregateRating schema from testimonials
$sum = 0;
foreach ($allTestimonials as $t) {
    $sum += (int) ($t['rating'] ?? 5);
}
$avgRating = count($allTestimonials) > 0 ? round($sum / count($allTestimonials), 1) : 5.0;

$reviewEntities = [];
foreach ($allTestimonials as $t) {
    $reviewEntities[] = [
        '@type' => 'Review',
        'reviewBody' => $t['quote'],
        'reviewRating' => [
            '@type' => 'Rating',
            'ratingValue' => (int) ($t['rating'] ?? 5),
            'bestRating' => 5,
            'worstRating' => 1,
        ],
        'author' => [
            '@type' => 'Person',
            'name' => $t['name'],
        ],
    ];
}

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebPage',
            '@id' => url('/pages/reviews.php') . '#webpage',
            'url' => url('/pages/reviews.php'),
            'name' => $pageTitle,
            'description' => $metaDesc,
            'isPartOf' => ['@type' => 'WebSite', 'name' => SITE_NAME, 'url' => SITE_URL],
        ],
        [
            '@type' => 'LocalBusiness',
            '@id' => url('/pages/reviews.php') . '#business',
            'name' => SITE_NAME,
            'url' => SITE_URL,
            'telephone' => PHONE,
            'email' => EMAIL,
            'image' => $ogImage,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '17 Woodlands Park Road',
                'addressLocality' => 'Offerton, Stockport',
                'addressRegion' => 'Greater Manchester',
                'postalCode' => 'SK2 5DE',
                'addressCountry' => 'GB',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $avgRating,
                'reviewCount' => (string) count($allTestimonials),
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            'review' => $reviewEntities,
            'sameAs' => array_values(array_filter([
                defined('SOCIAL_FACEBOOK') ? SOCIAL_FACEBOOK : '',
                defined('SOCIAL_INSTAGRAM') ? SOCIAL_INSTAGRAM : '',
                defined('SOCIAL_LINKEDIN') ? SOCIAL_LINKEDIN : '',
                defined('SOCIAL_TWITTER') ? SOCIAL_TWITTER : '',
                $googleReviewUrl,
            ])),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Reviews', 'item' => url('/pages/reviews.php')],
            ],
        ],
    ],
];

$stars = static function (int $n): string {
    $n = max(1, min(5, $n));
    return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
};

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">Reviews</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    Client feedback · North West
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Reviews &amp;<br>
                    <span class="text-[#ff6b00]">testimonials</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    What landlords, letting agents and facilities teams say about our electrical, fire, gas,
                    emergency lighting, CCTV and access work across Greater Manchester and the North West.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#testimonials" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Read reviews</a>
                    <a href="<?= htmlspecialchars($googleReviewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"
                       class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">Leave a Google review</a>
                    <a href="<?= url('/contact.php') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Free quote</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div>
                        <span class="text-white font-semibold text-xl block"><?= htmlspecialchars((string) $avgRating, ENT_QUOTES, 'UTF-8') ?>★</span>
                        Average rating
                    </div>
                    <div>
                        <span class="text-white font-semibold text-xl block"><?= count($allTestimonials) ?></span>
                        Featured reviews
                    </div>
                    <div>
                        <span class="text-white font-semibold text-xl block"><?= count($areas) ?>+</span>
                        Towns covered
                    </div>
                </div>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-3xl p-8 md:p-10 backdrop-blur-sm">
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Google reviews</div>
                <h2 class="text-2xl font-semibold tracking-tight">Happy with our work?</h2>
                <p class="mt-3 text-white/75 text-sm leading-relaxed">
                    A short Google review helps other landlords and facilities managers find a reliable compliance partner —
                    and helps our Stockport team keep improving.
                </p>
                <ul class="mt-6 space-y-3 text-sm text-white/90">
                    <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Takes under a minute on Google</li>
                    <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Mention the service and town if you can</li>
                    <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Or call / WhatsApp if something wasn’t right — we’ll put it right</li>
                </ul>
                <a href="<?= htmlspecialchars($googleReviewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"
                   class="inline-block mt-8 px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">
                    Leave a Google review →
                </a>
                <p class="mt-4 text-[11px] text-white/40 break-all">
                    <?= htmlspecialchars($googleReviewUrl, ENT_QUOTES, 'UTF-8') ?>
                </p>
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

<!-- TESTIMONIALS (expanded from includes/testimonials.php) -->
<section id="testimonials" class="bg-white border-t" aria-labelledby="reviews-heading">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Testimonials</div>
                <h2 id="reviews-heading" class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What clients say</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">
                    Expanded feedback from landlords, agents and facilities teams on compliance, certification and install work —
                    including the reviews featured on our homepage.
                </p>
            </div>
            <a href="<?= htmlspecialchars(url('/contact.php'), ENT_QUOTES, 'UTF-8') ?>" class="text-sm font-semibold text-[#ff6b00]">Request a quote →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($allTestimonials as $t):
                $quote = htmlspecialchars($t['quote'], ENT_QUOTES, 'UTF-8');
                $name = htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8');
                $role = htmlspecialchars($t['role'], ENT_QUOTES, 'UTF-8');
                $rating = (int) ($t['rating'] ?? 5);
                $initial = htmlspecialchars(mb_strtoupper(mb_substr($t['name'], 0, 1)), ENT_QUOTES, 'UTF-8');
            ?>
            <blockquote class="bg-zinc-50 border border-zinc-200 rounded-3xl p-6 flex flex-col hover:border-[#ff6b00] transition">
                <div class="text-[#ff6b00] text-sm tracking-wide mb-3" aria-label="<?= $rating ?> out of 5 stars"><?= $stars($rating) ?></div>
                <p class="text-sm text-zinc-700 leading-relaxed flex-1">“<?= $quote ?>”</p>
                <footer class="mt-6 pt-4 border-t border-zinc-200 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#0a2540] text-white font-semibold flex items-center justify-center shrink-0" aria-hidden="true"><?= $initial ?></div>
                    <div>
                        <cite class="not-italic font-semibold text-black text-sm"><?= $name ?></cite>
                        <div class="text-xs text-zinc-500 mt-0.5"><?= $role ?></div>
                    </div>
                </footer>
            </blockquote>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- HOW WE WORK -->
<section id="how-we-work" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Process</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">How we work</h2>
            <p class="mt-3 text-zinc-600">
                The same straightforward process our clients describe in their reviews — from first message to certificates in the inbox.
            </p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($howWeWork as $step): ?>
            <div class="bg-white border border-zinc-200 rounded-3xl p-6 hover:border-[#ff6b00] transition text-center sm:text-left">
                <div class="w-12 h-12 mx-auto sm:mx-0 rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center text-lg"><?= htmlspecialchars($step['n'], ENT_QUOTES, 'UTF-8') ?></div>
                <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($step['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-12 grid md:grid-cols-3 gap-5">
            <div class="bg-white border rounded-3xl p-6">
                <div class="font-semibold text-black">One point of contact</div>
                <p class="mt-2 text-sm text-zinc-600">Multi-service packages (EICR, fire, gas, lighting) under a single coordinator — fewer access days for tenants and site teams.</p>
            </div>
            <div class="bg-white border rounded-3xl p-6">
                <div class="font-semibold text-black">Audit-ready paperwork</div>
                <p class="mt-2 text-sm text-zinc-600">Certificates and reports formatted for insurers, freeholders, agents and internal compliance files.</p>
            </div>
            <div class="bg-white border rounded-3xl p-6">
                <div class="font-semibold text-black">Local North West cover</div>
                <p class="mt-2 text-sm text-zinc-600">Based in Offerton, Stockport SK2 5DE — serving <?= count($areas) ?>+ towns across Greater Manchester and the wider North West.</p>
            </div>
        </div>
    </div>
</section>

<!-- LEAVE A GOOGLE REVIEW -->
<section id="leave-review" class="bg-white">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Google</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Leave us a Google review</h2>
                <p class="mt-4 text-zinc-600 text-lg leading-relaxed">
                    If we’ve completed an EICR, fire alarm service, gas certificate, CCTV install or any other job for you,
                    a Google review is the best way to share that experience.
                </p>
                <ol class="mt-6 space-y-3 text-sm text-zinc-700 list-decimal list-inside">
                    <li>Open our Google Business profile using the button</li>
                    <li>Tap <strong>Write a review</strong> and choose a star rating</li>
                    <li>A sentence or two about the service and town is plenty</li>
                </ol>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="<?= htmlspecialchars($googleReviewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"
                       class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">
                        Open Google reviews →
                    </a>
                    <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                       class="px-8 py-4 rounded-2xl bg-[#0a2540] text-white font-semibold hover:bg-[#ff6b00] transition">
                        <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>
            </div>
            <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Prefer another channel?</div>
                <h3 class="text-2xl font-semibold tracking-tight">Talk to the team</h3>
                <p class="mt-3 text-white/75 text-sm">
                    Feedback, complaints or compliments — we’re happy to hear from you directly.
                </p>
                <dl class="mt-6 space-y-4 text-sm">
                    <div>
                        <dt class="text-white/50 text-xs uppercase tracking-wider">Phone</dt>
                        <dd class="mt-1">
                            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="text-white hover:text-[#ff6b00] font-medium text-lg">
                                <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-white/50 text-xs uppercase tracking-wider">WhatsApp</dt>
                        <dd class="mt-1">
                            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                               class="text-white hover:text-[#ff6b00] font-medium">+<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?></a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-white/50 text-xs uppercase tracking-wider">Email</dt>
                        <dd class="mt-1">
                            <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="text-white hover:text-[#ff6b00] break-all">
                                <?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-white/50 text-xs uppercase tracking-wider">Google profile</dt>
                        <dd class="mt-1">
                            <a href="<?= htmlspecialchars($googleReviewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"
                               class="text-white hover:text-[#ff6b00] break-all text-sm">
                                <?= htmlspecialchars($googleReviewUrl, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</section>

<!-- SERVICES TEASER -->
<section class="bg-zinc-50 border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Services</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Work that earns the reviews</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Install, maintain, test and certify — open any service for local pages and a free quote.</p>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($services as $slug => $name): ?>
                <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
                   class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="bg-[#0a2540] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Ready for the same standard of service?</h2>
            <p class="mt-3 text-white/75">
                Free fixed-price quotes from Stockport-based engineers — we aim to respond within 2 hours on business days.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= url('/contact.php') ?>" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Request a quote</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Icomply%2C%20I%20need%20a%20quote"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                   class="px-6 py-3 rounded-2xl border border-white/30 font-semibold hover:bg-white/10"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </div>
        <ul class="space-y-3 text-sm text-white/90">
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> BS 5839 · BS 5266 · BS 7671 · gas safety</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Installation, servicing and certification</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> <?= count($areas) ?>+ towns across the North West</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span>
                <a href="<?= htmlspecialchars($googleReviewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="hover:text-[#ff6b00] underline-offset-2 hover:underline">
                    Leave a Google review
                </a>
            </li>
        </ul>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
