<?php
/**
 * Marketing wave-1 catalogue: 14 fortnight resource guides + quality SEO hubs.
 * HMO package landings are intentionally absent (PR #3).
 * CTA target is always /contact. No keyword-matrix expansion.
 */
declare(strict_types=1);

require_once __DIR__ . '/wave1-guides.php';
require_once __DIR__ . '/wave1-hubs.php';

function wave1ContactPath(): string
{
    return '/contact';
}

/**
 * Official publish sequence from Marketing follow-up / PUBLISH-QUEUE.md
 * (file was not on disk; order is Batch A days 1–5, B days 6–14, C hubs).
 * HMO package landings and keyword-matrix URLs are excluded.
 *
 * @return array{A:array,B:array,C:array}
 */
function wave1PublishQueue(): array
{
    $guides = wave1FortnightGuides();
    $a = [];
    $b = [];
    foreach ($guides as $slug => $g) {
        $day = (int)($g['day'] ?? 0);
        if ($day >= 1 && $day <= 5) {
            $a[$slug] = $g;
        } elseif ($day >= 6 && $day <= 14) {
            $b[$slug] = $g;
        }
    }
    uasort($a, static fn($x, $y) => ((int)$x['day']) <=> ((int)$y['day']));
    uasort($b, static fn($x, $y) => ((int)$x['day']) <=> ((int)$y['day']));
    return [
        'A' => [
            'id' => 'batch-a',
            'label' => 'Batch A — Days 1–5',
            'blurb' => 'Core landlord certificates first: gas, FRA, smoke/CO, PAT and EPC.',
            'guides' => $a,
        ],
        'B' => [
            'id' => 'batch-b',
            'label' => 'Batch B — Days 6–14',
            'blurb' => 'Fire, commercial, care, process and Greater Manchester — after Batch A.',
            'guides' => $b,
        ],
        'C' => [
            'id' => 'batch-c',
            'label' => 'Batch C — SEO hubs',
            'blurb' => 'Twelve quality hubs. Not doorway spam, not HMO package landings, not keyword-matrix URLs.',
            'hubs' => wave1QualityHubs(),
        ],
    ];
}

function wave1Rich(string $text): string
{
    $out = '';
    $offset = 0;
    $len = strlen($text);
    while ($offset < $len) {
        if (preg_match('/\[([^\]]+)\]\((\/[^)]+)\)/', $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $start = (int)$m[0][1];
            $out .= htmlspecialchars(substr($text, $offset, $start - $offset), ENT_QUOTES, 'UTF-8');
            $href = url($m[2][0]);
            $out .= '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="text-[#ff6b00] hover:underline">'
                . htmlspecialchars($m[1][0], ENT_QUOTES, 'UTF-8') . '</a>';
            $offset = $start + strlen($m[0][0]);
            continue;
        }
        $out .= htmlspecialchars(substr($text, $offset), ENT_QUOTES, 'UTF-8');
        break;
    }
    $out = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $out) ?? $out;
    return $out;
}

function wave1Guide(string $slug): ?array
{
    $all = wave1FortnightGuides();
    return $all[$slug] ?? null;
}

function wave1Hub(string $slug): ?array
{
    $all = wave1QualityHubs();
    return $all[$slug] ?? null;
}

/** Cards for pre-wave-1 resource articles linked from hubs. */
function wave1ExistingResourceCard(string $slug): ?array
{
    $map = [
        'eicr-guide' => [
            'tag' => 'Electrical',
            'cardTitle' => 'EICR guide for landlords & commercial sites',
            'blurb' => 'What an Electrical Installation Condition Report covers, typical intervals and report codes.',
        ],
        'fire-alarm-servicing' => [
            'tag' => 'Fire alarms',
            'cardTitle' => 'Fire alarm servicing explained',
            'blurb' => 'User tests, periodic servicing, logbooks and when to upgrade.',
        ],
        'emergency-lighting-testing' => [
            'tag' => 'Emergency lighting',
            'cardTitle' => 'Emergency lighting testing explained',
            'blurb' => 'Monthly function checks, annual duration tests and logbooks.',
        ],
        'landlord-compliance-checklist' => [
            'tag' => 'Landlords',
            'cardTitle' => 'Landlord compliance checklist',
            'blurb' => 'A practical overview of common safety certificates for rented stock.',
        ],
    ];
    return $map[$slug] ?? null;
}

/** @return list<string> */
function wave1GuideSlugs(): array
{
    return array_keys(wave1FortnightGuides());
}

/** @return list<string> */
function wave1HubSlugs(): array
{
    return array_keys(wave1QualityHubs());
}

/**
 * Pretty paths for sitemap / site-map / nav (no .php).
 * @return list<array{path:string,priority:string,label:string}>
 */
function wave1SitemapEntries(): array
{
    $out = [];
    foreach (wave1FortnightGuides() as $slug => $g) {
        $out[] = [
            'path' => '/pages/resources/' . $slug,
            'priority' => '0.7',
            'label' => $g['cardTitle'] ?? $g['h1'],
        ];
    }
    foreach (wave1QualityHubs() as $slug => $h) {
        $out[] = [
            'path' => '/pages/' . $slug,
            'priority' => '0.78',
            'label' => $h['navLabel'] ?? $h['h1'],
        ];
    }
    return $out;
}

/** @return list<array{href:string,label:string}> */
function wave1NavFeatured(): array
{
    $items = [
        ['href' => url('/pages/resources'), 'label' => 'All resources'],
        ['href' => url('/pages/landlord-certificates'), 'label' => 'Landlord certificates'],
        ['href' => url('/pages/gas-safety-certificate'), 'label' => 'Gas safety certificate'],
        ['href' => url('/pages/fire-risk-assessment'), 'label' => 'Fire risk assessment'],
        ['href' => url('/pages/electrical-safety-landlords'), 'label' => 'Electrical safety'],
        ['href' => url('/pages/commercial-fire-safety'), 'label' => 'Commercial fire safety'],
        ['href' => url('/pages/stockport-property-compliance'), 'label' => 'Stockport'],
        ['href' => url('/pages/manchester-property-compliance'), 'label' => 'Manchester'],
    ];
    return $items;
}

function wave1BreadcrumbJsonLd(array $crumbs): array
{
    $items = [];
    $i = 1;
    foreach ($crumbs as $crumb) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $i++,
            'name' => $crumb['name'],
            'item' => $crumb['item'],
        ];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

function wave1FaqJsonLd(array $faqs): ?array
{
    if (!$faqs) {
        return null;
    }
    $ents = [];
    foreach ($faqs as $faq) {
        $ents[] = [
            '@type' => 'Question',
            'name' => $faq['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['a'],
            ],
        ];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $ents,
    ];
}

function wave1QuoteFormHtml(string $defaultService, string $heading, string $intro, string $placeholder): string
{
    $services = getServices();
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    $csrf = htmlspecialchars((string)$_SESSION['csrf'], ENT_QUOTES, 'UTF-8');
    $action = htmlspecialchars(url('/contact.php'), ENT_QUOTES, 'UTF-8');
    $privacy = htmlspecialchars(url('/privacy.php'), ENT_QUOTES, 'UTF-8');
    $terms = htmlspecialchars(url('/terms.php'), ENT_QUOTES, 'UTF-8');
    $html = '<section id="quote" class="bg-zinc-50 border-t">';
    $html .= '<div class="max-w-3xl mx-auto px-6 py-16">';
    $html .= '<div class="text-center mb-10">';
    $html .= '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>';
    $html .= '<h2 class="text-3xl font-semibold tracking-tight text-black mt-2">' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h2>';
    $html .= '<p class="mt-3 text-zinc-600">' . htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '</div>';
    $html .= '<form action="' . $action . '" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">';
    $html .= '<input type="hidden" name="csrf" value="' . $csrf . '">';
    $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    $html .= '<input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">';
    $html .= '<input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">';
    $html .= '</div>';
    $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    $html .= '<input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">';
    $html .= '<select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">';
    $html .= '<option value="' . htmlspecialchars($defaultService, ENT_QUOTES, 'UTF-8') . '" selected>'
        . htmlspecialchars($defaultService, ENT_QUOTES, 'UTF-8') . '</option>';
    foreach ($services as $name) {
        $html .= '<option value="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    $html .= '<option value="Multi-service package">Multi-service package</option>';
    $html .= '</select></div>';
    $html .= '<textarea name="message" rows="4" required maxlength="5000" placeholder="'
        . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8')
        . '" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>';
    $html .= '<button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>';
    $html .= '<p class="text-center text-xs text-zinc-500">By submitting you agree to our ';
    $html .= '<a href="' . $privacy . '" class="underline hover:text-black">Privacy Policy</a> and ';
    $html .= '<a href="' . $terms . '" class="underline hover:text-black">Terms</a>.</p>';
    $html .= '</form></div></section>';
    return $html;
}
