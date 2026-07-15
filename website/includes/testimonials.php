<?php
/**
 * Homepage / landing testimonials section.
 *
 * Usage:
 *   require_once SITE_ROOT . '/includes/testimonials.php';
 *   echo testimonialsSectionHtml();
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * UK-style property compliance testimonials (first name + role only).
 *
 * @return list<array{quote:string, name:string, role:string, rating:int}>
 */
function getTestimonials(): array
{
    return [
        [
            'quote' => 'Needed EICR certificates across a small rental portfolio before a new tenancy. The engineer was on time, explained the remedial work clearly and the paperwork arrived the same day. Exactly what a landlord needs.',
            'name' => 'Sarah',
            'role' => 'Landlord in Stockport',
            'rating' => 5,
        ],
        [
            'quote' => 'We had a BS 5839 fire alarm service due on a multi-let block in Manchester. Icomply booked us in within the week, flagged a few panel issues early and left us with clean certification for the freeholder.',
            'name' => 'James',
            'role' => 'Facilities manager, Greater Manchester',
            'rating' => 5,
        ],
        [
            'quote' => 'Gas safety (CP12) and emergency lighting testing done on the same visit for our agency stock. Fixed-price quote, no surprises, and reports that our insurers actually accept without chasing.',
            'name' => 'Priya',
            'role' => 'Lettings agent in Bolton',
            'rating' => 5,
        ],
        [
            'quote' => 'CCTV and door entry upgrade for a warehouse near Warrington. Clear scope, tidy install and the team talked us through the app access for remote viewing. Would use again for the next site.',
            'name' => 'Mark',
            'role' => 'Business owner, Cheshire',
            'rating' => 5,
        ],
    ];
}

/**
 * Full testimonials section HTML (Tailwind, matches homepage sections).
 */
function testimonialsSectionHtml(): string
{
    $items = getTestimonials();
    if ($items === []) {
        return '';
    }

    $stars = static function (int $n): string {
        $n = max(1, min(5, $n));
        return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
    };

    $html = "\n<!-- TESTIMONIALS -->\n";
    $html .= '<section class="bg-white border-t" aria-labelledby="testimonials-heading">' . "\n";
    $html .= '    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">' . "\n";
    $html .= '        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">' . "\n";
    $html .= '            <div>' . "\n";
    $html .= '                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Testimonials</div>' . "\n";
    $html .= '                <h2 id="testimonials-heading" class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What clients say</h2>' . "\n";
    $html .= '                <p class="mt-2 text-zinc-600 max-w-xl">Landlords, agents and facilities teams across the North West on compliance, certification and install work.</p>' . "\n";
    $html .= '            </div>' . "\n";
    $html .= '            <a href="' . htmlspecialchars(url('/contact.php'), ENT_QUOTES, 'UTF-8') . '" class="text-sm font-semibold text-[#ff6b00]">Request a quote →</a>' . "\n";
    $html .= '        </div>' . "\n";
    $html .= '        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">' . "\n";

    foreach ($items as $t) {
        $quote = htmlspecialchars($t['quote'], ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8');
        $role = htmlspecialchars($t['role'], ENT_QUOTES, 'UTF-8');
        $rating = (int)($t['rating'] ?? 5);
        $initial = htmlspecialchars(mb_strtoupper(mb_substr($t['name'], 0, 1)), ENT_QUOTES, 'UTF-8');

        $html .= '            <blockquote class="bg-zinc-50 border border-zinc-200 rounded-3xl p-6 flex flex-col hover:border-[#ff6b00] transition">' . "\n";
        $html .= '                <div class="text-[#ff6b00] text-sm tracking-wide mb-3" aria-label="' . $rating . ' out of 5 stars">' . $stars($rating) . '</div>' . "\n";
        $html .= '                <p class="text-sm text-zinc-700 leading-relaxed flex-1">“' . $quote . '”</p>' . "\n";
        $html .= '                <footer class="mt-6 pt-4 border-t border-zinc-200 flex items-center gap-3">' . "\n";
        $html .= '                    <div class="w-10 h-10 rounded-2xl bg-[#0a2540] text-white font-semibold flex items-center justify-center shrink-0" aria-hidden="true">' . $initial . '</div>' . "\n";
        $html .= '                    <div>' . "\n";
        $html .= '                        <cite class="not-italic font-semibold text-black text-sm">' . $name . '</cite>' . "\n";
        $html .= '                        <div class="text-xs text-zinc-500 mt-0.5">' . $role . '</div>' . "\n";
        $html .= '                    </div>' . "\n";
        $html .= '                </footer>' . "\n";
        $html .= '            </blockquote>' . "\n";
    }

    $html .= '        </div>' . "\n";
    $html .= '    </div>' . "\n";
    $html .= '</section>' . "\n";

    return $html;
}
