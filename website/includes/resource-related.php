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
        'gas-safety-certificate-landlords' => [
            ['href' => url('/pages/gas-safety-certificate'), 'label' => 'Gas safety certificate hub'],
            ['href' => url('/pages/services/gas-systems'), 'label' => 'Gas systems'],
        ],
        'fire-risk-assessment-guide' => [
            ['href' => url('/pages/fire-risk-assessment'), 'label' => 'Fire risk assessment hub'],
            ['href' => url('/pages/services/fire-risk-assessments'), 'label' => 'FRA service'],
        ],
        'smoke-and-co-alarms' => [
            ['href' => url('/pages/smoke-carbon-monoxide-alarms'), 'label' => 'Smoke & CO hub'],
            ['href' => url('/pages/services/smoke-co-alarms'), 'label' => 'Smoke & CO alarms'],
        ],
        'pat-testing-guide' => [
            ['href' => url('/pages/portable-appliance-testing'), 'label' => 'PAT testing hub'],
            ['href' => url('/pages/services/pat-testing'), 'label' => 'PAT testing'],
        ],
        'epc-for-landlords' => [
            ['href' => url('/pages/energy-performance-certificates'), 'label' => 'EPC hub'],
            ['href' => url('/pages/services/epc'), 'label' => 'EPC service'],
        ],
        'fire-door-inspection' => [
            ['href' => url('/pages/fire-door-compliance'), 'label' => 'Fire door hub'],
            ['href' => url('/pages/services/fire-doors'), 'label' => 'Fire doors'],
        ],
        'fire-extinguisher-servicing' => [
            ['href' => url('/pages/commercial-fire-safety'), 'label' => 'Commercial fire hub'],
            ['href' => url('/pages/services/fire-extinguishers'), 'label' => 'Fire extinguishers'],
        ],
        'kitchen-fire-suppression-guide' => [
            ['href' => url('/pages/services/kitchen-fire-suppression'), 'label' => 'Kitchen fire suppression'],
            ['href' => url('/pages/commercial-fire-safety'), 'label' => 'Commercial fire hub'],
        ],
        'let-ready-void-checklist' => [
            ['href' => url('/pages/landlord-certificates'), 'label' => 'Landlord certificates hub'],
            ['href' => url('/pages/packages/let-ready'), 'label' => 'Let Ready package'],
        ],
        'commercial-fire-safety-basics' => [
            ['href' => url('/pages/commercial-fire-safety'), 'label' => 'Commercial fire hub'],
            ['href' => url('/pages/commercial'), 'label' => 'Commercial / FM'],
        ],
        'care-home-fire-and-nurse-call' => [
            ['href' => url('/pages/care-homes'), 'label' => 'Care homes'],
            ['href' => url('/pages/services/nurse-call'), 'label' => 'Nurse call'],
        ],
        'electrical-safety-rented-homes' => [
            ['href' => url('/pages/electrical-safety-landlords'), 'label' => 'Electrical safety hub'],
            ['href' => url('/pages/resources/eicr-guide'), 'label' => 'EICR guide'],
        ],
        'booking-compliance-certificates' => [
            ['href' => url('/contact'), 'label' => 'Contact / quote'],
            ['href' => url('/pages/landlord-certificates'), 'label' => 'Landlord certificates'],
        ],
        'greater-manchester-property-compliance' => [
            ['href' => url('/pages/stockport-property-compliance'), 'label' => 'Stockport hub'],
            ['href' => url('/pages/manchester-property-compliance'), 'label' => 'Manchester hub'],
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
