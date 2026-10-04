<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/job-article.php';
renderJobArticle([
    'slug' => 'car-park-barrier',
    'title' => 'Car park barrier installation and repair | Stockport & North West',
    'meta' => 'Rising-arm car park barriers for Manchester, Burnley and the North West. Lane survey, safety devices and a written quote. Price on application from Stockport.',
    'keywords' => 'car park barrier, rising arm barrier, barrier installation, barrier repair, Stockport, Manchester, Burnley, North West',
    'h1' => 'Car park barrier installation and repair',
    'accent' => 'surveyed for the lane you have',
    'kicker' => 'Barriers · Stockport base',
    'lede' => 'A car park barrier is the rising arm, the cabinet, the loops and the safety devices that stop the boom for a vehicle or a person.',
    'paragraphs' => [
        'iComply surveys that lane from our Stockport base. We cover Greater Manchester, including Manchester and Burnley, and the wider North West. This is the job page for the barrier itself. It is not a separate page for every town.',
        'The survey looks at boom length, the island, induction loops, photocells, the safety edge and how the lane opens: fob, keypad, intercom or a number-plate camera. CAME Gard cabinets are the operators we fit when the lane needs them. A pedestrian door beside the lane is a maglock or door-entry job, quoted on its own.',
        'There is no catalogue price and no published call-out fee. The written quote follows the survey. Vehicle-strike damage, a boom stuck up or stuck down, and a planned service are all scoped from what we find on site.',
    ],
    'points' => [
        'Lane survey before parts are ordered',
        'Boom, loops, photocells and safety edge included in the scope',
        'CAME Gard cabinets where that operator suits the lane',
        'Manchester and Burnley attended from Stockport',
        'Written quote on application — no menu price',
    ],
    'faqs' => [
        ['Do you have a separate barrier page for Manchester or Burnley?', 'No. Both towns are covered from Stockport on this job. The survey and the quote are for the lane, not for a town name.'],
        ['Is a maglock part of the barrier price?', 'No. A pedestrian door lock is a different job. We will say so on the quote if the site has both.'],
        ['How is a car park barrier priced?', 'Price on application after the lane survey. Boom length, power, loops and safety devices change the figure. Nothing is priced on this page.'],
    ],
    'links' => [
        ['/pages/jobs/came-gard-gt4', 'CAME Gard GT4'],
        ['/pages/jobs/maglock-installation', 'Maglock installation'],
        ['/pages/services/barriers', 'Barriers service'],
        ['/pages/manufacturers/came', 'CAME barriers'],
        ['/pages/services/door-entry', 'Door entry'],
        ['/pages/keywords/rising-arm-barrier', 'Rising-arm barrier guide'],
    ],
    'image' => '/assets/images/services/barriers.jpg',
    'image_alt' => 'Car park barrier lane — iComply Property Services, Stockport',
    'service_label' => 'Barriers',
    'service_href' => '/pages/services/barriers',
    'parent_label' => 'Access control',
    'parent_href' => '/pages/services/access-control',
    'form_options' => ['Car park barrier installation', 'Car park barrier repair', 'Barrier maintenance'],
]);
