<?php
/**
 * Vehicle, parking and access barriers — nationwide priority service.
 * Came is the partner brand. Other barrier manufacturers are covered in full.
 * Prices stay POA. Phone is the site PHONE constant (07517806082).
 */
declare(strict_types=1);

function barrierCameHeroImage(): string
{
    $map = [];
    $file = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/bar-5m-came-gard-images.json';
    if (is_file($file)) {
        $decoded = json_decode((string)file_get_contents($file), true);
        if (is_array($decoded)) {
            $map = $decoded;
        }
    }
    $url = (string)($map['bar-5m-std'] ?? $map['bar-5m-allin'] ?? '');
    if ($url !== '') {
        return $url;
    }
    return 'https://cdn.shopify.com/s/files/1/1073/5550/4972/files/came-gard-gt4.jpg?v=1788884803';
}

function barrierCameImageMap(): array
{
    $file = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/bar-5m-came-gard-images.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    return is_array($decoded) ? $decoded : [];
}

/**
 * Full UK barrier manufacturer set. Came is first and is the partner.
 *
 * @return list<array{name:string,slug:string,partner?:bool,focus:string,family:string}>
 */
function barrierManufacturerSeeds(): array
{
    $rows = [
        ['CAME', 'came', true, 'rising-arm', 'GARD rising-arm barriers, including GARD GT4 and GARD GT8, are the lanes we specify first. Five-metre GARD kits are quoted with Videx, Paxton or GSM where the entrance needs them.'],
        ['FAAC', 'faac', false, 'rising-arm', 'FAAC 620 and B680-style rising arms, loops and safety edges on staff and public car parks.'],
        ['BFT', 'bft', false, 'rising-arm', 'BFT Michelangelo and Giotto-class arms for commercial yards and residential gates that share a lane.'],
        ['Nice', 'nice', false, 'rising-arm', 'Nice Wide and M-Bar style arms, with sign and loop kits matched to the cabinet already on site.'],
        ['Magnetic Autocontrol', 'magnetic-autocontrol', false, 'parking', 'Magnetic parking barriers and lane controllers used on higher-cycle car parks and toll-style entries.'],
        ['Automatic Systems', 'automatic-systems', false, 'parking', 'Automatic Systems parking barriers and lane equipment for managed car parks and campuses.'],
        ['Beninca', 'beninca', false, 'rising-arm', 'Beninca EVA and LADY-style arms for private bays, compounds and light commercial entrances.'],
        ['Roger Technology', 'roger-technology', false, 'rising-arm', 'Roger brushless barrier operators where duty cycle and quiet running matter.'],
        ['DEA', 'dea', false, 'rising-arm', 'DEA STOP and PASS-style barriers for yards that already use DEA gate operators.'],
        ['Ditec', 'ditec', false, 'rising-arm', 'Ditec Qik and Entrematic barriers on mixed gate-and-barrier sites.'],
        ['GiBiDi', 'gibidi', false, 'rising-arm', 'GiBiDi BA and PASS barriers, including replacement booms and control boards.'],
        ['Tau', 'tau', false, 'rising-arm', 'Tau RBLOK-style arms for residential developments and small commercial lanes.'],
        ['Cardin', 'cardin', false, 'rising-arm', 'Cardin barrier cabinets and spare booms where the existing operator is Cardin.'],
        ['Genius', 'genius', false, 'rising-arm', 'Genius barrier operators, including sites that have moved on from older Genius gate kits.'],
        ['Life', 'life-automation', false, 'rising-arm', 'Life barrier and gate operators serviced without a full cabinet swap when the board is sound.'],
        ['V2', 'v2-automation', false, 'rising-arm', 'V2 CITY and similar arms for private car parks and light industrial units.'],
        ['SEA', 'sea-automation', false, 'rising-arm', 'SEA barrier operators and replacement controls on older European cabinets.'],
        ['Fadini', 'fadini', false, 'rising-arm', 'Fadini BAYT-style barriers for sites that want a compact rising arm rather than a swing gate.'],
        ['Proteco', 'proteco', false, 'rising-arm', 'Proteco Strike and Barrier kits for compounds and small commercial parks.'],
        ['ELKA', 'elka', false, 'rising-arm', 'ELKA parking barriers specified for higher opening speeds and longer booms.'],
        ['Aprimatic', 'aprimatic', false, 'rising-arm', 'Aprimatic barrier operators on Italian-made gate estates that already use the brand.'],
        ['King Gates', 'king-gates', false, 'rising-arm', 'King Gates rising arms and coupled gate operators on the same entrance.'],
        ['Key Automation', 'key-automation', false, 'rising-arm', 'Key Automation ALT and CT-style barriers for new lanes and cabinet replacements.'],
        ['Comunello', 'comunello', false, 'rising-arm', 'Comunello LIMIT and barrier arms where the boom and spring need matching.'],
        ['Motorline', 'motorline', false, 'rising-arm', 'Motorline Professional barriers for trade and light commercial lanes.'],
        ['Centurion', 'centurion', false, 'rising-arm', 'Centurion SECTOR-style barriers, including solar and battery-backed lanes where mains is weak.'],
        ['Hörmann', 'hormann', false, 'rising-arm', 'Hörmann parking barriers alongside Hörmann gates on the same industrial entrance.'],
        ['LiftMaster', 'liftmaster', false, 'rising-arm', 'LiftMaster barrier and gate operators where the estate is already on that control platform.'],
        ['Somfy', 'somfy', false, 'rising-arm', 'Somfy barrier and gate controls, including connected opening for smaller private lanes.'],
        ['Allmatic', 'allmatic', false, 'rising-arm', 'Allmatic barrier boards and operators when a like-for-like control swap is the right repair.'],
        ['Erreka', 'erreka', false, 'rising-arm', 'Erreka barriers on sites that already use Erreka sliding or swing operators.'],
        ['Quiko', 'quiko', false, 'rising-arm', 'Quiko rising arms for residential courts and small business yards.'],
        ['RIB', 'rib-automation', false, 'rising-arm', 'RIB barrier operators and spare booms for older RIB cabinets.'],
        ['Stagnoli', 'stagnoli', false, 'rising-arm', 'Stagnoli HERCULES-style arms for private and light commercial entrances.'],
        ['Pujol', 'pujol', false, 'rising-arm', 'Pujol barrier and gate operators serviced as one entrance, not two contractors.'],
        ['MAC', 'mac-automation', false, 'rising-arm', 'MAC barrier operators where the name on the cabinet is MAC and parts are still supportable.'],
        ['APT Controls', 'apt-controls', false, 'parking', 'APT Controls parking barriers and lane logic on UK car parks that still carry the brand.'],
        ['Newgate', 'newgate', false, 'parking', 'Newgate barriers and associated lane equipment on managed parking sites.'],
        ['Frontier Pitts', 'frontier-pitts', false, 'vehicle-security', 'Frontier Pitts vehicle barriers and gates where the brief is security as well as parking control.'],
        ['Avon Barrier', 'avon-barrier', false, 'vehicle-security', 'Avon Barrier rising arms and vehicle blockers for higher-security vehicle entrances.'],
        ['ATG Access', 'atg-access', false, 'vehicle-security', 'ATG Access vehicle barriers where the lane is specified as a security entrance, not only a car park.'],
        ['Heald', 'heald', false, 'vehicle-security', 'Heald vehicle barriers and statics-related entrance equipment on secure sites.'],
        ['Bristorm', 'bristorm', false, 'vehicle-security', 'Bristorm vehicle barrier products where the duty is to stop a vehicle, not only to count a ticket.'],
        ['Safetyflex', 'safetyflex', false, 'vehicle-security', 'Safetyflex vehicle barriers for yards that need a robust arm or blocker rather than a light parking boom.'],
        ['Skidata', 'skidata', false, 'parking-systems', 'Skidata lanes, barriers and ticket or ticketless entry on staffed and automatic car parks.'],
        ['Designa', 'designa', false, 'parking-systems', 'Designa parking barriers and pay-on-foot or lane equipment.'],
        ['Hub Parking Technology', 'hub-parking-technology', false, 'parking-systems', 'Hub Parking barriers and revenue-control lanes.'],
        ['Zeag', 'zeag', false, 'parking-systems', 'Zeag parking barriers and lane controllers on multi-storey and surface car parks.'],
        ['WPS', 'wps', false, 'parking-systems', 'WPS parking barriers and associated lane hardware.'],
        ['Parkare', 'parkare', false, 'parking-systems', 'Parkare barriers and parking-control equipment on UK car parks.'],
    ];
    $out = [];
    foreach ($rows as $row) {
        $out[] = [
            'name' => $row[0],
            'slug' => $row[1],
            'partner' => $row[2],
            'family' => $row[3],
            'focus' => $row[4],
        ];
    }
    return $out;
}

/** @return list<string> */
function barrierManufacturerNames(): array
{
    $names = [];
    foreach (barrierManufacturerSeeds() as $row) {
        $names[] = $row['name'];
    }
    return $names;
}

function barrierManufacturerCatalog(): array
{
    $images = barrierCameImageMap();
    $catalog = [];
    foreach (barrierManufacturerSeeds() as $row) {
        $name = $row['name'];
        $slug = $row['slug'];
        $partner = !empty($row['partner']);
        $phone = defined('PHONE') ? PHONE : '07517806082';
        if ($partner) {
            $blurb = 'CAME is our barrier partner. Icomply supplies, installs and maintains CAME GARD rising-arm barriers across the UK from Stockport, including GARD GT4 and GARD GT8 and five-metre lanes with Videx, Paxton or GSM. Safety edges, loops, signs and access release are surveyed before a written quote. Call ' . $phone . '. Prices are on application.';
            $products = [
                [
                    'id' => 'came-gard-gt4',
                    'title' => 'CAME GARD GT4',
                    'blurb' => 'Five-metre class GARD GT4 rising arm for staff, residential and commercial lanes. Boom length, loops and safety devices are confirmed on survey. Price on application.',
                    'price' => 'POA',
                    'handle' => 'bar-5m-std',
                    'shopify_product_id' => '',
                    'image' => (string)($images['bar-5m-std'] ?? barrierCameHeroImage()),
                    'badge' => 'Partner',
                ],
                [
                    'id' => 'came-gard-4',
                    'title' => 'CAME GARD 4',
                    'blurb' => 'CAME GARD 4 rising-arm barrier for standard vehicle lanes. Quoted with the access method the site already uses. Price on application.',
                    'price' => 'POA',
                    'handle' => 'bar-5m-videx',
                    'shopify_product_id' => '',
                    'image' => (string)($images['bar-5m-videx'] ?? barrierCameHeroImage()),
                    'badge' => 'Partner',
                ],
                [
                    'id' => 'came-gard-gt8',
                    'title' => 'CAME GARD GT8',
                    'blurb' => 'CAME GARD GT8 for wider or heavier-duty lanes. Spring, boom and safety geometry are checked before supply. Price on application.',
                    'price' => 'POA',
                    'handle' => 'bar-5m-gsm',
                    'shopify_product_id' => '',
                    'image' => (string)($images['bar-5m-gsm'] ?? barrierCameHeroImage()),
                    'badge' => 'Partner',
                ],
                [
                    'id' => 'came-gard-paxton',
                    'title' => 'CAME GARD with Paxton',
                    'blurb' => 'CAME barrier lane released from Paxton access control. Reader position, fire release and anti-passback are part of the survey. Price on application.',
                    'price' => 'POA',
                    'handle' => 'bar-5m-paxton',
                    'shopify_product_id' => '',
                    'image' => (string)($images['bar-5m-paxton'] ?? barrierCameHeroImage()),
                    'badge' => 'Access',
                ],
                [
                    'id' => 'came-gard-allin',
                    'title' => 'CAME GARD all-in lane',
                    'blurb' => 'Barrier, safety devices and the access method in one surveyed lane. Price on application after width, power and ground conditions are known.',
                    'price' => 'POA',
                    'handle' => 'bar-5m-allin',
                    'shopify_product_id' => '',
                    'image' => (string)($images['bar-5m-allin'] ?? barrierCameHeroImage()),
                    'badge' => 'Partner',
                ],
            ];
        } else {
            $blurb = 'Icomply installs and services ' . $name . ' vehicle and parking barriers across the UK from Stockport. ' . $row['focus'] . ' Lane width, power, loops, safety edges and access or fire release are confirmed before a written quote. Price on application. Call ' . $phone . '.';
            $products = [
                [
                    'id' => $slug . '-barrier-survey',
                    'title' => $name . ' barrier — supply, install or service',
                    'blurb' => 'Quoted after survey. We do not publish a catalogue price for ' . $name . ' barriers. ' . $row['focus'],
                    'price' => 'POA',
                    'handle' => $slug . '-barrier',
                    'shopify_product_id' => '',
                    'image' => '/assets/images/services/access-control.jpg',
                    'badge' => 'POA',
                ],
            ];
        }
        $catalog[$slug] = [
            'name' => $name,
            'slug' => $slug,
            'services' => ['barriers'],
            'blurb' => $blurb,
            'seo_title' => $name . ($partner ? ' partner barriers' : ' barriers') . ' | UK-wide',
            'seo_desc' => $partner
                ? 'CAME partner for GARD rising-arm and parking barriers across the UK. Install, service and POA quotes. Phone ' . $phone . '.'
                : $name . ' vehicle and parking barriers installed and serviced UK-wide. POA after survey. Phone ' . $phone . '.',
            'seo_keywords' => $name . ' barrier, ' . $name . ' rising arm, ' . $name . ' parking barrier, ' . $name . ' barrier installation, ' . $name . ' Manchester, ' . $name . ' Burnley, vehicle barrier UK',
            'products' => $products,
            'featured' => $partner,
            'partner' => $partner,
            'family' => $row['family'],
        ];
    }
    return $catalog;
}

function barriersServiceCopy(string $slug): ?array
{
    if ($slug !== 'barriers') {
        return null;
    }
    $count = count(barrierManufacturerSeeds());
    return [
        'hero_accent' => 'UK-wide. Came partner. Quoted after survey.',
        'pillars' => [
            ['title' => 'Came partner lanes', 'text' => 'CAME GARD GT4, GARD 4 and GARD GT8 rising arms, including five-metre lanes with Videx, Paxton or GSM. That is the range we lead with.'],
            ['title' => $count . ' barrier manufacturers', 'text' => 'FAAC, BFT, Nice, Magnetic, Automatic Systems, ELKA, Frontier Pitts, Skidata and the rest of the parking and vehicle-barrier list — installed, repaired or replaced without pretending every cabinet is Came.'],
            ['title' => 'Access, not just a boom', 'text' => 'Loops, safety edges, signs, reader posts and fire or access release are part of the lane. Door access stays on the access-control service when the building doors are the job.'],
        ],
        'intro' => [
            'Icomply designs, supplies, installs and maintains vehicle barriers, rising-arm barriers and parking barriers across the United Kingdom. The yard is in Offerton, Stockport SK2. Travel outside the North West is included in the written quote — coverage is not limited to Manchester or Burnley.',
            'CAME is our barrier partner. Where a new lane is the right answer we specify CAME GARD first, then survey boom length, spring balance, induction loops, safety edges and how the arm should open for staff, residents, visitors or a fire-alarm release. Existing FAAC, BFT, Nice, Magnetic and other cabinets are serviced under their own name.',
            'Manchester and Burnley each have a full local page because those are busy vehicle-entrance markets for us. They are not the edge of the map. A depot in Scotland or a car park in the South is the same engineering conversation, with mobilisation priced honestly.',
        ],
        'sections' => [
            [
                'h2' => 'What sits in a barrier lane',
                'p' => [
                    'A rising arm is the visible part. The lane also needs a cabinet with a safe manual release, a boom that matches the road width, a closing safety device, and usually an induction loop or photocell so the arm does not drop on a vehicle. Signs, skirts and LED strips are specified when the car park or the insurer asks for them.',
                    'Access is a separate decision: fob, ANPR-ready contact, keypad, GSM, reception button or a timed free-flow period. We connect that to the barrier controller. Building doors, maglocks and Paxton door networks stay described on the access-control pages and are linked from here when the same site needs both.',
                ],
            ],
            [
                'h2' => 'Where we will not invent a price',
                'p' => [
                    'Lane width, ground conditions, power, the manufacturer already on the island, and whether the arm must fail open or fail locked all change the figure. This page does not publish a starting price. Ask for a surveyed quote. Phone 07517806082.',
                ],
            ],
        ],
        'cta_line' => 'Lane width, town, and whether the cabinet is Came or another brand.',
        'quote_placeholder' => 'Town, lane width, Came / other brand, fob or ANPR, photo of the cabinet if you have one…',
    ];
}

function barriersKeywords(): array
{
    $phone = defined('PHONE') ? PHONE : '07517806082';
    return [
        'vehicle-barriers' => [
            'name' => 'Vehicle Barriers',
            'service' => 'barriers',
            'related' => 'rising-arm-barrier',
            'intro' => 'Vehicle barriers control cars and vans at car parks, yards, residential courts and secure entrances. Icomply supplies and installs them UK-wide, with CAME as the partner brand and a full list of other manufacturers when the cabinet on site is not Came.',
            'body' => 'A vehicle barrier survey covers road width, traffic direction, power, drainage, induction loops, safety edges and who is allowed to open the lane. We fit new CAME GARD rising arms where that is the right specification, and we repair FAAC, BFT, Nice, Magnetic, Automatic Systems, ELKA and security barriers from Frontier Pitts, Avon Barrier and ATG Access when replacement is not required. Manchester and Burnley have their own pages. The rest of the UK is the same service, with travel written into the quote. Call ' . $phone . '. Price on application.',
            'meta_desc' => 'Vehicle barriers installed and serviced UK-wide. Came partner, rising arms and parking lanes. POA. Phone ' . $phone . '.',
            'seo_keywords' => 'vehicle barriers, vehicle barrier installation, vehicle access barrier, rising arm barrier, parking barrier, Came barrier, barrier installation UK, vehicle barriers Manchester, vehicle barriers Burnley',
            'focus_points' => [
                'CAME partner specification for new rising-arm lanes',
                'Repair and like-for-like parts for the manufacturer already installed',
                'Loops, safety edges, signs and manual release included in the survey',
                'UK-wide attendance from Stockport, with Manchester and Burnley pages',
            ],
            'faq' => [
                ['Do you only cover Manchester and Burnley?', 'No. Those two cities have detailed local pages. Vehicle barrier work is quoted anywhere in the UK. Travel is part of the quote.'],
                ['Are you a Came barrier partner?', 'Yes. CAME GARD is the rising-arm range we specify first, including GT4 and GT8. Other brands are still serviced under their own name.'],
                ['What does a vehicle barrier cost?', 'Price on application after lane width, power and safety devices are known. We do not publish a made-up starting fee.'],
            ],
        ],
        'rising-arm-barrier' => [
            'name' => 'Rising Arm Barrier',
            'service' => 'barriers',
            'related' => 'parking-barrier',
            'intro' => 'A rising arm barrier is the boom that lifts to let an authorised vehicle through and lowers behind it. Icomply installs and maintains rising arm barriers across the UK, leading with CAME GARD and covering the other cabinets we find on site.',
            'body' => 'Boom length has to match the lane. A five-metre CAME GARD is a common staff-car-park arm. Wider entrances may need a GARD GT8, a skirt, or a different manufacturer’s long boom. We set spring balance, closing speed, and a safety device so the arm does not strike a vehicle or a person. Access can be a fob, a keypad, GSM, a reception push or an ANPR contact. Fire-alarm release is agreed with the building’s fire strategy rather than guessed. Phone ' . $phone . '.',
            'meta_desc' => 'Rising arm barriers UK-wide. Came GARD partner install and service, plus other brands. POA after survey. ' . $phone . '.',
            'seo_keywords' => 'rising arm barrier, rising arm barrier installation, boom barrier, Came GARD, automatic rising arm, barrier boom replacement, rising arm Manchester, rising arm Burnley',
            'focus_points' => [
                'CAME GARD GT4, GARD 4 and GARD GT8 rising arms',
                'Boom, spring and safety-edge replacement on other brands',
                'Loop and photocell geometry so the arm closes only when the lane is clear',
                'Manual release and signage explained to the people who use the lane',
            ],
            'faq' => [
                ['What length of rising arm do I need?', 'Usually the clear lane width plus a small overlap. We measure. A five-metre GARD is common and is not a guess we will force onto a narrower road.'],
                ['Can you replace only the boom?', 'Often yes, if the cabinet and spring are sound. If the operator is obsolete we say so and quote a Came or like-for-like replacement.'],
                ['Will the arm drop if the power fails?', 'That is a design choice. We set fail-open or secured behaviour to match the site, and we do not hide the decision in a default dip-switch.'],
            ],
        ],
        'parking-barrier' => [
            'name' => 'Parking Barrier',
            'service' => 'barriers',
            'related' => 'access-barrier',
            'intro' => 'Parking barriers keep bays for the people who are allowed to use them: staff, residents, customers and visitors with a ticket or a validation. Icomply installs and services parking barriers UK-wide, with CAME as the partner brand.',
            'body' => 'Surface car parks, multi-storeys, retail parks and office decks all punish a light domestic arm. We look at vehicles per hour, island width, pay-on-foot or ticketless rules, and whether Magnetic, Automatic Systems, Skidata, Designa, Zeag, Hub Parking, WPS or Parkare equipment is already in the lane. New lanes are specified as CAME GARD unless the existing parking system must stay on its own barrier. Safety and signage are included. Manchester multi-storeys and Burnley industrial parks are both in the diary. So is the rest of the UK. Phone ' . $phone . '. Price on application.',
            'meta_desc' => 'Parking barriers for car parks and decks, UK-wide. Came partner and other parking brands. POA. ' . $phone . '.',
            'seo_keywords' => 'parking barrier, car park barrier, parking barrier installation, automatic parking barrier, Came parking barrier, parking barrier service, parking barrier Manchester, parking barrier Burnley',
            'focus_points' => [
                'Staff, resident, retail and visitor parking lanes',
                'CAME GARD where a new barrier is the right specification',
                'Service for Magnetic, Automatic Systems, Skidata, Designa, Zeag and similar lane brands',
                'No published price — duty cycle and civils change the quote',
            ],
            'faq' => [
                ['Can one barrier share fobs with the building doors?', 'Often yes. That integration is scoped with access control. See car park barrier access if the credential is the main problem and the arm itself is sound.'],
                ['Do you take over a parking company’s barrier?', 'We service the barrier and the safety devices. Revenue-control contracts and ticket-machine merchant accounts stay with the operator unless you ask us to coordinate.'],
                ['How fast can you attend a stuck-down arm?', 'Phone ' . $phone . ' with the postcode and a photo of the cabinet. We confirm a window. There is no fake 24-hour promise on this page.'],
            ],
        ],
        'access-barrier' => [
            'name' => 'Access Barrier',
            'service' => 'barriers',
            'related' => 'automatic-barrier',
            'intro' => 'An access barrier is the vehicle gatekeeper: it opens for the right credential and stays down for everyone else. Icomply builds that lane UK-wide and links it to door access when the same people also enter the building.',
            'body' => 'Readers, keypads, GSM modules and ANPR contacts sit on the island or the approach, not as an afterthought screwed to a wet cabinet. We use Paxton and Videx where the building already does, on a CAME GARD arm when we are supplying the barrier. Fire-brigade switches, airlocks and anti-passback are written down. If you only need door fobs, start at access control and we will send you back here when the vehicle lane is part of the same job. Phone ' . $phone . '.',
            'meta_desc' => 'Access barriers for vehicle lanes, UK-wide. Came arms with Paxton or Videx. Linked from access control. POA. ' . $phone . '.',
            'seo_keywords' => 'access barrier, vehicle access barrier, barrier access control, Came access barrier, fob barrier, ANPR barrier, access barrier Manchester, access barrier Burnley',
            'focus_points' => [
                'Credential, keypad, GSM or ANPR-ready release of the arm',
                'CAME barrier with Paxton or Videx when those systems are already on site',
                'Deep link back to door access control for the pedestrian entrance',
                'Audit of who can open the lane, including leavers and contractors',
            ],
            'faq' => [
                ['Is an access barrier the same as access control?', 'No. Access control is readers, doors and credentials. An access barrier is the vehicle arm. They should share a user list when the site wants one rule for cars and doors.'],
                ['Can ANPR open the barrier?', 'Yes when the camera, the allow-list and the barrier input are designed together. We do not promise a camera brand will open an unknown arm until the controller terminals are seen.'],
                ['What about pedestrians?', 'A rising arm is not a pedestrian gate. We will say if you also need a turnstile, a side gate or a door. Those are specified separately.'],
            ],
        ],
        'automatic-barrier' => [
            'name' => 'Automatic Barrier',
            'service' => 'barriers',
            'related' => 'barrier-installation',
            'intro' => 'An automatic barrier lifts without someone walking out with a key. Icomply installs and maintains automatic barriers for car parks, yards and private roads across the UK.',
            'body' => 'Automatic means a motor, a control input and a safety circuit — not a remote left on a dashboard forever. We commission CAME GARD automatic arms as the partner range, and we repair automatic barriers from FAAC, BFT, Nice, Beninca, Roger Technology, DEA, Ditec, ELKA and the parking-system brands when that name is on the door of the cabinet. Closing protection and a tested manual release are part of handover. Phone ' . $phone . '. Price on application.',
            'meta_desc' => 'Automatic barriers installed and repaired UK-wide. Came GARD partner range. POA after survey. ' . $phone . '.',
            'seo_keywords' => 'automatic barrier, automatic car park barrier, automatic rising arm, electric barrier, Came automatic barrier, automatic barrier installation UK',
            'focus_points' => [
                'Motor, control input and tested safety circuit',
                'CAME GARD automatic arms for new lanes',
                'Repair of automatic barriers already in service',
                'Handover that includes manual release, not only a remote',
            ],
            'faq' => [
                ['Remote, fob or both?', 'Both are possible. Shared remotes get copied. Named fobs can be cancelled. We recommend the option that matches how often people join and leave.'],
                ['Do automatic barriers work in frost?', 'They do when the boom is balanced and the safety devices are not packed with ice. We say so at survey if the site is exposed.'],
                ['Can you automate a manual swing gate instead?', 'Sometimes a barrier is the wrong tool. If a swing or sliding gate is the better entrance we will say that rather than sell an arm that does not fit.'],
            ],
        ],
        'barrier-installation' => [
            'name' => 'Barrier Installation',
            'service' => 'barriers',
            'related' => 'barrier-maintenance',
            'intro' => 'Barrier installation is the surveyed fit of a new rising arm or the replacement of a dead one: cabinet, boom, power, loops, safety and the way the lane opens. Icomply installs barriers UK-wide with CAME as the partner brand.',
            'body' => 'We mark the island, confirm the boom will not hit a wall or a sign, bring power to the cabinet, cut loops if the lane needs them, and commission the access input. Civils that need a groundworker are identified before the quote, not after the arm is on site. CAME GARD is the default new installation. If you must match an estate of FAAC or Magnetic barriers we install that brand instead of forcing a mixed lane. Manchester and Burnley installations are spelled out on their own pages. Phone ' . $phone . '.',
            'meta_desc' => 'Barrier installation UK-wide. Came GARD partner installs and other brands when the estate must match. POA. ' . $phone . '.',
            'seo_keywords' => 'barrier installation, car park barrier installation, rising arm installation, Came barrier install, barrier installer UK, barrier installation Manchester, barrier installation Burnley',
            'focus_points' => [
                'Survey before any cabinet is ordered',
                'CAME partner installation, including five-metre GARD lanes',
                'Loops, power, safety edge and access input in the same job',
                'Civils called out in the quote when the island is not ready',
            ],
            'faq' => [
                ['How long does installation take?', 'A prepared island with power nearby is often a day. New ducting, loops in reinforced concrete or a wider GT8 lane takes longer. We say which one after survey.'],
                ['Do you install the loop?', 'Yes when the lane needs one. If the deck cannot be cut we design a photocell or another safe detection method and explain the compromise.'],
                ['Is installation priced online?', 'No. Phone ' . $phone . ' or send the lane width and a photo. The quote is written after that.'],
            ],
        ],
        'barrier-maintenance' => [
            'name' => 'Barrier Maintenance',
            'service' => 'barriers',
            'related' => 'vehicle-barriers',
            'intro' => 'Barrier maintenance is the planned visit that stops a car park arm failing on a Monday morning: springs, booms, loops, safety edges, boards and the people who still have a working fob. Icomply maintains barriers UK-wide.',
            'body' => 'We service CAME GARD as the partner range and we open other cabinets — FAAC, BFT, Nice, Magnetic, Automatic Systems, Beninca, ELKA, Skidata and the security barriers — without swapping the brand for the sake of it. A maintenance visit records what moved, what failed, and what should be replaced next time. Reactive call-outs sit on top of that plan when an arm is stuck. There is no invented annual price on this page. Phone ' . $phone . '.',
            'meta_desc' => 'Barrier maintenance and reactive repair UK-wide. Came and other manufacturers. POA. Phone ' . $phone . '.',
            'seo_keywords' => 'barrier maintenance, barrier repair, rising arm service, parking barrier maintenance, Came barrier service, barrier engineer UK, barrier repair Manchester, barrier repair Burnley',
            'focus_points' => [
                'Springs, booms, loops, edges and control boards',
                'CAME partner servicing plus other manufacturers',
                'A written note of what was found, not a tick-box sticker only',
                'Reactive visits when an arm is stuck up or stuck down',
            ],
            'faq' => [
                ['Will you maintain a barrier you did not install?', 'Yes, after we identify the manufacturer and confirm parts are still supportable. If they are not, we quote a Came or like-for-like replacement.'],
                ['How often should a busy arm be serviced?', 'Car parks with steady traffic usually want a planned visit at least once a year, and sooner if the arm is hit or the spring is tired. We set the interval from the duty, not from a national template.'],
                ['The arm is down and traffic is queuing. What now?', 'Phone ' . $phone . '. Use the manual release only if your own staff have been shown how, and only if it is safe to do so.'],
            ],
        ],
        'car-park-barrier' => [
            'name' => 'Car Park Barrier',
            'service' => 'barriers',
            'related' => 'car-park-barrier-access',
            'intro' => 'A car park barrier is the arm at the entrance or exit of a private or public car park. Icomply installs and maintains car park barriers across the UK, with CAME GARD as the partner product and other parking brands supported in full.',
            'body' => 'Entry lanes, exit lanes and nested staff compounds are different jobs. We survey both directions, the queue length, and whether visitors pay, validate or are on a list. CAME is the arm we fit on new car parks. Magnetic, Automatic Systems, APT Controls, Newgate, Skidata, Designa, Hub Parking, Zeag, WPS and Parkare stay in place when the revenue system depends on them. Credential rules are described on the car park barrier access guide so the door-fob question has a proper page. Phone ' . $phone . '. Manchester and Burnley pages carry the local detail.',
            'meta_desc' => 'Car park barriers UK-wide. Came partner rising arms and full parking-brand coverage. POA. ' . $phone . '.',
            'seo_keywords' => 'car park barrier, car park barrier installation, car park rising arm, Came car park barrier, car park barrier repair, car park barrier Manchester, car park barrier Burnley',
            'focus_points' => [
                'Entry, exit and staff-only car park lanes',
                'CAME GARD for new car park barriers',
                'Service for the parking-system brands already in the lane',
                'Linked guide for fobs, visitors and ANPR-style access',
            ],
            'faq' => [
                ['Can residents and staff use different rules on one arm?', 'Yes. Time zones and user groups are an access-control setting. The barrier has to accept that input. We check both.'],
                ['What if delivery vans need a taller clearance?', 'Tell us. Skirt kits and boom height are chosen so a van is not clipped. We will not fit a skirt that fouls the vehicle you actually receive.'],
                ['Do you cover hospital, retail and office car parks?', 'Yes, UK-wide, including Manchester city car parks and Burnley industrial and town-centre parks. The quote follows the lane, not a package price.'],
            ],
        ],
    ];
}

function barrierNationwideIntro(string $serviceName, string $area): string
{
    $phone = defined('PHONE') ? PHONE : '07517806082';
    $local = '';
    if (strcasecmp($area, 'Manchester') === 0) {
        $local = ' Manchester has its own barrier page because city-centre decks, airport hotels, campus car parks and south-Manchester residential courts all need a surveyed lane, not a generic North West paragraph.';
    } elseif (strcasecmp($area, 'Burnley') === 0) {
        $local = ' Burnley has its own barrier page because town-centre retail, industrial estates and hospital or college car parks need arms that survive shift changes and HGV approaches.';
    }
    return 'Icomply provides ' . $serviceName . ' in ' . $area . ' as part of a UK-wide barrier service based in Stockport SK2.' . $local
        . ' CAME is the partner brand for new rising arms. Other manufacturers are serviced under their own name. Travel to ' . $area . ' is included in the written quote rather than described as a short hop from the yard. Price on application. Phone ' . $phone . '.';
}

function barrierLocationEssay(string $area, string $keywordName): string
{
    $phone = defined('PHONE') ? PHONE : '07517806082';
    if (strcasecmp($area, 'Manchester') === 0) {
        return 'Manchester vehicle entrances are rarely a quiet cul-de-sac. City-centre multi-storeys, hotels serving the airport corridor, university and hospital car parks, Ancoats and Northern Quarter yards, Trafford and out-of-town retail, and gated residential courts in Didsbury, Chorlton and the Quays all turn up as ' . $keywordName . ' jobs. Queue length matters: an arm that is fine at 7am in a staff yard will block a retail exit on a Saturday if the closing safety device is slow or missing. We measure the lane, look at how coaches or vans approach, and specify a CAME GARD rising arm when a new barrier is the right tool — GT4 for the common five-metre staff and residential lane, GT8 when the opening is wider. Where Magnetic, Skidata, Designa or another parking brand already runs the revenue lane, we service that cabinet instead of ripping it out to put our partner brand on the island. Paxton and Videx stay the access layer when the building already uses them. Manchester is a priority attendance area. It is not the limit of the service. The same engineers quote Leeds, London or anywhere else in the UK, with mobilisation written down. Phone ' . $phone . ' with the postcode and a photo of the cabinet. There is no catalogue price.';
    }
    if (strcasecmp($area, 'Burnley') === 0) {
        return 'Burnley lanes are a different shape to a Manchester multi-storey. Industrial estates, mill conversions, town-centre retail yards, Burnley College approaches and hospital car parks need rising arms that cope with shift changes, delivery vans and, on some approaches, HGVs that should never have been sent at a short boom. ' . $keywordName . ' work here starts with the width and the camber, then the power supply, which on older units is often further from the island than the drawing suggests. CAME GARD is the partner barrier we fit when the lane is new or the old operator is finished. Five-metre GT4 arms cover most staff and retail entrances. We will not pretend a light boom is a security blocker: if the brief is to stop a vehicle rather than manage parking, Frontier Pitts, Avon Barrier, ATG Access, Heald, Bristorm or Safetyflex are named honestly. FAAC, BFT, Nice and the other maintenance brands are opened under their own name when the cabinet is still supportable. Burnley is on the priority list with Manchester. Coverage remains UK-wide from Stockport, and the quote includes the journey. Phone ' . $phone . '. Price on application after survey.';
    }
    return '';
}

function barriersDeepLinksHtml(string $context = 'hub'): string
{
    $phone = defined('PHONE') ? PHONE : '07517806082';
    $tel = preg_replace('/\s+/', '', $phone);
    $links = [
        ['/pages/services/barriers', 'Barriers hub'],
        ['/pages/manufacturers/came', 'Came partner'],
        ['/pages/keywords/vehicle-barriers', 'Vehicle barriers'],
        ['/pages/keywords/rising-arm-barrier', 'Rising arm barrier'],
        ['/pages/keywords/parking-barrier', 'Parking barrier'],
        ['/pages/keywords/access-barrier', 'Access barrier'],
        ['/pages/keywords/automatic-barrier', 'Automatic barrier'],
        ['/pages/keywords/barrier-installation', 'Barrier installation'],
        ['/pages/keywords/barrier-maintenance', 'Barrier maintenance'],
        ['/pages/keywords/car-park-barrier', 'Car park barrier'],
        ['/pages/keywords/car-park-barrier-access', 'Car park barrier access'],
        ['/pages/keywords/vehicle-barriers/manchester', 'Barriers in Manchester'],
        ['/pages/keywords/vehicle-barriers/burnley', 'Barriers in Burnley'],
        ['/pages/services/access-control', 'Access control'],
        ['/pages/resources/access-control-guide', 'Access control guide'],
        ['/pages/services/aov-air-handling', 'AOV and smoke control'],
    ];
    $heading = $context === 'access-control'
        ? 'Vehicle and parking barriers'
        : 'Barrier pages, Came, and local lanes';
    $lede = $context === 'access-control'
        ? 'Door readers stay on this access-control service. The vehicle arm is a barrier. These pages are the barrier scope, led by our Came partner range and covered for the other manufacturers.'
        : 'Nationwide barrier service. Manchester and Burnley are featured local pages. Came is the partner brand. AOV is the other priority life-safety service and stays linked here.';
    $html = '<section class="max-w-7xl mx-auto px-6 py-12"><div class="rounded-3xl border-2 border-[#ff6b00] bg-[#0B1F3A] text-white p-8 md:p-10">';
    $html .= '<p class="text-xs uppercase tracking-[3px] text-[#ffb27a] font-semibold">Priority · Barriers</p>';
    $html .= '<h2 class="text-2xl md:text-3xl font-semibold tracking-tight mt-2">' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h2>';
    $html .= '<p class="mt-3 text-white/80 max-w-3xl">' . htmlspecialchars($lede, ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '<div class="mt-6 flex flex-wrap gap-2">';
    foreach ($links as [$path, $label]) {
        $href = htmlspecialchars(url($path), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="px-4 py-2 rounded-full bg-white/10 border border-white/20 text-sm font-semibold hover:bg-[#ff6b00] hover:border-[#ff6b00]">'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
    }
    $html .= '</div>';
    $html .= '<p class="mt-6 text-sm text-white/80">Phone <a class="font-semibold text-white underline" href="tel:' . htmlspecialchars($tel, ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</a>. Written quote after survey. No catalogue barrier price.</p>';
    $html .= '</div></section>';
    return $html;
}

function barriersPartnerPanelHtml(): string
{
    $images = barrierCameImageMap();
    $cards = [
        ['CAME GARD GT4', (string)($images['bar-5m-std'] ?? barrierCameHeroImage()), 'Five-metre class partner arm. POA after the lane is measured.'],
        ['CAME GARD 4', (string)($images['bar-5m-videx'] ?? barrierCameHeroImage()), 'Standard GARD lane, including Videx where the entrance already uses it.'],
        ['CAME GARD GT8', (string)($images['bar-5m-gsm'] ?? barrierCameHeroImage()), 'Wider or heavier-duty GARD. GSM and safety devices quoted on survey.'],
    ];
    $count = count(barrierManufacturerSeeds());
    $html = '<section class="max-w-7xl mx-auto px-6 pb-4"><div class="rounded-3xl border bg-white p-8 md:p-10">';
    $html .= '<p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Came partner</p>';
    $html .= '<h2 class="text-3xl font-semibold tracking-tight text-black mt-2">CAME GARD is the barrier we specify first</h2>';
    $html .= '<p class="mt-4 text-lg text-zinc-700 max-w-3xl leading-relaxed">New rising-arm lanes are designed around CAME GARD — GT4, GARD 4 and GT8 — then connected to Videx, Paxton or GSM when that is how the site lets vehicles in. '
        . 'The other ' . ($count - 1) . ' manufacturers on this service are there so an existing cabinet is maintained honestly, not replaced for a logo. '
        . '<a class="font-semibold text-[#ff6b00] hover:underline" href="' . htmlspecialchars(url('/pages/manufacturers/came'), ENT_QUOTES, 'UTF-8') . '">Open the Came partner page</a>.</p>';
    $html .= '<div class="mt-8 grid md:grid-cols-3 gap-4">';
    foreach ($cards as [$title, $src, $text]) {
        $html .= '<a href="' . htmlspecialchars(url('/pages/manufacturers/came'), ENT_QUOTES, 'UTF-8') . '" class="block border rounded-3xl overflow-hidden hover:border-[#ff6b00]">';
        $html .= '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($title . ' — Icomply Came partner', ENT_QUOTES, 'UTF-8') . '" class="w-full h-44 object-cover" loading="lazy">';
        $html .= '<div class="p-5"><h3 class="font-semibold text-black">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
        $html .= '<p class="text-sm text-zinc-600 mt-2">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p></div></a>';
    }
    $html .= '</div></div></section>';
    return $html;
}

function barrierLocationHtml(string $area, string $keywordName): string
{
    $essay = barrierLocationEssay($area, $keywordName);
    if ($essay === '') {
        return '';
    }
    $other = strcasecmp($area, 'Manchester') === 0 ? 'Burnley' : 'Manchester';
    $otherSlug = areaSlug($other);
    $html = '<div class="mt-6 bg-zinc-50 border-2 border-zinc-200 rounded-3xl p-6 md:p-8">';
    $html .= '<h2 class="text-2xl font-bold text-[#061828]">' . htmlspecialchars($keywordName . ' in ' . $area, ENT_QUOTES, 'UTF-8') . '</h2>';
    $html .= '<p class="mt-4 text-lg text-zinc-900 leading-relaxed">' . htmlspecialchars($essay, ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '<p class="mt-4 text-sm"><a class="font-bold text-[#ff6b00] hover:underline" href="'
        . htmlspecialchars(url('/pages/keywords/vehicle-barriers/' . $otherSlug), ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($keywordName . ' context for ' . $other, ENT_QUOTES, 'UTF-8') . '</a>'
        . ' · <a class="font-bold text-[#ff6b00] hover:underline" href="'
        . htmlspecialchars(url('/pages/manufacturers/came'), ENT_QUOTES, 'UTF-8') . '">Came partner</a>'
        . ' · <a class="font-bold text-[#ff6b00] hover:underline" href="'
        . htmlspecialchars(url('/pages/services/barriers'), ENT_QUOTES, 'UTF-8') . '">UK-wide barriers hub</a></p>';
    $html .= '</div>';
    return $html;
}

/**
 * Product card used when shopify.php has no storefront helper.
 * POA prices are labelled as quotes, not as a fake number.
 */
function barrierProductCardHtml(array $p, string $brandName): string
{
    $title = htmlspecialchars((string)($p['title'] ?? $brandName), ENT_QUOTES, 'UTF-8');
    $blurb = htmlspecialchars((string)($p['blurb'] ?? ''), ENT_QUOTES, 'UTF-8');
    $img = (string)($p['image'] ?? '');
    if ($img !== '' && !preg_match('#^https?://#i', $img)) {
        $img = function_exists('url') ? url($img) : $img;
    }
    $imgAttr = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');
    $price = trim((string)($p['price'] ?? 'POA'));
    $priceLabel = ($price === '' || strcasecmp($price, 'POA') === 0) ? 'Price on application' : $price;
    $badge = htmlspecialchars((string)($p['badge'] ?? ''), ENT_QUOTES, 'UTF-8');
    $html = '<article class="bg-white border rounded-3xl overflow-hidden flex flex-col">';
    if ($img !== '') {
        $html .= '<img src="' . $imgAttr . '" alt="' . $title . '" class="w-full h-44 object-cover" loading="lazy">';
    }
    $html .= '<div class="p-5 flex-1 flex flex-col">';
    if ($badge !== '') {
        $html .= '<p class="text-xs uppercase tracking-wider text-[#ff6b00] font-semibold">' . $badge . '</p>';
    }
    $html .= '<h3 class="font-semibold text-lg text-black mt-1">' . $title . '</h3>';
    $html .= '<p class="text-sm text-zinc-600 mt-2 flex-1">' . $blurb . '</p>';
    $html .= '<p class="mt-4 font-semibold text-[#0B1F3A]">' . htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '<a href="' . htmlspecialchars(url('/contact.php'), ENT_QUOTES, 'UTF-8') . '" class="mt-3 text-sm font-semibold text-[#ff6b00]">Request a quote →</a>';
    $html .= '</div></article>';
    return $html;
}

if (!function_exists('shopifyCardFromManufacturerProduct')) {
    function shopifyCardFromManufacturerProduct(array $p, string $brandSlug, string $brandName): string
    {
        return barrierProductCardHtml($p, $brandName);
    }
}

if (!function_exists('shopifyBuyButtonScript')) {
    function shopifyBuyButtonScript(): string
    {
        return '';
    }
}
