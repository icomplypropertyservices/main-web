<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/job-article.php';
renderJobArticle([
    'slug' => 'maglock-installation',
    'title' => 'Maglock installation for communal doors | Stockport & North West',
    'meta' => 'Maglock and electric-strike installation for flats, HMOs and commercial doors across the North West. Fire-release checked. Price on application from Stockport.',
    'keywords' => 'maglock installation, magnetic lock, electric strike, communal door, fire release, Stockport, Manchester, North West',
    'h1' => 'Maglock installation',
    'accent' => 'holding force, brackets and fire release',
    'kicker' => 'Doors · Maglocks',
    'lede' => 'A maglock has to hold when the credential is refused and let go when the fire alarm requires the door to open. The survey decides which of those the door actually needs.',
    'paragraphs' => [
        'We fit and replace maglocks, monitored locks and electric strikes on communal entrances, flats, HMOs and commercial doors. The visit covers the armature, Z and L brackets, the power supply and the fire-release interface. Fail-safe and fail-secure are chosen for that door. They are not copied from the last job.',
        'This is not a car park barrier. A rising arm in the car park is a separate survey and a separate quote. If the entrance has both, both are written down. Manchester and Burnley doors are attended from Stockport with the rest of Greater Manchester and the North West.',
        'Glass doors, double doors and a closer that already struggles need a bracket and a loop that survive daily use. We do not publish a lock price. The quote follows photos or a visit: door edge, supply and the fire interface.',
    ],
    'points' => [
        'Fail-safe or fail-secure chosen for the door in front of us',
        'Armature, brackets and power supply included in the survey',
        'Fire-release tested where the door must open on alarm',
        'Double doors and glass doors bracketed for daily use',
        'Price on application — no catalogue maglock fee',
    ],
    'faqs' => [
        ['Will the door release if the fire alarm sounds?', 'Where the door strategy needs a fire interface, we test release on that door and record it. We do not leave a maglock holding against an alarm.'],
        ['Is this the same job as a car park barrier?', 'No. Barriers are the lane. A maglock is the pedestrian door. They are quoted separately when a site has both.'],
        ['How is maglock installation priced?', 'Price on application after we see the door, the supply and the bracket. Nothing on this page is a fixed fee.'],
    ],
    'links' => [
        ['/pages/services/access-control', 'Access control'],
        ['/pages/services/door-entry', 'Door entry'],
        ['/pages/jobs/car-park-barrier', 'Car park barriers'],
        ['/pages/services/fire-alarms', 'Fire alarms'],
        ['/pages/keywords/access-control-installation', 'Access control installation'],
    ],
    'image' => '/assets/images/services/access-control.jpg',
    'image_alt' => 'Communal door access hardware — iComply Property Services, Stockport',
    'service_label' => 'Access control',
    'service_href' => '/pages/services/access-control',
    'parent_label' => 'Door entry',
    'parent_href' => '/pages/services/door-entry',
    'form_options' => ['Maglock installation', 'Maglock replacement', 'Electric strike'],
]);
