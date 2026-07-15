<?php
/**
 * Contact — conversion-focused page matching homepage style.
 * Preserves CSRF, validation, leads.jsonl, gclid/fbclid POST handling.
 */
require_once __DIR__ . '/config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Contact Us | Free Quote';
$metaDesc = 'Contact Icomply Property Services for a free compliance quote. Call, WhatsApp or send a message. Based in Stockport SK2 5DE — covering Greater Manchester & the North West.';
$metaKeywords = 'contact Icomply, free quote Stockport, property compliance quote, electrician Stockport, fire alarm quote Manchester';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/contact.php');

$services = getServices();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Simple CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        $errors[] = 'Invalid form token. Please try again.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $service = trim((string)($_POST['service'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));
        $gclid = trim((string)($_POST['gclid'] ?? ''));
        $fbclid = trim((string)($_POST['fbclid'] ?? ''));

        if ($name === '' || strlen($name) > 120) {
            $errors[] = 'Please enter your name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email.';
        }
        if ($phone === '' || strlen($phone) > 40) {
            $errors[] = 'Please enter a phone number.';
        }
        if ($service === '') {
            $errors[] = 'Please select a service.';
        }
        if ($message === '' || strlen($message) > 5000) {
            $errors[] = 'Please enter a short message.';
        }

        if (!$errors) {
            $lead = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'service' => $service,
                'message' => $message,
                'gclid' => $gclid,
                'fbclid' => $fbclid,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'timestamp' => date('c'),
            ];
            $leadsDir = SITE_ROOT . '/data';
            if (!is_dir($leadsDir)) {
                mkdir($leadsDir, 0755, true);
            }
            file_put_contents($leadsDir . '/leads.jsonl', json_encode($lead, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);

            // Optional email notify — fail silently; JSONL + redirect always proceed
            $notifyTo = (defined('LEADS_NOTIFY_EMAIL') && LEADS_NOTIFY_EMAIL !== '')
                ? LEADS_NOTIFY_EMAIL
                : EMAIL;
            $subject = 'New website lead: ' . $service;
            $body = "New contact form lead\n"
                . "=====================\n"
                . "Name: {$name}\n"
                . "Email: {$email}\n"
                . "Phone: {$phone}\n"
                . "Service: {$service}\n"
                . "Message:\n{$message}\n\n"
                . "gclid: {$gclid}\n"
                . "fbclid: {$fbclid}\n"
                . "IP: " . ($lead['ip'] ?? '') . "\n"
                . "Time: " . ($lead['timestamp'] ?? '') . "\n";
            $headers = 'From: ' . EMAIL . "\r\n"
                . 'Reply-To: ' . $email . "\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n";
            @mail($notifyTo, $subject, $body, $headers);

            $_SESSION['csrf'] = bin2hex(random_bytes(16));
            // PRG: prevent resubmit on refresh; land on conversion page
            header('Location: ' . url('/thank-you.php?ref=contact'), true, 303);
            exit;
        }
    }
}

// Capture click IDs from query string for form
$gclidPrefill = htmlspecialchars($_GET['gclid'] ?? $_POST['gclid'] ?? '', ENT_QUOTES, 'UTF-8');
$fbclidPrefill = htmlspecialchars($_GET['fbclid'] ?? $_POST['fbclid'] ?? '', ENT_QUOTES, 'UTF-8');

$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);

$trust = [
    ['title' => 'Fast response', 'text' => 'We aim to reply within 2 hours on business days'],
    ['title' => 'Local engineers', 'text' => 'Based in Stockport SK2 — covering 150+ towns'],
    ['title' => 'Fixed-price quotes', 'text' => 'Clear scope, documentation and certification'],
    ['title' => 'Standards-led', 'text' => 'BS 5839, BS 5266, BS 7671, gas safety & more'],
];

$faqs = [
    [
        'q' => 'How quickly will you respond?',
        'a' => 'We aim to respond to quote requests within 2 hours on business days. For urgent call-outs, phone or WhatsApp us directly.',
    ],
    [
        'q' => 'Do you cover my area?',
        'a' => 'We serve 150+ towns across Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria. Stockport SK2 is our base — most North West postcodes are in range.',
    ],
    [
        'q' => 'What information do you need for a quote?',
        'a' => 'Service type, postcode, property type, and any panel brand or system details help us price accurately. Photos via WhatsApp are welcome.',
    ],
    [
        'q' => 'Can I buy parts or kits as well as install services?',
        'a' => 'Yes — browse our trade shop and manufacturer pages, or ask for a bulk / trade quote on the form.',
    ],
];

require SITE_ROOT . '/includes/header.php';

$faqEntities = [];
foreach ($faqs as $faq) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $faq['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $faq['a'],
        ],
    ];
}

$contactSchema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'ContactPage',
            '@id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => $pageTitle,
            'description' => $metaDesc,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => SITE_NAME,
                'url' => SITE_URL,
            ],
            'about' => ['@id' => $canonicalUrl . '#business'],
            'mainEntity' => ['@id' => $canonicalUrl . '#business'],
            'inLanguage' => 'en-GB',
        ],
        [
            '@type' => 'LocalBusiness',
            '@id' => $canonicalUrl . '#business',
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
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'telephone' => PHONE,
                    'contactType' => 'customer service',
                    'email' => EMAIL,
                    'areaServed' => 'GB',
                    'availableLanguage' => ['English'],
                ],
                [
                    '@type' => 'ContactPoint',
                    'contactType' => 'customer support',
                    'url' => 'https://wa.me/' . WHATSAPP,
                    'areaServed' => 'GB',
                    'availableLanguage' => ['English'],
                ],
            ],
            'sameAs' => array_values(array_filter([
                defined('SOCIAL_FACEBOOK') ? SOCIAL_FACEBOOK : '',
                defined('SOCIAL_INSTAGRAM') ? SOCIAL_INSTAGRAM : '',
                defined('SOCIAL_LINKEDIN') ? SOCIAL_LINKEDIN : '',
                defined('SOCIAL_TWITTER') ? SOCIAL_TWITTER : '',
                defined('SOCIAL_GOOGLE') ? SOCIAL_GOOGLE : '',
                'https://wa.me/' . WHATSAPP,
            ])),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contact', 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => $canonicalUrl . '#faq',
            'mainEntity' => $faqEntities,
        ],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($contactSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">Contact</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    Stockport SK2 5DE · North West
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Get in touch.<br>
                    <span class="text-[#ff6b00]">Free fixed-price quote.</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    Call, WhatsApp or send a message — electrical, fire, gas, emergency lighting, CCTV and access control across the North West.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote-form" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Send a message</a>
                    <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                       class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">
                        Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block"><?= count($services) ?></span> core services</div>
                    <div><span class="text-white font-semibold text-xl block">2 hrs</span> typical reply*</div>
                    <div><span class="text-white font-semibold text-xl block">SK2</span> local base</div>
                </div>
                <p class="mt-3 text-[11px] text-white/40">*Business days, subject to capacity.</p>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-3xl p-6 md:p-8 backdrop-blur-sm">
                <h2 class="text-xl font-semibold mb-5">Direct contact</h2>
                <div class="space-y-5 text-sm">
                    <div class="flex gap-4 items-start">
                        <div class="w-10 h-10 rounded-2xl bg-[#ff6b00]/20 flex items-center justify-center text-[#ff6b00] font-bold shrink-0">☎</div>
                        <div>
                            <div class="text-white/50 text-xs uppercase tracking-wider">Phone</div>
                            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="text-lg font-semibold text-white hover:text-[#ff6b00] transition">
                                <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <div class="w-10 h-10 rounded-2xl bg-green-500/20 flex items-center justify-center text-green-400 font-bold shrink-0">Wa</div>
                        <div>
                            <div class="text-white/50 text-xs uppercase tracking-wider">WhatsApp</div>
                            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                               class="text-lg font-semibold text-white hover:text-green-400 transition">Message us instantly</a>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-white font-bold shrink-0">@</div>
                        <div>
                            <div class="text-white/50 text-xs uppercase tracking-wider">Email</div>
                            <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="text-lg font-semibold text-white hover:text-[#ff6b00] transition">
                                <?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-white font-bold shrink-0">📍</div>
                        <div>
                            <div class="text-white/50 text-xs uppercase tracking-wider">Address</div>
                            <div class="text-white font-medium leading-relaxed"><?= htmlspecialchars(ADDRESS, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-white/10 flex flex-wrap gap-3">
                    <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-white/80 hover:text-[#ff6b00]">All services →</a>
                    <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-white/80 hover:text-[#ff6b00]">Manufacturers →</a>
                    <a href="<?= url('/shop/index.php') ?>" class="text-sm font-semibold text-white/80 hover:text-[#ff6b00]">Shop →</a>
                </div>
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

<!-- FORM + SIDEBAR -->
<section id="quote-form" class="bg-zinc-50 border-b">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="grid lg:grid-cols-5 gap-10 lg:gap-12 items-start">
            <!-- Form -->
            <div class="lg:col-span-3">
                <div class="mb-8">
                    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
                    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Send us a message</h2>
                    <p class="mt-3 text-zinc-600">Tell us the service, postcode and any system details — we will come back with a clear next step.</p>
                </div>

                <?php if ($errors): ?>
                    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-800" role="alert">
                        <?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="<?= url('/contact.php') ?>" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="gclid" value="<?= $gclidPrefill ?>">
                    <input type="hidden" name="fbclid" value="<?= $fbclidPrefill ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="contact-name" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1.5">Name</label>
                            <input id="contact-name" type="text" name="name" placeholder="Full name" required maxlength="120"
                                   class="w-full border border-zinc-200 px-5 py-3.5 rounded-2xl focus:outline-none focus:border-[#ff6b00] focus:ring-1 focus:ring-[#ff6b00]"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="contact-email" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1.5">Email</label>
                            <input id="contact-email" type="email" name="email" placeholder="you@example.com" required
                                   class="w-full border border-zinc-200 px-5 py-3.5 rounded-2xl focus:outline-none focus:border-[#ff6b00] focus:ring-1 focus:ring-[#ff6b00]"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="contact-phone" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1.5">Phone</label>
                            <input id="contact-phone" type="tel" name="phone" placeholder="Mobile or landline" required maxlength="40"
                                   class="w-full border border-zinc-200 px-5 py-3.5 rounded-2xl focus:outline-none focus:border-[#ff6b00] focus:ring-1 focus:ring-[#ff6b00]"
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="contact-service" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1.5">Service</label>
                            <select id="contact-service" name="service" required
                                    class="w-full border border-zinc-200 px-5 py-3.5 rounded-2xl bg-white focus:outline-none focus:border-[#ff6b00] focus:ring-1 focus:ring-[#ff6b00]">
                                <option value="">Select service…</option>
                                <?php foreach ($services as $slug => $s): ?>
                                    <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>" <?= (($_POST['service'] ?? '') === $s) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="Shop / products" <?= (($_POST['service'] ?? '') === 'Shop / products') ? 'selected' : '' ?>>Shop / products</option>
                                <option value="Multi-service package" <?= (($_POST['service'] ?? '') === 'Multi-service package') ? 'selected' : '' ?>>Multi-service package</option>
                                <option value="Other / not sure" <?= (($_POST['service'] ?? '') === 'Other / not sure') ? 'selected' : '' ?>>Other / not sure</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="contact-message" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1.5">Message</label>
                        <textarea id="contact-message" name="message" rows="5" required maxlength="5000"
                                  placeholder="Postcode, property type, panel brand / system details…"
                                  class="w-full border border-zinc-200 px-5 py-3.5 rounded-2xl focus:outline-none focus:border-[#ff6b00] focus:ring-1 focus:ring-[#ff6b00]"><?= htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
                    <p class="text-center text-xs text-zinc-500">
                        By submitting you agree to our
                        <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                        and
                        <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
                        After send you will be taken to a confirmation page.
                    </p>
                </form>
                <p class="mt-4 text-center text-sm text-zinc-600">
                    Prefer not to wait?
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" class="text-green-600 font-semibold hover:underline" target="_blank" rel="noopener">WhatsApp</a>
                    or
                    <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="text-[#ff6b00] font-semibold hover:underline"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                </p>
            </div>

            <!-- Sidebar CTAs -->
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-[#0a2540] text-white rounded-3xl p-6 md:p-8">
                    <h3 class="text-xl font-semibold tracking-tight">Prefer to talk?</h3>
                    <p class="mt-2 text-white/75 text-sm">Same-week appointments often available. Phone or WhatsApp for the fastest route.</p>
                    <div class="mt-6 space-y-3">
                        <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                           class="flex items-center justify-between gap-3 w-full px-5 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold transition">
                            <span>Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></span>
                            <span aria-hidden="true">→</span>
                        </a>
                        <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                           class="flex items-center justify-between gap-3 w-full px-5 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold transition">
                            <span>WhatsApp chat</span>
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>

                <div class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8">
                    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Explore</div>
                    <h3 class="text-lg font-semibold text-black mt-2">Services &amp; brands</h3>
                    <p class="mt-2 text-sm text-zinc-600">Not sure what you need? Browse services or manufacturer pages first.</p>
                    <div class="mt-5 flex flex-col gap-2">
                        <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#0a2540] hover:text-[#ff6b00] transition">All services →</a>
                        <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#0a2540] hover:text-[#ff6b00] transition">Manufacturers we install →</a>
                        <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#0a2540] hover:text-[#ff6b00] transition">Areas we cover →</a>
                        <a href="<?= url('/shop/index.php') ?>" class="text-sm font-semibold text-[#0a2540] hover:text-[#ff6b00] transition">Trade shop →</a>
                    </div>
                </div>

                <div class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8">
                    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Base</div>
                    <h3 class="text-lg font-semibold text-black mt-2">Stockport SK2 5DE</h3>
                    <p class="mt-2 text-sm text-zinc-600 leading-relaxed">
                        <?= htmlspecialchars(ADDRESS, ENT_QUOTES, 'UTF-8') ?>. Local engineers serving Greater Manchester and the wider North West.
                    </p>
                    <p class="mt-4 text-xs text-zinc-500">
                        <!-- Optional map embed: replace the link below with an iframe if you add a Google Maps embed key later. -->
                        <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode(ADDRESS) ?>"
                           target="_blank" rel="noopener"
                           class="inline-flex font-semibold text-[#ff6b00] hover:underline">
                            Open in Google Maps →
                        </a>
                    </p>
                    <div class="mt-4 rounded-2xl bg-zinc-100 border border-zinc-200 p-4 text-center text-xs text-zinc-500">
                        Map embed optional — use the link above, or drop a Google Maps iframe here when ready.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ MINI -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">FAQ</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Quick answers</h2>
            <p class="mt-3 text-zinc-600">Common questions before you get in touch.</p>
        </div>
        <div class="space-y-3">
            <?php foreach ($faqs as $faq): ?>
                <details class="bg-white border border-zinc-200 rounded-2xl p-5 group">
                    <summary class="font-semibold text-black cursor-pointer list-none flex items-center justify-between gap-4">
                        <span><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="text-[#ff6b00] text-xl leading-none group-open:rotate-45 transition shrink-0">+</span>
                    </summary>
                    <p class="mt-3 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SHARE -->
<section class="max-w-3xl mx-auto px-6 pb-16">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
