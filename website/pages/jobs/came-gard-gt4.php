<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/job-article.php';
renderJobArticle([
    'slug' => 'came-gard-gt4',
    'title' => 'CAME Gard GT4 car park barrier | Stockport & North West',
    'meta' => 'CAME Gard GT4 barrier cabinets for North West car parks. Survey, install, repair and service from Stockport. Price on application. No dealer-number claim on this page.',
    'keywords' => 'CAME Gard GT4, CAME barrier, car park barrier, barrier cabinet, Stockport, Manchester, North West',
    'h1' => 'CAME Gard GT4 barrier',
    'accent' => 'cabinet, arm and safety devices for the lane',
    'kicker' => 'CAME · Barrier partner',
    'lede' => 'CAME Gard GT4 is the cabinet we specify when a car park lane needs that operator. The arm, the loops and the safety devices are part of the same survey.',
    'paragraphs' => [
        'CAME is the barrier partner on this site. A GT4 quote starts with the lane, not with a box price. We check the island, the boom length, the power supply and whether a longer arm or a different Gard cabinet fits better. A 5 metre arm is only specified when the opening needs it.',
        'Install, repair and planned maintenance sit on this page. A boom after a vehicle strike, a motor that will not raise, and a loop that no longer sees a car are all attended from Stockport across Greater Manchester and the North West, including Manchester and Burnley.',
        'This page does not state a CAME dealer certificate number. It also does not publish a cabinet price. The figure is written after the survey, and a maglock on the pedestrian door beside the lane is listed separately.',
    ],
    'points' => [
        'Lane survey before a GT4 is named on the quote',
        'Arm length matched to the opening, including 5 metre arms where needed',
        'Repair after a strike, a dead motor or a failed loop',
        'Planned maintenance scoped from the cabinet you already have',
        'Price on application — no catalogue cabinet fee',
    ],
    'faqs' => [
        ['Is every car park a GT4?', 'No. We name a GT4 when the lane suits that cabinet. If it does not, the car park barrier page is the wider job and the quote says which operator we recommend.'],
        ['Do you publish a GT4 price?', 'No. Cabinet, arm, civils and safety devices are priced after the survey.'],
        ['Are you stating a CAME accreditation number?', 'No. CAME is the barrier partner we fit and service. This page does not publish a certificate or dealer number.'],
    ],
    'links' => [
        ['/pages/keywords/car-park-barrier', 'Car park barriers'],
        ['/pages/manufacturers/came', 'CAME manufacturer page'],
        ['/pages/services/barriers', 'Barriers service'],
        ['/pages/jobs/maglock-installation', 'Maglock on the pedestrian door'],
        ['/pages/services/access-control', 'Access control'],
    ],
    'image' => '/assets/images/services/barriers.jpg',
    'image_alt' => 'CAME barrier cabinet for a car park lane — iComply Property Services',
    'service_label' => 'Barriers',
    'service_href' => '/pages/services/barriers',
    'parent_label' => 'Car park barriers',
    'parent_href' => '/pages/keywords/car-park-barrier',
    'form_options' => ['CAME Gard GT4 installation', 'CAME Gard GT4 repair', 'CAME barrier maintenance'],
]);
