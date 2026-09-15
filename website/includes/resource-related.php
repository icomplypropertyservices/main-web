<?php
/**
 * Related keyword / service targets for resource articles.
 */
function resourceRelatedLinks(string $slug): array {
    $map = [
        'eicr-guide' => [
            ['href' => url('/pages/keywords/eicr'), 'label' => 'EICR keyword guide'],
            ['href' => url('/pages/services/electrical'), 'label' => 'Electrical services'],
        ],
        'fire-alarm-servicing' => [
            ['href' => url('/pages/keywords/fire-alarm-service'), 'label' => 'Fire alarm servicing guide'],
            ['href' => url('/pages/services/fire-alarms'), 'label' => 'Fire alarm services'],
        ],
        'emergency-lighting-testing' => [
            ['href' => url('/pages/keywords/emergency-lighting-test'), 'label' => 'Emergency lighting testing guide'],
            ['href' => url('/pages/services/emergency-lighting'), 'label' => 'Emergency lighting services'],
        ],
        'cctv-for-business' => [
            ['href' => url('/pages/keywords/cctv-installation'), 'label' => 'CCTV installation guide'],
            ['href' => url('/pages/services/cctv'), 'label' => 'CCTV services'],
        ],
        'access-control-guide' => [
            ['href' => url('/pages/keywords/access-control-system'), 'label' => 'Access control system guide'],
            ['href' => url('/pages/services/access-control'), 'label' => 'Access control services'],
        ],
        'landlord-compliance-checklist' => [
            ['href' => url('/pages/services/landlord-compliance'), 'label' => 'Landlord compliance service'],
            ['href' => url('/pages/landlords'), 'label' => 'Landlord services'],
        ],
    ];
    return $map[$slug] ?? [];
}

function resourceRelatedHtml(string $slug): string {
    $links = resourceRelatedLinks($slug);
    if (!$links) {
        return '';
    }
    $html = '<aside class="mt-10 rounded-3xl border border-zinc-200 bg-zinc-50 p-6">';
    $html .= '<h2 class="text-lg font-semibold text-black mb-3">Related guides &amp; services</h2><ul class="space-y-2">';
    foreach ($links as $link) {
        $html .= '<li><a class="text-[#ff6b00] font-semibold hover:underline" href="'
            . htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') . ' →</a></li>';
    }
    $html .= '</ul></aside>';
    return $html;
}
