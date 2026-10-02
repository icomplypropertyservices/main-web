#!/usr/bin/env php
<?php
/**
 * Expand ELECTRICAL and GAS keyword families to ~100 each with unique
 * UK-English copy (Stockport / North West). Cost/price keywords are POA only.
 * Source of truth: website/data/seo-matrix-electrical.md + seo-matrix-gas.md
 * (see seo-matrix-rollout-notes.md for P090 / anti-junk / HMO rules).
 *
 * Usage: php website/bin/expand-electrical-gas-matrix.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * @return array<string, array{name:string,service:string,related:string,intro:string,body:string,meta_desc:string,focus_points:list<string>,faq:list<array{0:string,1:string}>,seo_keywords:string}>
 */
function icomplyElectricalGasNewKeywords(): array
{
    $e = 'electrical';
    $g = 'gas-systems';

    $rows = [];

    $add = static function (
        array &$rows,
        string $slug,
        string $name,
        string $service,
        string $related,
        string $intro,
        string $body,
        string $meta,
        array $focus,
        array $faq,
        string $seo
    ): void {
        $rows[$slug] = [
            'name' => $name,
            'service' => $service,
            'related' => $related,
            'intro' => $intro,
            'body' => $body,
            'meta_desc' => $meta,
            'focus_points' => $focus,
            'faq' => $faq,
            'seo_keywords' => $seo,
        ];
    };

    // --- Electrical: rewire / boards / emergency / cost / domestic extras ---
    $add(
        $rows,
        'domestic-rewire',
        'Domestic Rewire',
        $e,
        'rewire',
        'A domestic rewire renews the fixed wiring, accessories and usually the consumer unit in a house or flat so the installation meets current BS 7671 practice. From Stockport we plan full and staged domestic rewires for homeowners and landlords across Greater Manchester and the wider North West.',
        'We survey existing cables, boards and earthing, then agree isolation, making-good and certification before work starts. A domestic rewire is often the right answer after a failed EICR, when wiring is fabric-sheathed or overloaded, or during a void refurb. Engineers attend from Offerton (SK2) into Manchester, Stockport, Bolton, Oldham and surrounding towns. Price of a domestic rewire is always POA after survey — never a guessed online figure.',
        'Domestic rewire across Stockport, Greater Manchester and the North West. BS 7671 planning and certification from Icomply. Quotes are POA after survey.',
        [
            'Full or staged domestic rewire after survey',
            'Consumer unit and circuit protection brought up to current practice',
            'Electrical installation certificate on completion',
            'POA only — no invented rewire prices',
        ],
        [
            ['What is a domestic rewire?', 'It is the planned replacement of fixed wiring and usually the consumer unit in a home or small let, finished with BS 7671 certification.'],
            ['Do I always need a full house rewire?', 'Not always. A partial rewire or board upgrade can be enough if the sound circuits test well. We say so after inspection.'],
            ['How is a domestic rewire priced?', 'Always POA after survey. Circuit count, access, making-good and board type all change the scope.'],
        ],
        'domestic rewire, house rewire, full rewire Stockport, domestic rewire Manchester, BS 7671 rewire North West'
    );

    $add(
        $rows,
        'consumer-unit',
        'Consumer Unit',
        $e,
        'consumer-unit-upgrade',
        'A consumer unit (the modern fuse board) is the heart of a domestic or small commercial electrical installation. Icomply specifies, upgrades and certificates consumer units across the North West from our Stockport base.',
        'We replace outdated fuse boards with dual-RCD or RCBO consumer units, add surge protection where the risk justifies it, and label circuits clearly for the next inspection. Work is notifiable where required. A consumer unit job is quoted POA after we see the existing board, tails and earthing arrangement — we do not publish invented pound prices.',
        'Consumer unit supply, upgrade and certification in Stockport and the North West. POA after we see the existing board.',
        [
            'RCBO or dual-RCD board specifications after survey',
            'Clear circuit labelling and test sheets',
            'Coordination with rewires and EICR remedials',
            'POA — board type and tails decide the quote',
        ],
        [
            ['Is a consumer unit the same as a fuse board?', 'Yes in everyday language. Newer boards use MCBs and RCD/RCBO protection rather than rewirable fuses.'],
            ['Do I need a new consumer unit or a rewire?', 'A board upgrade is sometimes enough. If cables or earthing fail inspection we will say a partial or full rewire is wiser.'],
            ['What does a consumer unit cost?', 'POA after inspection. Enclosure size, RCBO count and any tail or meter-cupboard works change the figure.'],
        ],
        'consumer unit, fuse board, consumer unit upgrade Stockport, RCBO board Manchester, fuse box North West'
    );

    $add(
        $rows,
        'fuse-board',
        'Fuse Board',
        $e,
        'fuse-board-replacement',
        'A fuse board is the older name for the consumer unit that protects your circuits. Replacing a dated fuse board is one of the most common electrical safety upgrades we carry out for North West homes and small lets.',
        'Rewirable or cartridge-fuse boards often lack residual-current protection. We isolate safely, fit a modern consumer unit and test every outgoing circuit. From Stockport we cover Greater Manchester, Cheshire, Lancashire and nearby counties. Fuse board work is POA after we see the existing enclosure and supply arrangement.',
        'Fuse board replacement and consumer unit upgrades across Greater Manchester and the North West. POA after survey.',
        [
            'Safe isolation and like-for-like circuit mapping',
            'Modern RCD/RCBO protection in place of old fuses',
            'Certification suitable for conveyancing and landlords',
            'POA — no catalogue fuse-board prices',
        ],
        [
            ['Can you keep my existing circuit cables?', 'Often yes if they test sound. We will not reuse damaged or undersized cables just to save a board swap.'],
            ['Will I lose power for a full day?', 'Most domestic fuse-board changes are planned as a single isolated visit. Complex cupboards or meter moves take longer — we say so on the quote.'],
            ['How much is a fuse board?', 'POA after we see the board, tails and earthing. Online pound figures are not used.'],
        ],
        'fuse board, fuse board replacement, fuse box upgrade Stockport, consumer unit North West'
    );

    $add(
        $rows,
        'price-of-rewire',
        'Price of Rewire',
        $e,
        'rewire',
        'The price of a rewire is always price on application (POA). Room count, access, making-good, consumer unit type and whether the job is a full or partial rewire all change the figure — we will not invent a pound price on this page.',
        'Searchers looking for “price of rewire” usually want a honest range conversation, not a fake online number. We survey from Stockport, confirm isolation and finishes, then issue a written POA quote for the agreed scope. Domestic rewires, landlord voids and commercial partial rewires are priced separately because the labour and disruption differ. Ask for a site visit across Greater Manchester and the North West.',
        'Price of rewire in the North West is POA after survey. Icomply will not publish invented £ figures for full or partial rewires.',
        [
            'Always POA — never an invented pound price',
            'Scope agreed before any rewire starts',
            'Full, partial and landlord void options',
            'Stockport engineers covering 150+ North West towns',
        ],
        [
            ['Why will you not show a rewire price here?', 'Because a three-bed terrace, a flat with limited access and a commercial unit are not the same job. Publishing a made-up £ figure would be misleading.'],
            ['What do you need for a POA rewire quote?', 'Address, property type, photos of the consumer unit if possible, and whether you want making-good included.'],
            ['Is a partial rewire cheaper?', 'Often, if sound circuits can stay. That still needs testing first — the quote remains POA.'],
        ],
        'price of rewire, rewire cost, how much is a rewire, domestic rewire price POA Stockport'
    );

    $add(
        $rows,
        'rewire-cost',
        'Rewire Cost',
        $e,
        'price-of-rewire',
        'Rewire cost for North West homes and lets is POA after survey. Icomply does not list invented pound prices for full-house or partial rewires.',
        'Cost depends on storeys, circuit count, chasing versus surface trunking, consumer unit location and how much plaster or decoration you want us to reinstate. We explain those variables on site from our Stockport base, then write a fixed-scope POA quote. Related searches such as price of rewire and domestic rewire cost are answered the same way: no catalogue £ figures.',
        'Rewire cost across Greater Manchester & the North West is POA after survey. No invented £ prices from Icomply.',
        [
            'POA after survey only',
            'Clear scope: full vs partial vs board-only',
            'Making-good included only when agreed',
            'Certificate issued on completion',
        ],
        [
            ['Can you give a ballpark over the phone?', 'We can talk through typical scope, but the commercial figure is POA once we have seen the property.'],
            ['Are remediations after an EICR priced separately?', 'Yes. Inspection, remedial circuits and a full rewire are different jobs and are quoted separately — all POA.'],
        ],
        'rewire cost, house rewire cost, full rewire price POA, rewire cost Stockport, rewire cost Manchester'
    );

    $add(
        $rows,
        'domestic-rewire-cost',
        'Domestic Rewire Cost',
        $e,
        'domestic-rewire',
        'Domestic rewire cost is POA. Bedroom count alone is not enough to invent a pound price — access, board position and finishes matter more.',
        'Landlords and owner-occupiers asking for domestic rewire cost usually want to budget a void or a renovation. We survey from Stockport, list circuits to renew, and return a written POA figure for the agreed making-good. We will not publish “from £X” rewire numbers on this site.',
        'Domestic rewire cost in Stockport and the North West is POA after survey. Icomply does not invent £ prices.',
        [
            'POA written quote after inspection',
            'Domestic houses, flats and small lets',
            'Optional making-good scoped in or out',
            'No invented pound prices',
        ],
        [
            ['Why do websites show a rewire from £X?', 'Those figures are rarely based on your cables or board. We treat domestic rewire cost as POA so the quote matches the house.'],
            ['Can you rewire between tenancies?', 'Yes, when access is clear. Lead time and POA cost depend on diary and scope.'],
        ],
        'domestic rewire cost, house rewire cost POA, domestic rewire price Stockport'
    );

    $add(
        $rows,
        'consumer-unit-cost',
        'Consumer Unit Cost',
        $e,
        'consumer-unit',
        'Consumer unit cost is POA after we see the existing board, tails and earthing. Icomply will not invent a pound price for an RCBO or dual-RCD upgrade.',
        'A simple like-for-like swap in an accessible cupboard is a different job from a metal-clad board, SPD, or a meter-operator tail change. We quote from Stockport for Greater Manchester and the North West once that is clear. Related fuse board and consumer unit upgrade pages explain the technical options; this page is only about honest POA pricing.',
        'Consumer unit cost in the North West is POA after inspection. No invented £ board prices.',
        [
            'POA after we see the existing board',
            'RCBO, dual-RCD and SPD options scoped on quote',
            'Tails and earthing checked before we price',
            'No catalogue pound figures',
        ],
        [
            ['Is a consumer unit cheaper than a rewire?', 'Usually, if cables test sound. Both remain POA until surveyed.'],
            ['Does the quote include certification?', 'Yes — the agreed consumer unit works include the relevant BS 7671 certificate.'],
        ],
        'consumer unit cost, fuse board cost, consumer unit price POA Stockport'
    );

    $add(
        $rows,
        'eicr-price',
        'EICR Price',
        $e,
        'eicr-cost',
        'EICR price is £249. That is the published inspection price. Circuit count, access and extra boards can change the visit — anything outside that published price is POA. We do not invent other pound prices.',
        'Landlords searching “EICR price” get the published figure of £249 for the standard EICR. Reports are coded to BS 7671. Remedial works are priced separately after the findings and stay POA. Stockport engineers cover the North West.',
        'EICR price is £249. Larger or unusual installations across Greater Manchester and the North West are POA. No other invented £ fees.',
        [
            'Published EICR price £249',
            'Remedials quoted separately after coding (POA)',
            'Domestic, landlord and commercial options outside that price stay POA',
            'No other invented pound prices',
        ],
        [
            ['Is EICR price the same as EICR cost?', 'Same published price — £249 — unless scope takes the job outside that figure, which is then POA.'],
            ['Do you publish a from-£ EICR list?', 'The published EICR price is £249. We do not invent a ladder of other starting prices.'],
        ],
        'EICR price, EICR cost POA, electrical certificate price Stockport, landlord EICR price'
    );

    $add(
        $rows,
        'emergency-electrician-cost',
        'Emergency Electrician Cost',
        $e,
        'emergency-electrician',
        'Emergency electrician cost is POA. Out-of-hours attendance, make-safe time and any parts needed are explained before we despatch — we do not invent a pound call-out fee on this page.',
        'Burning smells, flooding near electrics or a total loss affecting vulnerable occupants are treated as urgent. Daytime faults that can wait are booked as planned work. From Stockport we cover Greater Manchester and nearby towns when diary and travel allow. Emergency electrician cost is always agreed as POA terms on the call.',
        'Emergency electrician cost in the North West is POA. Attendance terms explained before despatch — no invented £ call-out prices.',
        [
            'POA attendance terms before despatch',
            'Make-safe first, permanent repair when safe',
            'Stockport-based North West cover',
            'No invented emergency pound fees',
        ],
        [
            ['Will I know the cost before you set off?', 'Yes. Emergency electrician cost is explained as POA terms on the phone so you can decide.'],
            ['Is night work more than a daytime visit?', 'Out-of-hours attendance usually carries a premium. That is still POA, not a hidden £ list.'],
        ],
        'emergency electrician cost, emergency electrician price POA, out of hours electrician Stockport'
    );

    $add(
        $rows,
        'fuse-board-cost',
        'Fuse Board Cost',
        $e,
        'fuse-board',
        'Fuse board cost is POA after we inspect the existing enclosure, tails and earthing. Icomply will not invent a pound price for a fuse-board or consumer-unit change.',
        'Search intent overlaps with consumer unit cost. We keep this page so “fuse board cost” reaches the same honest POA message: survey first, written quote second. Stockport engineers cover Greater Manchester and the North West.',
        'Fuse board cost in Stockport and the North West is POA after inspection. No invented £ prices.',
        [
            'POA after seeing the existing fuse board',
            'Modern consumer unit options explained on quote',
            'Certification included in the agreed scope',
            'No invented pound prices',
        ],
        [
            ['Can you price a fuse board from a photo?', 'A photo helps us prepare, but fuse board cost stays POA until we confirm tails, earthing and cupboard space.'],
        ],
        'fuse board cost, fuse box cost, fuse board price POA Stockport'
    );

    $add(
        $rows,
        'electrician',
        'Electrician',
        $e,
        'electrician-near-me',
        'Looking for an electrician in Stockport, Greater Manchester or the wider North West? Icomply provides domestic and commercial electrical work — EICR, rewires, consumer units, fault-finding and planned installs — with written quotes after we understand the job.',
        'We are based in Offerton, Stockport (SK2 5DE) and travel to 150+ towns. An electrician visit might be a landlord certificate, a consumer unit upgrade, a domestic rewire or an emergency make-safe. We do not invent prices; cost-style jobs are POA. Tell us the postcode, property type and what you need.',
        'Electrician covering Stockport, Manchester and the North West. EICR, rewires and consumer units from Icomply. Quotes POA where price varies.',
        [
            'Local Stockport-based electricians',
            'Domestic and light commercial work',
            'EICR, rewire, board and fault-finding',
            'Clear scope before work starts',
        ],
        [
            ['Do you cover my town?', 'If you are in Greater Manchester, Cheshire, Lancashire, Merseyside or nearby Cumbria, ask — we cover 150+ towns from Stockport.'],
            ['Are you an emergency electrician as well?', 'Yes for genuine electrical danger or business-critical loss, subject to diary. Emergency electrician cost is POA on the call.'],
        ],
        'electrician, electrician Stockport, electrician Manchester, electrician North West, local electrician'
    );

    $add(
        $rows,
        'domestic-electrician',
        'Domestic Electrician',
        $e,
        'residential-electrician',
        'A domestic electrician focuses on houses, flats and small lets — sockets, lighting, consumer units, EICR and rewires rather than heavy industrial plant. Icomply provides that domestic electrical service from Stockport across the North West.',
        'Typical domestic electrician jobs include additional sockets, cooker points, extractor fans, board upgrades and full or partial rewires. We work to BS 7671 and explain notification where it applies. Domestic work is quoted after we see the property; cost and price keywords stay POA.',
        'Domestic electrician in Stockport, Greater Manchester and the North West. Homes and small lets — POA after scope.',
        [
            'Houses, flats and small rental properties',
            'Boards, sockets, lighting and rewires',
            'Landlord EICR coordination',
            'POA where price would otherwise be guessed',
        ],
        [
            ['Do you only do domestic work?', 'Domestic is a large part of the diary. We also handle commercial EICR and light commercial installs.'],
            ['Can a domestic electrician fit an EV charger?', 'Where the supply and earthing allow, yes — we survey first.'],
        ],
        'domestic electrician, house electrician, domestic electrician Stockport, home electrician Manchester'
    );

    $add(
        $rows,
        'house-rewire',
        'House Rewire',
        $e,
        'rewire',
        'A house rewire replaces dated or unsafe fixed wiring throughout a dwelling and usually includes a new consumer unit. Icomply plans house rewires for owner-occupiers and landlords from Stockport across Greater Manchester and the North West.',
        'This page sits alongside rewire and domestic rewire for people who search the everyday phrase “house rewire”. We survey, isolate, install and certificate. House rewire cost and price of rewire are always POA after survey — we will not invent a £ figure for a three-bed or any other stock type.',
        'House rewire in Stockport and the North West. BS 7671 install and certification. Price of a house rewire is POA after survey.',
        [
            'Whole-house or phased rewire plans',
            'New consumer unit with the rewire when agreed',
            'Installation certificate on completion',
            'POA — no invented house rewire prices',
        ],
        [
            ['How long does a house rewire take?', 'A typical three-bed can take several days depending on access and making-good. We confirm on survey.'],
            ['What is the price of a house rewire?', 'POA after survey. We will not publish an invented pound price.'],
        ],
        'house rewire, full house rewire, house rewire Stockport, house rewire Manchester, price of rewire POA'
    );

    $add(
        $rows,
        'loft-rewire',
        'Loft Rewire',
        $e,
        'partial-rewire',
        'A loft rewire renews lighting, power and often smoke-alarm or conversion circuits in the roof space. Icomply carries out loft rewires and loft-conversion first/second-fix electrics from Stockport across the North West.',
        'Loft work may be a partial rewire after a failed circuit, or part of a conversion with new lighting, sockets and a dedicated board way. Access, insulation and fire-alarm interfaces are checked before we quote. Loft rewire cost is POA.',
        'Loft rewire and loft conversion electrics across Greater Manchester and the North West. POA after survey.',
        [
            'Loft lighting, power and conversion circuits',
            'Coordination with insulation and boarding',
            'Partial rewire option when the rest of the house is sound',
            'POA after we see access and the existing board',
        ],
        [
            ['Can you rewire a loft without doing the whole house?', 'Yes if the rest of the installation tests well. That is a partial rewire scoped as a loft rewire.'],
            ['Do conversions need Building Control electrical notification?', 'Often yes. We advise the correct route for your loft rewire or conversion electrics.'],
        ],
        'loft rewire, loft conversion electrician, loft electrics Stockport, loft rewire Manchester'
    );

    $add(
        $rows,
        'kitchen-rewire',
        'Kitchen Rewire',
        $e,
        'kitchen-electrical-installation',
        'A kitchen rewire covers the power, lighting, cooker point and extraction circuits that a modern kitchen actually uses. Icomply sequences kitchen rewires with fitters and landlords across the North West from Stockport.',
        'Kitchen electrical work is often a partial rewire: new socket rows, a dedicated cooker or hob supply, lighting and an extractor. We isolate, first-fix before units go in, and second-fix after. Kitchen rewire cost is POA because layouts and appliance loads differ.',
        'Kitchen rewire and kitchen electrical first/second fix in Stockport and the North West. POA after we see the layout.',
        [
            'First and second fix around kitchen units',
            'Cooker, hob and extraction supplies',
            'Works with our kitchen-fitting colleagues when needed',
            'POA — no invented kitchen electrical prices',
        ],
        [
            ['Should the kitchen be empty?', 'First-fix is easiest before units. We can still work in a refurb if access is agreed.'],
            ['Is a kitchen rewire notifiable?', 'New circuits often are. We confirm for your kitchen rewire.'],
        ],
        'kitchen rewire, kitchen electrician, kitchen electrical installation Stockport'
    );

    $add(
        $rows,
        'bathroom-rewire',
        'Bathroom Rewire',
        $e,
        'bathroom-electrical-safety',
        'A bathroom rewire deals with zones, extraction, lighting and any electric-shower or towel-rail circuits to current wiring practice. Icomply plans bathroom electrical work for homes and lets across the North West.',
        'Bathrooms need the right IP ratings, RCD/RCBO protection and extractor provision. We coordinate with bathroom fitters where the room is being gutted. Bathroom rewire cost is POA after we see the existing circuits and the new layout.',
        'Bathroom rewire and bathroom electrical safety in Stockport, Manchester and the North West. POA after survey.',
        [
            'Zone-aware lighting and extraction',
            'Electric shower and towel-rail circuits when specified',
            'RCD/RCBO protection checked at the board',
            'POA after layout and existing circuits are known',
        ],
        [
            ['Can you work to a bathroom fitter’s programme?', 'Yes — first-fix before tiling and second-fix after is the usual sequence.'],
            ['Is an electric shower part of a bathroom rewire?', 'If specified. It needs a suitable circuit and board space — scoped on the POA quote.'],
        ],
        'bathroom rewire, bathroom electrician, bathroom electrical Stockport, electric shower circuit'
    );

    $add(
        $rows,
        'consumer-unit-replacement',
        'Consumer Unit Replacement',
        $e,
        'consumer-unit',
        'Consumer unit replacement is the planned swap of an old fuse board or tired consumer unit for a modern protected board. Icomply carries out replacements across Greater Manchester and the North West from Stockport.',
        'We map circuits, isolate, fit the new enclosure and test. Surge protection and RCBO layouts are specified on the quote. Consumer unit replacement cost is POA — see consumer unit cost for the pricing rule (no invented £).',
        'Consumer unit replacement in Stockport and the North West. RCBO/dual-RCD options. POA after we see the existing board.',
        [
            'Mapped circuits before isolation',
            'RCBO or dual-RCD replacement boards',
            'Test sheets and certification',
            'POA — no invented replacement prices',
        ],
        [
            ['How long does a consumer unit replacement take?', 'Many domestic swaps are a single visit if the cupboard and tails are straightforward. We confirm on survey.'],
            ['Will I need a DNO or meter appointment?', 'Sometimes, if tails or cut-out work is needed. We flag that before you accept the POA quote.'],
        ],
        'consumer unit replacement, fuse board replacement, replace consumer unit Stockport'
    );

    $add(
        $rows,
        'fuse-box-upgrade',
        'Fuse Box Upgrade',
        $e,
        'fuse-board',
        'A fuse box upgrade is the everyday phrase for replacing an old fuse board with a modern consumer unit. Icomply delivers fuse box upgrades for North West homes and small lets from Stockport.',
        'If you searched fuse box upgrade you want the same outcome as consumer unit upgrade or fuse board replacement: safer protection, clearer labelling and a certificate. Cost is POA after inspection.',
        'Fuse box upgrade across Greater Manchester and the North West. POA after we see the existing fuse box.',
        [
            'Old fuse box to modern consumer unit',
            'RCD/RCBO protection as specified',
            'Certificate for house sale or landlord files',
            'POA — no invented fuse-box prices',
        ],
        [
            ['Is a fuse box the same as a consumer unit?', 'Same role. Newer equipment is usually called a consumer unit.'],
            ['What does a fuse box upgrade cost?', 'POA after we see the existing fuse box, tails and earthing.'],
        ],
        'fuse box upgrade, fuse box replacement, fuse box Stockport, consumer unit upgrade'
    );

    $add(
        $rows,
        'electrical-rewire',
        'Electrical Rewire',
        $e,
        'rewire',
        'An electrical rewire is the formal way of saying a property’s fixed wiring is being renewed to current BS 7671 practice. Icomply delivers electrical rewires — full or partial — from Stockport across the North West.',
        'Use this page if you searched “electrical rewire” rather than house rewire or domestic rewire. The process is the same: survey, isolate, install, test, certificate. Electrical rewire cost and price of rewire remain POA.',
        'Electrical rewire in Stockport, Manchester and the North West. Full or partial. Price is POA after survey.',
        [
            'Full and partial electrical rewires',
            'Domestic and light commercial',
            'Certification on completion',
            'POA — no invented rewire prices',
        ],
        [
            ['Is an electrical rewire the same as a house rewire?', 'For a dwelling, yes. We also rewire small commercial units when that is the right remedy.'],
            ['How do you price an electrical rewire?', 'Always POA after survey.'],
        ],
        'electrical rewire, electrical rewiring, electrical rewire Stockport, BS 7671 rewire'
    );

    $add(
        $rows,
        'landlord-rewire',
        'Landlord Rewire',
        $e,
        'domestic-rewire',
        'A landlord rewire is a domestic or HMO rewire planned around a void, a failed EICR or a portfolio upgrade. Icomply programmes landlord rewires across Greater Manchester and the North West from Stockport.',
        'We work with agents on access, making-good and certificate turnaround so the let can resume. C1/C2 findings sometimes mean a partial rewire rather than a full strip-out — we say which after the EICR. Landlord rewire cost is POA; we do not invent portfolio pound rates on the page.',
        'Landlord rewire for rental homes and HMOs in the North West. POA after EICR or survey. Stockport-based team.',
        [
            'Void and between-tenancy programming',
            'Partial rewire where the EICR allows',
            'Certificates for agent and tenant files',
            'POA — no invented landlord rewire prices',
        ],
        [
            ['Can you rewire after an unsatisfactory EICR?', 'Yes. We quote the remedial or rewire scope from the coded findings — POA.'],
            ['Do you cover multi-property landlords?', 'Yes. Ask about scheduling several addresses in the same town.'],
        ],
        'landlord rewire, rental property rewire, landlord rewire Stockport, HMO rewire'
    );

    $add(
        $rows,
        'out-of-hours-electrician',
        'Out of Hours Electrician',
        $e,
        'emergency-electrician',
        'An out of hours electrician attends when a fault cannot wait for the next working day — burning smells, water in a board, or a total loss for vulnerable occupants. Icomply offers out-of-hours electrical attendance from Stockport when capacity allows.',
        'Make-safe is the priority. Permanent repairs follow when parts and daylight allow. Out of hours electrician cost is POA and is explained before despatch. Use the emergency line for genuine danger, not for routine upgrades.',
        'Out of hours electrician cover in Greater Manchester and the North West. Make-safe first. Attendance terms POA.',
        [
            'Evening and weekend make-safe when diary allows',
            'Terms explained before an engineer is despatched',
            'Follow-up permanent repair booking',
            'POA — no invented out-of-hours £ fees',
        ],
        [
            ['Is every evening job an emergency?', 'No. We will say if it can wait for a planned visit. Out-of-hours electrician cost is reserved for genuine urgency.'],
            ['Do you cover the whole North West at 2am?', 'Travel time and diary decide. We are honest about that on the call.'],
        ],
        'out of hours electrician, night electrician, weekend electrician Stockport, emergency electrician North West'
    );

    $add(
        $rows,
        'electrical-call-out',
        'Electrical Call Out',
        $e,
        'electrical-fault-finding',
        'An electrical call out is a reactive visit for a fault, trip or power loss. Icomply provides electrical call-outs across the North West from Stockport, with make-safe or diagnosis first and a clear next step.',
        'Call-outs range from a tripped RCD to a failed board or a damaged cable. We explain attendance terms before travel. Electrical call out cost is POA — we do not publish an invented call-out pound fee. Planned EICR and rewire work is booked separately.',
        'Electrical call out in Stockport, Manchester and the North West. Diagnosis and make-safe. Attendance POA.',
        [
            'Reactive fault-finding from Stockport',
            'Make-safe and advice on lasting repair',
            'Daytime and urgent options',
            'POA attendance — no invented call-out prices',
        ],
        [
            ['What should I do before you arrive?', 'If it is safe, leave the board accessible and describe what tripped or failed. Do not reset a board that smells of burning.'],
            ['Is a call-out the same as an emergency electrician?', 'Related. Emergency attendance is for danger or critical loss; a standard electrical call out can often wait for the next slot.'],
        ],
        'electrical call out, electrician call out, electrical callout Stockport, fault finding electrician'
    );

    $add(
        $rows,
        'additional-socket',
        'Additional Socket',
        $e,
        'socket-installation',
        'Need an additional socket in a kitchen, home office or rental bedroom? Icomply adds sockets on existing or new circuits to BS 7671 for North West homes and small lets.',
        'We check the existing circuit loading and protection before adding an outlet. Sometimes a new circuit from the consumer unit is the safer answer. Additional socket work is quoted after we see the room and board — POA, not an invented per-socket pound list.',
        'Additional socket installation in Stockport and the North West. Quoted POA after we see the circuit and board.',
        [
            'Load check before adding outlets',
            'New circuit from the board when needed',
            'Tidy first and second fix',
            'POA after we see the existing circuit',
        ],
        [
            ['Can you add a socket to an existing ring?', 'Often yes if the circuit and protection are suitable. We test rather than assume.'],
            ['How much is an extra socket?', 'POA. Chasing, access and whether a new circuit is needed all change the scope.'],
        ],
        'additional socket, extra socket, add a socket Stockport, socket installation Manchester'
    );

    $add(
        $rows,
        'cooker-point-installation',
        'Cooker Point Installation',
        $e,
        'kitchen-electrical-installation',
        'A cooker point installation provides a dedicated, correctly rated supply for an electric cooker or oven. Icomply fits cooker points as part of kitchen electrical work across the North West from Stockport.',
        'We size the cable and protective device, run a dedicated circuit from the consumer unit where required, and position the switch for safe isolation. Cooker point installation is POA after we see the kitchen and board space.',
        'Cooker point installation in Stockport, Manchester and the North West. Dedicated circuit, POA after survey.',
        [
            'Dedicated cooker circuit when required',
            'Correct isolation switch position',
            'Works with kitchen first/second fix',
            'POA — no invented cooker-point prices',
        ],
        [
            ['Do I need a new consumer unit for a cooker point?', 'Not always. We need a spare way and suitable tails. If the board is full or outdated we will say so.'],
            ['Gas cooker or electric?', 'This page is the electrical cooker point. Gas cooker installation sits under our gas family.'],
        ],
        'cooker point installation, cooker circuit, electric cooker supply Stockport'
    );

    $add(
        $rows,
        'electric-shower-installation',
        'Electric Shower Installation',
        $e,
        'bathroom-rewire',
        'Electric shower installation needs a dedicated circuit, the right cable size and a board that can take the load. Icomply surveys bathrooms and consumer units across the North West before we fit or replace an electric shower supply.',
        'We will not guess a kW rating from the pavement. Water, bonding and bathroom zones are checked with the electrical side. Electric shower installation is POA after we see the existing board and the bathroom.',
        'Electric shower installation and dedicated shower circuits in Stockport and the North West. POA after survey.',
        [
            'Dedicated shower circuit sized after survey',
            'Board space and protection checked first',
            'Bathroom zone and isolation considered',
            'POA — no invented shower-install prices',
        ],
        [
            ['Can my existing board take an electric shower?', 'Sometimes. We check spare ways, diversity and cable routes before quoting.'],
            ['Do you plumb the shower as well?', 'Electrical supply is our lead. Plumbing can be coordinated — ask when you enquire.'],
        ],
        'electric shower installation, electric shower circuit, bathroom electrician Stockport'
    );

    // --- Gas: boiler / safety / emergency / cost ---
    $add(
        $rows,
        'boiler',
        'Boiler',
        $g,
        'boiler-installation',
        'Need a boiler installed, serviced or repaired in Stockport or the wider North West? Icomply arranges Gas Safe boiler work — install, repair, breakdown and landlord safety — with written quotes after we understand the appliance and system.',
        'This hub covers the everyday search “boiler”: combi, system and regular appliances, flues, controls and the difference between a service and a CP12. Boiler cost, boiler install cost and new boiler cost are always POA. We do not invent pound prices for boilers on this site.',
        'Boiler install, repair and service across Greater Manchester and the North West. Gas Safe engineers from Stockport. Boiler prices are POA.',
        [
            'Install, repair and service options',
            'Combi, system and regular boilers after survey',
            'Landlord gas safety (CP12) can be booked alongside',
            'POA — no invented boiler pound prices',
        ],
        [
            ['Do you supply the boiler or fit mine?', 'Either can be discussed after we see flue, gas supply and system condition.'],
            ['What does a new boiler cost?', 'New boiler cost is POA after survey. We will not publish an invented £ figure.'],
            ['Is a boiler service the same as a CP12?', 'Related but not identical. A CP12 is the landlord gas safety record; servicing is additional maintenance.'],
        ],
        'boiler, boiler Stockport, boiler Manchester, boiler engineer North West, gas boiler'
    );

    $add(
        $rows,
        'boiler-install',
        'Boiler Install',
        $g,
        'boiler-installation',
        'A boiler install (also searched as boiler installation) is the Gas Safe replacement or first-time fit of a central-heating boiler, flue and controls. Icomply surveys North West properties from Stockport before we specify the appliance.',
        'We look at heat demand, hot water, flue route, condensate and the existing system. A like-for-like combi swap is a different job from a system or regular boiler with a cylinder. Boiler install cost is POA — see that page for the pricing rule. Certification and handover notes are included in the agreed scope.',
        'Boiler install across Stockport, Greater Manchester and the North West. Gas Safe. Boiler install cost is POA after survey.',
        [
            'Survey for output, flue and system type',
            'Combi, system and regular options',
            'Commissioning and user handover',
            'POA — no invented install prices',
        ],
        [
            ['How long does a boiler install take?', 'A straightforward like-for-like combi can be a day; flue changes or system redesigns take longer. Timescales sit on the POA quote.'],
            ['Do you remove the old boiler?', 'Yes when that is in the agreed scope, including responsible disposal.'],
        ],
        'boiler install, boiler installation, new boiler install Stockport, combi boiler install Manchester'
    );

    $add(
        $rows,
        'gas-safety',
        'Gas Safety',
        $g,
        'gas-safety-certificate',
        'Gas safety for rented and occupied homes means competent checks of appliances and flues, and — for landlords — a current gas safety record (often called a CP12). Icomply arranges gas safety work across the North West from Stockport.',
        'Homeowners usually want a service or a tightness check; landlords need the annual record and a copy for tenants. Commercial kitchens and plant are scoped separately. Gas safety certificate cost and CP12 cost are POA. We do not invent pound fees for gas safety on this page.',
        'Gas safety checks and landlord records across Greater Manchester and the North West. CP12 and appliance checks. Fees POA.',
        [
            'Landlord annual gas safety records',
            'Appliance and flue checks',
            'Portfolio booking for agents',
            'POA — no invented gas-safety prices',
        ],
        [
            ['How often is landlord gas safety due?', 'Appliances and flues in a rented home must be checked every 12 months, with a record issued to the tenant.'],
            ['Is gas safety the same as a boiler service?', 'A safety check looks at safe operation and flues. A service is deeper maintenance. Many landlords book both — still POA.'],
        ],
        'gas safety, gas safety Stockport, gas safety Manchester, landlord gas safety, CP12'
    );

    $add(
        $rows,
        'landlord-gas',
        'Landlord Gas',
        $g,
        'landlord-gas-safety',
        'Landlord gas work covers annual gas safety records (CP12 / LGSR), portfolio scheduling and advice when an appliance fails a check. Icomply supports private landlords and agents across the North West from Stockport.',
        'If you searched “landlord gas” you want the rental-compliance path, not a homeowner boiler advert. We book single lets, HMOs and multi-property runs. Landlord gas and CP12 cost are POA. Combine with EICR packages when you want one contractor for the void.',
        'Landlord gas safety and CP12 records across Greater Manchester and the North West. Portfolio-friendly. POA.',
        [
            'Annual landlord gas safety records',
            'HMO and multi-let scheduling',
            'Clear advice if an appliance is condemned',
            'POA — no invented landlord gas prices',
        ],
        [
            ['Do all rentals need landlord gas checks?', 'If there are gas appliances or flues, yes — annual checks and a tenant record are required.'],
            ['Can you do gas and EICR on the same void?', 'Yes. Ask for a combined landlord visit plan.'],
        ],
        'landlord gas, landlord gas safety, landlord gas certificate, CP12 landlord, landlord gas Stockport'
    );

    $add(
        $rows,
        'boiler-cost',
        'Boiler Cost',
        $g,
        'boiler',
        'Boiler cost — supply, install or both — is always POA after survey. Icomply will not invent a pound price for a combi, system or regular boiler on this page.',
        'Output, flue, system condition, controls and whether you already own the appliance all change the figure. We survey from Stockport and write a POA quote for the agreed package. Related searches (new boiler cost, boiler install cost, boiler repair cost) follow the same rule.',
        'Boiler cost in the North West is POA after survey. No invented £ boiler prices from Icomply.',
        [
            'Always POA — never an invented pound price',
            'Supply-and-fit or fit-only options',
            'Flue and system extras scoped in writing',
            'Stockport engineers, North West cover',
        ],
        [
            ['Why no “from £X” boiler price?', 'Because a simple swap and a new flue with a system clean are not the same job. Invented £ figures mislead.'],
            ['What do you need for a boiler cost quote?', 'Make, if known, photos of the existing boiler and flue, postcode and whether you want a like-for-like or an upgrade.'],
        ],
        'boiler cost, new boiler cost, boiler price POA, boiler cost Stockport, boiler cost Manchester'
    );

    $add(
        $rows,
        'boiler-install-cost',
        'Boiler Install Cost',
        $g,
        'boiler-install',
        'Boiler install cost is POA after we see the existing appliance, flue and system. Icomply does not publish invented pound prices for boiler installs.',
        'Labour, flue components, magnetic filtration, power flush and controls are listed on the quote so you can compare like with like. Boiler install cost for a combi is still not a catalogue number — access and condensate routes differ. Stockport-based cover across the North West.',
        'Boiler install cost across Greater Manchester and the North West is POA after survey. No invented £ install fees.',
        [
            'POA written install quote after survey',
            'Extras listed rather than hidden',
            'Combi, system and regular installs',
            'No invented pound prices',
        ],
        [
            ['Is labour-only cheaper?', 'Fit-only can be, if you supply a suitable appliance. It remains POA until we confirm compatibility.'],
            ['Does install cost include the boiler?', 'Only if we specify supply-and-fit. The quote will say which.'],
        ],
        'boiler install cost, boiler installation cost, boiler fitting price POA Stockport'
    );

    $add(
        $rows,
        'boiler-repair-cost',
        'Boiler Repair Cost',
        $g,
        'boiler-repair',
        'Boiler repair cost is POA. Diagnosis comes first; parts and labour are quoted once we know the fault. We will not invent a pound repair price on this page.',
        'No heating, no hot water, lockouts and leaks are common call-outs. Some faults are a five-minute reset and advice; others need parts that have to be ordered. Emergency gas engineer attendance terms are also POA. From Stockport we cover Greater Manchester and nearby towns when diary allows.',
        'Boiler repair cost in the North West is POA after diagnosis. No invented £ repair fees.',
        [
            'Diagnose first, then POA parts and labour',
            'Honest advice if replacement is wiser than repair',
            'Breakdown and planned repair slots',
            'No invented pound prices',
        ],
        [
            ['Will you charge just to look?', 'Attendance terms are explained as POA before we travel. Diagnosis and repair can be combined when parts are on the van.'],
            ['Do you repair all brands?', 'We work on common domestic boilers after we identify the appliance. Some parts are brand-specific and may need ordering.'],
        ],
        'boiler repair cost, boiler repair price POA, boiler breakdown cost Stockport'
    );

    $add(
        $rows,
        'gas-safety-certificate-cost',
        'Gas Safety Certificate Cost',
        $g,
        'gas-safety-certificate',
        'Gas safety certificate cost (CP12 / landlord gas safety record) is £85. Extra appliances and commercial plant are POA — we do not invent other pound fees.',
        'The published gas safety price is £85. A studio with one combi is not the same visit as an HMO with several appliances; extra appliances are POA. Failed appliances are isolated and explained; remedials are extra and also POA. Stockport engineers cover the North West.',
        'Gas safety certificate cost is £85. Extra appliances in Stockport and the North West are POA. No other invented £ fees.',
        [
            'Published gas safety price £85',
            'Extra appliances and commercial plant are POA',
            'Landlord record issued after a satisfactory check',
            'No other invented pound prices',
        ],
        [
            ['Is the certificate included in the visit?', 'Yes — the published £85 gas safety price includes the record for the agreed appliance scope.'],
            ['Do you charge per appliance?', 'The published gas safety price is £85. Additional appliances are POA, not an invented website £.'],
        ],
        'gas safety certificate cost, CP12 cost, landlord gas certificate price POA'
    );

    $add(
        $rows,
        'cp12-cost',
        'CP12 Cost',
        $g,
        'cp12',
        'CP12 cost is £85 for the published landlord gas safety record. Extra appliances are POA. We do not invent other pound fees.',
        'The published CP12 price is the same gas safety price: £85. Agents booking several lets or extra appliances get a written POA figure for that extra scope. Same-day CP12 is capacity-dependent and still POA when it sits outside the published price.',
        'CP12 cost is £85. Extra appliances across Greater Manchester and the North West are POA. No other invented £ fees.',
        [
            'Published CP12 / gas safety price £85',
            'Extra appliances are POA',
            'Single lets and portfolios',
            'No other invented pound prices',
        ],
        [
            ['Is CP12 cost the same as a boiler service price?', 'No. The published gas safety / CP12 price is £85. A boiler service is extra maintenance and is POA.'],
            ['Can you do several CP12s in one town?', 'Yes. The published price is £85 each for the standard record. Batching extra scope stays POA.'],
        ],
        'CP12 cost, CP12 price, landlord gas safety cost POA, CP12 Stockport'
    );

    $add(
        $rows,
        'emergency-gas-engineer-cost',
        'Emergency Gas Engineer Cost',
        $g,
        'emergency-gas-engineer',
        'Emergency gas engineer cost is POA. If you smell gas, follow official emergency advice first (supply isolation and the gas emergency service). Our attendance terms are explained before we travel — no invented pound call-out fee.',
        'We attend lockouts, leaks after isolation and no-heating emergencies when it is safe and legal for a private engineer to do so. Smell of gas in the property is a National Gas Emergency situation first. Emergency gas engineer cost is POA on the call. Stockport-based North West cover subject to diary.',
        'Emergency gas engineer cost in the North West is POA. Safety first. No invented £ call-out prices.',
        [
            'Official gas emergency service first if you smell gas',
            'POA attendance terms before despatch',
            'Breakdown and lockout support when safe',
            'No invented pound fees',
        ],
        [
            ['I can smell gas — shall I book you first?', 'No. Open vents if safe, turn off the meter if you can, get out and call the official gas emergency number. Book us after the situation is made safe.'],
            ['Will I know the cost before you set off?', 'Yes. Emergency gas engineer cost is explained as POA terms on the phone.'],
        ],
        'emergency gas engineer cost, emergency gas engineer price POA, gas engineer call out Stockport'
    );

    $add(
        $rows,
        'new-boiler-cost',
        'New Boiler Cost',
        $g,
        'boiler-cost',
        'New boiler cost is POA after survey. Icomply will not invent a pound price for a new combi, system or regular boiler.',
        'People searching new boiler cost want a budget number. The honest answer is that flue, output, system clean and controls move the figure more than a website table. We survey from Stockport and issue a written POA quote. See boiler cost and boiler install cost for the same rule.',
        'New boiler cost in Stockport and the North West is POA after survey. No invented £ prices.',
        [
            'POA after we see flue and system',
            'Supply-and-fit or specified appliance',
            'Optional filters and controls on the quote',
            'No invented pound prices',
        ],
        [
            ['Can you estimate from the make and model I want?', 'We can discuss suitability, but new boiler cost stays POA until the flue and system are checked.'],
        ],
        'new boiler cost, new boiler price POA, replacement boiler cost Stockport'
    );

    $add(
        $rows,
        'combi-boiler',
        'Combi Boiler',
        $g,
        'combi-boiler-installation',
        'A combi boiler heats the home and provides instant hot water without a stored cylinder. Icomply surveys whether a combi is the right boiler for your North West property, then quotes install or repair as POA.',
        'Combis suit many flats and smaller houses if the gas supply, flue and simultaneous hot-water demand are suitable. We will not push a combi where a system or regular boiler is the better fit. Combi boiler cost and combi boiler install remain POA.',
        'Combi boiler install, repair and advice in Stockport and the North West. Suitability first. Prices POA.',
        [
            'Suitability survey before we recommend a combi',
            'Install, repair and service options',
            'Flue and condensate routes checked',
            'POA — no invented combi prices',
        ],
        [
            ['Is a combi cheaper to install than a system boiler?', 'Sometimes, if you already have the right pipework. The figure is still POA.'],
            ['Can you convert from a tanked system to a combi?', 'Often yes after survey. Conversion extras are listed on the POA quote.'],
        ],
        'combi boiler, combi boiler Stockport, combi boiler install Manchester, combination boiler'
    );

    $add(
        $rows,
        'combi-boiler-repair',
        'Combi Boiler Repair',
        $g,
        'boiler-repair',
        'Combi boiler repair covers lockouts, no heating, no hot water and leaks on combination boilers. Icomply diagnoses common domestic combis across the North West from Stockport.',
        'We identify the appliance, test safely and advise repair versus replacement. Combi boiler repair cost is POA after diagnosis. If a new boiler is wiser we say so and point you at boiler install — also POA.',
        'Combi boiler repair in Stockport, Manchester and the North West. Diagnosis first. Repair cost POA.',
        [
            'Fault-finding on common domestic combis',
            'Honest repair versus replace advice',
            'Parts ordered when they are not on the van',
            'POA after diagnosis',
        ],
        [
            ['My combi has no hot water but heating works — can you help?', 'Yes, that is a common split fault on combis. We diagnose before quoting POA repair.'],
        ],
        'combi boiler repair, combination boiler repair, combi repair Stockport'
    );

    $add(
        $rows,
        'system-boiler-installation',
        'System Boiler Installation',
        $g,
        'boiler-installation',
        'System boiler installation is for homes that keep a hot-water cylinder and need a boiler designed to work with that stored system. Icomply surveys system boiler installs across the North West from Stockport.',
        'We check cylinder condition, controls, flue and system cleanliness. A system boiler is not a combi — we will explain the difference in plain English. System boiler installation is POA after survey.',
        'System boiler installation in Greater Manchester and the North West. Gas Safe. POA after survey.',
        [
            'Cylinder and control compatibility checked',
            'Flue and condensate routes planned',
            'Optional system clean scoped on quote',
            'POA — no invented system-boiler prices',
        ],
        [
            ['Should I switch to a combi instead?', 'Only if your hot-water demand and pipework suit it. We advise after survey, not from a sales script.'],
        ],
        'system boiler installation, system boiler install, system boiler Stockport'
    );

    $add(
        $rows,
        'regular-boiler-installation',
        'Regular Boiler Installation',
        $g,
        'boiler-installation',
        'Regular (heat-only) boiler installation suits properties with a traditional tank-and-cylinder layout. Icomply specifies regular boiler installs when that is the right match, not because it is fashionable.',
        'We survey the existing tanks, open-vented or sealed conversion options, and flue. Regular boiler installation is POA. If a combi or system boiler would serve you better we will say so.',
        'Regular boiler installation in Stockport and the North West. Traditional systems. POA after survey.',
        [
            'Heat-only / regular boiler specification after survey',
            'Tank and cylinder arrangements reviewed',
            'Conversion options explained in plain English',
            'POA — no invented regular-boiler prices',
        ],
        [
            ['Is a regular boiler outdated?', 'Not if the system is well designed and you need stored hot water. Suitability is the test.'],
        ],
        'regular boiler installation, heat only boiler, regular boiler Stockport'
    );

    $add(
        $rows,
        'boiler-replacement',
        'Boiler Replacement',
        $g,
        'boiler-install',
        'Boiler replacement is a planned swap of a failed or inefficient appliance for a suitable new boiler. Icomply treats replacement as a survey-led install, not a one-size box on the wall.',
        'We compare like-for-like against an upgrade (combi to combi, or a change of system type). Boiler replacement and new boiler cost are POA. Disposal of the old appliance is included when agreed.',
        'Boiler replacement across Greater Manchester and the North West. Survey-led. Prices POA.',
        [
            'Like-for-like or system-change options',
            'Flue and gas-supply check before we commit',
            'Old appliance removed when in scope',
            'POA — no invented replacement prices',
        ],
        [
            ['Is replacement cheaper than another repair?', 'Sometimes, if parts are scarce or the heat exchanger has failed. We explain both POA paths.'],
        ],
        'boiler replacement, replace boiler, boiler replacement Stockport, new boiler'
    );

    $add(
        $rows,
        'boiler-upgrade',
        'Boiler Upgrade',
        $g,
        'boiler-replacement',
        'A boiler upgrade is a replacement chosen to improve controls, efficiency or hot-water performance — not just to get the heating back on. Icomply surveys upgrades across the North West from Stockport.',
        'We talk through combi versus system, controls and whether a power flush or filter should sit on the same quote. Boiler upgrade cost is POA. We do not invent grant or pound figures.',
        'Boiler upgrade in Stockport, Manchester and the North West. Efficiency and controls after survey. POA.',
        [
            'Upgrade specified after a heating survey',
            'Controls and filtration optional extras',
            'Honest conversation about expected comfort',
            'POA — no invented upgrade prices',
        ],
        [
            ['Do you handle grant paperwork?', 'We can discuss current schemes in general terms. Eligibility is not guaranteed and we do not invent grant values.'],
        ],
        'boiler upgrade, boiler upgrade Stockport, efficient boiler install North West'
    );

    $add(
        $rows,
        'landlord-boiler-service',
        'Landlord Boiler Service',
        $g,
        'boiler-service',
        'A landlord boiler service is planned maintenance of the heating appliance in a rented home, often booked with the annual gas safety record. Icomply offers landlord boiler services across the North West from Stockport.',
        'A service is not automatically a CP12. Many agents book both on the same visit. Landlord boiler service cost is POA. If the appliance fails the safety check we explain isolation and next steps.',
        'Landlord boiler service for rental homes in Greater Manchester and the North West. Can be paired with CP12. POA.',
        [
            'Service visit for rented-property boilers',
            'Optional same-visit CP12',
            'Clear report if the appliance is unsafe',
            'POA — no invented service prices',
        ],
        [
            ['Is a service legally required as well as a CP12?', 'The legal record is the gas safety check. A service is good practice and often required by warranties.'],
        ],
        'landlord boiler service, rental boiler service, landlord boiler Stockport'
    );

    $add(
        $rows,
        'annual-boiler-service',
        'Annual Boiler Service',
        $g,
        'boiler-service',
        'An annual boiler service is manufacturer-style maintenance — clean, check and test — usually once a year. Icomply provides annual boiler services for homeowners and landlords across the North West.',
        'Keep warranty booklets and any Benchmark-style records to hand. Annual boiler service cost is POA. This is separate from a landlord CP12 unless you ask us to combine them.',
        'Annual boiler service in Stockport and the North West. Homeowner and landlord options. POA.',
        [
            'Yearly maintenance visit',
            'Combis, system and regular boilers after we identify them',
            'Optional pairing with landlord gas safety',
            'POA — no invented service prices',
        ],
        [
            ['Will a service restore a broken boiler?', 'A service is maintenance, not a guaranteed repair. Breakdowns are diagnosed as boiler repair — also POA.'],
        ],
        'annual boiler service, yearly boiler service, boiler service Stockport'
    );

    $add(
        $rows,
        'boiler-service-cost',
        'Boiler Service Cost',
        $g,
        'annual-boiler-service',
        'Boiler service cost is POA. Appliance type, access and whether you also want a CP12 change the visit — we do not invent a pound service fee.',
        'Homeowners and landlords searching boiler service cost get the same honest rule as CP12 cost: confirm the appliance, then a written POA figure. Stockport engineers cover the North West.',
        'Boiler service cost in Greater Manchester and the North West is POA. No invented £ service prices.',
        [
            'POA once we know the appliance and access',
            'Service-only or service-plus-CP12 options',
            'No invented pound prices',
            'Local North West attendance from Stockport',
        ],
        [
            ['Why will you not show a service from £X?', 'Because access, flue type and combined CP12 work change the visit. Invented £ figures are not used.'],
        ],
        'boiler service cost, boiler service price POA, annual boiler service cost Stockport'
    );

    $add(
        $rows,
        'gas-fire-service',
        'Gas Fire Service',
        $g,
        'gas-appliance-service',
        'A gas fire service checks the fire, flue or chimney pathway and safe operation. Icomply arranges gas fire servicing for North West homes and lets from Stockport.',
        'Open-flued fires need particular care. We will not service an appliance we cannot test safely. Gas fire service cost is POA. Landlord properties often combine this with the annual gas safety record.',
        'Gas fire service across Greater Manchester and the North West. Flue checks. POA after we know the appliance.',
        [
            'Appliance and flue / chimney pathway check',
            'Landlord or homeowner booking',
            'Honest fail advice if the fire is unsafe',
            'POA — no invented gas-fire prices',
        ],
        [
            ['Can you service a living-flame fire in a bedroom?', 'Location and ventilation rules matter. We advise after we know the room and flue type.'],
        ],
        'gas fire service, gas fire servicing, gas fire Stockport, landlord gas fire'
    );

    $add(
        $rows,
        'commercial-gas',
        'Commercial Gas',
        $g,
        'commercial-gas-installation',
        'Commercial gas covers catering plant, boilers in small commercial buildings and landlord plant rooms — not a domestic combi advert. Icomply scopes commercial gas work across the North West from Stockport.',
        'We survey first. Commercial kitchen gas and commercial gas safety certificates are related pages. Commercial gas is always POA because plant, isolation and access vary widely. We do not invent commercial pound rates.',
        'Commercial gas installation and safety support in Greater Manchester and the North West. POA after survey.',
        [
            'Survey-led commercial gas scope',
            'Catering and small plant options',
            'Safety records where they apply',
            'POA — no invented commercial gas prices',
        ],
        [
            ['Do you work in restaurants?', 'Commercial kitchen gas is a common enquiry. We survey extraction, isolation and appliances first.'],
            ['Is commercial gas the same as a home boiler install?', 'No. Different risk, access and often different paperwork. Both are POA after survey.'],
        ],
        'commercial gas, commercial gas engineer, commercial gas Stockport, catering gas'
    );

    $add(
        $rows,
        'commercial-gas-engineer',
        'Commercial Gas Engineer',
        $g,
        'commercial-gas',
        'A commercial gas engineer attends catering, plant and small commercial heating — not only domestic combis. Icomply provides commercial gas engineer visits across the North West from Stockport when the scope is a match.',
        'Tell us the site type, appliance list and isolation points. Commercial gas engineer attendance is POA. If the job is domestic we will say so and route you to boiler or gas safety pages.',
        'Commercial gas engineer cover in Stockport, Manchester and the North West. Catering and plant. POA after scope.',
        [
            'Commercial and catering gas attendance',
            'Survey before we commit to plant work',
            'Safety documentation where required',
            'POA — no invented commercial call-out prices',
        ],
        [
            ['Can you attend a school or care site?', 'Often yes after we understand access, permits and the appliance list. Still POA.'],
        ],
        'commercial gas engineer, commercial gas engineer Stockport, catering gas engineer'
    );

    $add(
        $rows,
        'gas-central-heating',
        'Gas Central Heating',
        $g,
        'boiler',
        'Gas central heating is the boiler, controls, radiators or underfloor loops that warm the building. Icomply surveys gas central heating installs, upgrades and repairs from Stockport across the North West.',
        'We look at the boiler type, system cleanliness, controls and emitter sizing in practical terms. Gas central heating work is POA. Power flush and smart controls can sit on the same quote when you ask.',
        'Gas central heating install and repair in Greater Manchester and the North West. POA after survey.',
        [
            'Boiler and system viewed together',
            'Controls and filtration optional extras',
            'Repair or upgrade paths after diagnosis',
            'POA — no invented heating prices',
        ],
        [
            ['Do you only do gas, not heat pumps?', 'This page is gas central heating. Ask if you also need a different heat source — we will be clear about what we take on.'],
        ],
        'gas central heating, central heating engineer, gas heating Stockport'
    );

    $add(
        $rows,
        'heating-engineer',
        'Heating Engineer',
        $g,
        'gas-central-heating',
        'Looking for a heating engineer in Stockport or the North West? Icomply provides Gas Safe heating engineer visits for boilers, controls and system faults — with POA quotes after we understand the symptom.',
        'A heating engineer visit might be a breakdown, an annual service, a boiler install or a landlord gas safety check. We will not invent heating-engineer pound rates. Emergency attendance terms are explained before despatch.',
        'Heating engineer covering Stockport, Manchester and the North West. Boilers and gas heating. Quotes POA.',
        [
            'Breakdown, service and install paths',
            'Gas Safe heating work after survey',
            'Landlord and homeowner bookings',
            'POA — no invented heating-engineer prices',
        ],
        [
            ['Are you also an emergency gas engineer?', 'For safe, legal private-engineer work, yes when diary allows. Smell of gas still goes to the official emergency service first.'],
        ],
        'heating engineer, heating engineer Stockport, gas heating engineer Manchester'
    );

    $add(
        $rows,
        'boiler-breakdown',
        'Boiler Breakdown',
        $g,
        'boiler-breakdown-repair',
        'A boiler breakdown usually means no heating, no hot water or a persistent lockout. Icomply attends boiler breakdowns across the North West from Stockport when we can diagnose the appliance safely.',
        'Describe the fault codes if the display shows them. Boiler breakdown repair cost is POA after diagnosis. If the boiler is beyond economical repair we move you to boiler replacement — also POA.',
        'Boiler breakdown repair in Stockport and the North West. Diagnosis first. Repair cost POA.',
        [
            'Reactive breakdown attendance when diary allows',
            'Fault-code and appliance identification',
            'Repair versus replace advice',
            'POA after diagnosis',
        ],
        [
            ['Should I keep resetting the boiler?', 'If it lockouts repeatedly, stop and book diagnosis. Repeated resets can hide a flue or ignition fault.'],
        ],
        'boiler breakdown, boiler breakdown repair, broken boiler Stockport'
    );

    $add(
        $rows,
        'no-heating-engineer',
        'No Heating Engineer',
        $g,
        'boiler-breakdown',
        'No heating? A heating engineer can diagnose the boiler, controls or pump. Icomply attends no-heating call-outs across the North West from Stockport when it is safe to work on the appliance.',
        'Check the thermostat and any programmer first. If the boiler is in lockout, note the code. No-heating attendance is POA. Mid-winter emergency slots are capacity-limited — we are honest about that.',
        'No heating engineer call-out in Greater Manchester and the North West. Boiler and controls diagnosis. POA.',
        [
            'No-heating diagnosis on common domestic systems',
            'Controls and boiler checked together',
            'POA attendance terms',
            'Stockport-based North West cover',
        ],
        [
            ['Radiators cold but boiler on — can you help?', 'Yes. That can be a pump, zone valve or air issue. We diagnose rather than guess.'],
        ],
        'no heating engineer, no heating, heating not working Stockport, boiler no heating'
    );

    $add(
        $rows,
        'no-hot-water-boiler',
        'No Hot Water Boiler',
        $g,
        'combi-boiler-repair',
        'No hot water from the boiler — while heating may still work — is a common combi fault and a different diagnosis on a stored system. Icomply investigates no-hot-water boiler faults from Stockport across the North West.',
        'Do not drain or dismantle the appliance. We diagnose, then quote POA repair. If a plate heat exchanger or diverter has failed we will explain the options, including replacement if that is wiser.',
        'No hot water from the boiler — diagnosis in Stockport and the North West. Repair cost POA.',
        [
            'Combi and stored-system no-hot-water paths',
            'Diagnosis before parts are ordered',
            'POA repair after we know the fault',
            'Replacement advice if repair is uneconomic',
        ],
        [
            ['Heating works but no hot water — is the boiler finished?', 'Not always. Many combis fail on the hot-water side only. We diagnose first.'],
        ],
        'no hot water boiler, combi no hot water, boiler no hot water Stockport'
    );

    $add(
        $rows,
        'gas-emergency',
        'Gas Emergency',
        $g,
        'emergency-gas-engineer',
        'A gas emergency — especially a smell of gas — is first a matter for the official gas emergency service, not a private website booking form. Icomply can attend after the situation is made safe, for isolation follow-up, repairs or a landlord record.',
        'If you smell gas: do not use switches or flames, ventilate if safe, turn off the meter if you can, leave the building and call the official emergency number. Our emergency gas engineer page explains private follow-up. Attendance after a gas emergency is POA.',
        'Gas emergency advice for the North West. Official emergency service first. Icomply follow-up POA when it is safe.',
        [
            'Official emergency service first if you smell gas',
            'Private engineer follow-up when safe and legal',
            'Landlord isolation and record advice',
            'POA — no invented emergency prices',
        ],
        [
            ['What is the official gas emergency number?', 'Use the National Gas Emergency Service number published by the network operator — it is widely listed on official UK pages. Do not wait for a private engineer if you smell gas.'],
            ['Can you cap a supply after an emergency isolation?', 'Often yes once the network has made the situation safe. That follow-up is POA.'],
        ],
        'gas emergency, smell of gas, gas leak emergency, emergency gas Stockport'
    );

    $add(
        $rows,
        'smell-of-gas',
        'Smell of Gas',
        $g,
        'gas-emergency',
        'A smell of gas is an emergency. Do not book a routine engineer first — follow official gas emergency advice, then contact Icomply for follow-up repairs or a safety record when it is safe.',
        'We include this keyword so people who type “smell of gas” get safety-first guidance, not a sales pitch. Private tightness tests and appliance checks after isolation are POA. Stockport team, North West follow-up when diary allows.',
        'Smell of gas — official emergency service first. Icomply follow-up tightness tests and repairs in the North West are POA.',
        [
            'Safety-first official emergency steps',
            'Follow-up tightness test when safe',
            'Appliance check or isolation advice',
            'POA for private follow-up work',
        ],
        [
            ['Should I turn the electricity off?', 'Do not operate switches if you smell gas. Leave and call the official emergency service.'],
            ['Can you find a small leak after the emergency service leaves?', 'A tightness test and appliance check can be booked as follow-up — POA.'],
        ],
        'smell of gas, gas smell, gas leak smell, smell of gas Stockport'
    );

    $add(
        $rows,
        'landlord-cp12',
        'Landlord CP12',
        $g,
        'cp12',
        'A landlord CP12 is the gas safety record private landlords need each year for rented homes with gas appliances. Icomply issues landlord CP12 records across the North West from Stockport.',
        'Same document family as CP12, LGSR and landlord gas safety certificate. Landlord CP12 cost is POA. We can batch portfolios and remind you when the next year is due if you ask us to keep a simple schedule.',
        'Landlord CP12 gas safety records in Stockport, Manchester and the North West. Portfolio-friendly. POA.',
        [
            'Annual landlord CP12 records',
            'Tenant copy after a satisfactory check',
            'HMO and single-let options',
            'POA — no invented CP12 prices',
        ],
        [
            ['Is landlord CP12 the same as CP12?', 'Yes — landlord wording. It is the gas safety record for the rental.'],
        ],
        'landlord CP12, CP12 landlord, landlord gas safety certificate, landlord CP12 Stockport'
    );

    $add(
        $rows,
        'tenant-gas-certificate',
        'Tenant Gas Certificate',
        $g,
        'gas-certificate-for-tenants',
        'A tenant gas certificate is the copy of the landlord gas safety record that tenants should receive. Icomply produces the record after the check so landlords and agents can issue it.',
        'Tenants cannot usually commission the legal record themselves unless the landlord agrees. We still explain what the document is. Tenant gas certificate / CP12 cost is billed to the instructing landlord or agent as POA.',
        'Tenant gas certificate (landlord gas safety record copy) across the North West. Issued after the check. POA to the instructing client.',
        [
            'Record suitable to give to tenants',
            'Booked by landlord or agent',
            'Clear appliance list on the document',
            'POA — no invented certificate prices',
        ],
        [
            ['I am a tenant — can I book this?', 'The legal duty sits with the landlord. We can still explain the document. Booking is normally by the landlord or agent.'],
        ],
        'tenant gas certificate, gas certificate for tenants, tenant CP12, landlord gas record'
    );

    $add(
        $rows,
        'gas-safety-inspection',
        'Gas Safety Inspection',
        $g,
        'gas-safety-check',
        'A gas safety inspection is the competent check of appliances and flues that sits behind a gas safety certificate or CP12. Icomply carries out gas safety inspections across the North West from Stockport.',
        'Inspection, check and certificate are the same visit for most landlord jobs. Homeowners may want an inspection without a rental record. Gas safety inspection cost is POA.',
        'Gas safety inspection in Stockport and the North West. Landlord and homeowner options. POA.',
        [
            'Appliance and flue inspection',
            'Landlord record when instructed',
            'Clear fail / pass advice',
            'POA after we know appliance count',
        ],
        [
            ['Is an inspection the same as a service?', 'An inspection is the safety check. A service is extra maintenance.'],
        ],
        'gas safety inspection, gas safety check, gas inspection Stockport'
    );

    $add(
        $rows,
        'condensing-boiler-installation',
        'Condensing Boiler Installation',
        $g,
        'boiler-install',
        'Condensing boiler installation is the current norm for most new domestic gas boilers — they recover extra heat from flue gases. Icomply surveys condensing installs including condensate routes from Stockport across the North West.',
        'A condensing boiler needs a safe condensate discharge as well as a flue. We will not skip that detail to win a cheap job. Condensing boiler installation is POA after survey.',
        'Condensing boiler installation in Greater Manchester and the North West. Flue and condensate planned. POA.',
        [
            'Condensate route planned with the flue',
            'Combi, system or regular condensing types',
            'Commissioning on completion',
            'POA — no invented condensing-boiler prices',
        ],
        [
            ['Can you reuse my old flue?', 'Only if it is compatible and safe. Many upgrades need a new concentric flue — we confirm on survey.'],
        ],
        'condensing boiler installation, condensing boiler, condensing boiler Stockport'
    );

    $add(
        $rows,
        'hydrogen-ready-boiler',
        'Hydrogen Ready Boiler',
        $g,
        'boiler-upgrade',
        'A hydrogen-ready boiler is a current gas boiler designed so it can be converted if a future hydrogen blend or switch is confirmed for your area. Icomply can discuss hydrogen-ready options as part of a normal boiler survey — we will not oversell a fuel that is not at your meter today.',
        'Today the appliance still runs on natural gas (or LPG if specified). We will not invent government dates, grants or pound savings. Hydrogen-ready boiler supply and install is POA after the same flue and system survey as any boiler install.',
        'Hydrogen-ready boiler discussion and install in the North West. Still a gas boiler today. POA after survey. No invented grants or prices.',
        [
            'Honest “runs on gas today” explanation',
            'Same survey as any boiler install',
            'No invented grant or fuel-saving figures',
            'POA for supply and install',
        ],
        [
            ['Is hydrogen at my house now?', 'Almost certainly not as a full switch. We treat this as a standard gas boiler conversation unless your network says otherwise.'],
            ['Should I wait to replace a broken boiler?', 'A failed boiler still needs a safe appliance now. We will not tell you to sit in the cold for a future fuel.'],
        ],
        'hydrogen ready boiler, hydrogen boiler, hydrogen ready boiler Stockport'
    );

    $add(
        $rows,
        'smart-heating-controls-gas',
        'Smart Heating Controls',
        $g,
        'gas-central-heating',
        'Smart heating controls — programmers, room stats and app-connected thermostats — sit on the electrical/controls side of a gas heating system. Icomply fits controls as part of boiler and heating jobs across the North West.',
        'We match controls to the boiler, not the other way around. Smart heating controls are POA because wiring, wireless range and existing valves differ. We do not invent energy-saving pound claims.',
        'Smart heating controls for gas boilers in Stockport and the North West. Fitted with the heating system. POA. No invented bill-saving figures.',
        [
            'Controls matched to the boiler and valves',
            'Wired or wireless options after survey',
            'No invented energy-saving £ claims',
            'POA for supply and fit',
        ],
        [
            ['Will a smart thermostat cut my bill by a set amount?', 'We will not invent a pound saving. Comfort and control usually improve; bills depend on how you use the system.'],
        ],
        'smart heating controls, smart thermostat boiler, heating controls Stockport'
    );

    $add(
        $rows,
        'boiler-pressure-repair',
        'Boiler Pressure Repair',
        $g,
        'boiler-repair',
        'Boiler pressure problems — repeatedly dropping or over-pressuring — need diagnosis, not just another top-up. Icomply investigates boiler pressure faults on North West domestic systems from Stockport.',
        'A single top-up is not a repair. We look for leaks, expansion issues or filling-loop faults. Boiler pressure repair is POA after diagnosis.',
        'Boiler pressure repair in Stockport and the North West. Find the cause, then POA repair.',
        [
            'Pressure drop or over-pressure diagnosis',
            'Leak and expansion vessel checks',
            'Advice if the system needs a wider repair',
            'POA after we know the cause',
        ],
        [
            ['I keep topping up every week — is that normal?', 'No. Weekly topping-up usually means a leak or a failed vessel. Book diagnosis rather than living on the filling loop.'],
        ],
        'boiler pressure, boiler pressure low, boiler pressure repair Stockport'
    );

    $add(
        $rows,
        'same-day-cp12',
        'Same Day CP12',
        $g,
        'gas-certificate-same-day',
        'Same day CP12 means a landlord gas safety record completed when diary and travel allow on the day you call. Icomply offers same-day CP12 where an engineer is free in that North West town — it is not a guaranteed two-hour promise.',
        'Same-day slots are capacity-limited, especially in winter. Same day CP12 cost is still POA. If we cannot attend today we will offer the next honest slot.',
        'Same day CP12 in Greater Manchester and nearby towns when diary allows. Not guaranteed. Cost POA.',
        [
            'Same-day only when an engineer is free nearby',
            'Honest no if the diary is full',
            'Record issued after the check',
            'POA — no invented same-day premiums listed as £',
        ],
        [
            ['Is same-day guaranteed?', 'No. We will not pretend otherwise. We try when travel and diary allow.'],
            ['Does same-day cost more?', 'It can. Any premium is still POA and is stated before you book.'],
        ],
        'same day CP12, same day gas certificate, last minute landlord gas Stockport'
    );

    $add(
        $rows,
        'portfolio-cp12',
        'Portfolio CP12',
        $g,
        'portfolio-gas-certificates',
        'Portfolio CP12 is batch landlord gas safety for agents and landlords with several addresses. Icomply schedules portfolio CP12 runs across Greater Manchester and the North West from Stockport.',
        'Share a spreadsheet of addresses and due dates. We cluster towns to cut wasted travel. Portfolio CP12 cost is POA for the batch — we do not invent a per-property pound rate on the page.',
        'Portfolio CP12 for North West landlords and agents. Clustered visits. Batch pricing POA.',
        [
            'Multi-address scheduling',
            'Town clustering from a Stockport base',
            'Records returned in a consistent format',
            'POA for the batch — no invented per-door £',
        ],
        [
            ['Can you remind us when CP12s expire?', 'We can keep a simple schedule if you ask. The legal duty remains with the landlord.'],
        ],
        'portfolio CP12, multi property gas certificates, agent CP12 North West'
    );

    $add(
        $rows,
        'gas-cooker-safety-check',
        'Gas Cooker Safety Check',
        $g,
        'gas-cooker-installation',
        'A gas cooker safety check confirms the cooker or hob is safe to use, with stable connections and combustion where we can test. Icomply includes cooker checks in landlord gas safety visits and as standalone homeowner checks across the North West.',
        'Cookers in rented homes form part of the annual gas safety record when they belong to the landlord. Tenant-owned appliances are treated carefully — we will say what we can and cannot certificate. Gas cooker safety check cost is POA.',
        'Gas cooker safety check in Stockport and the North West. Landlord or homeowner. POA after we know the appliance.',
        [
            'Cooker and hob safety check',
            'Included in landlord CP12 when the appliance is in scope',
            'Clear advice on tenant-owned cookers',
            'POA — no invented cooker-check prices',
        ],
        [
            ['Is a tenant-owned cooker on the CP12?', 'Often the landlord record covers landlord appliances. We explain what was tested. Do not assume a tenant cooker is included.'],
        ],
        'gas cooker safety check, gas hob safety check, cooker gas safety Stockport'
    );

    $add(
        $rows,
        'lpg-gas-safety',
        'LPG Gas Safety',
        $g,
        'gas-safety',
        'LPG gas safety covers checks and landlord records for liquefied petroleum gas appliances and tanks where we are competent to work. Icomply discusses LPG jobs across the North West from Stockport after we confirm the fuel and appliance type.',
        'LPG is not the same as natural gas. We will not take on an LPG job we cannot do safely. LPG gas safety is POA after we know the installation. If we need to decline, we will say so quickly.',
        'LPG gas safety discussion and competent checks in the North West. Confirm fuel first. POA. We will not invent LPG prices or claim every LPG job.',
        [
            'Fuel type confirmed before we attend',
            'Landlord or homeowner LPG appliances when in competence',
            'Honest decline if the plant is outside our scope',
            'POA — no invented LPG prices',
        ],
        [
            ['Do you work on every LPG tank?', 'No. We confirm competence and access first. Large commercial LPG plant may be declined.'],
        ],
        'LPG gas safety, LPG boiler, LPG landlord gas, LPG Stockport'
    );

    $add(
        $rows,
        'combi-boiler-cost',
        'Combi Boiler Cost',
        $g,
        'combi-boiler',
        'Combi boiler cost — supply, install or both — is POA after survey. Icomply will not invent a pound price for a combination boiler.',
        'Output, flue, conversion from a tanked system and extras such as a filter all change combi boiler cost. We survey from Stockport and write a POA quote. See boiler cost and boiler install cost for the same rule.',
        'Combi boiler cost in Stockport and the North West is POA after survey. No invented £ combi prices.',
        [
            'POA after flue and system survey',
            'Supply-and-fit or fit-only',
            'Conversion extras listed when needed',
            'No invented pound prices',
        ],
        [
            ['Is a combi cheaper than a system boiler?', 'Sometimes on labour, not always on the whole job. The comparison is still POA after survey.'],
        ],
        'combi boiler cost, combination boiler price POA, combi boiler cost Stockport'
    );

    return $rows;
}

$existing = loadJsonData('keywords', []);
if (!is_array($existing)) {
    $existing = [];
}

$new = icomplyElectricalGasNewKeywords();
$added = 0;
$skipped = 0;
$patchedPoa = 0;

foreach ($new as $slug => $meta) {
    $slug = keywordSlug((string)$slug);
    if ($slug === '' || isset($existing[$slug])) {
        $skipped++;
        continue;
    }
    $existing[$slug] = $meta;
    $added++;
}

$costRe = '/\b(cost|price|quote|how-much|how much)\b/i';
foreach ($existing as $slug => $meta) {
    if (!is_array($meta)) {
        continue;
    }
    $svc = (string)($meta['service'] ?? '');
    if ($svc !== 'electrical' && $svc !== 'gas-systems') {
        continue;
    }
    $name = (string)($meta['name'] ?? $slug);
    $hay = $slug . ' ' . $name;
    if (!preg_match($costRe, $hay)) {
        continue;
    }
    $blob = (string)($meta['intro'] ?? '') . ' ' . (string)($meta['body'] ?? '') . ' ' . (string)($meta['meta_desc'] ?? '');
    $published = function_exists('icomplyVisibleServicePrice') ? icomplyVisibleServicePrice(null, (string)$slug) : null;
    if ($published !== null) {
        $display = (string)$published['display'];
        $amount = (string)$published['amount'];
        $otherPounds = false;
        if (preg_match_all('/£\s*([0-9][0-9,]*)/', $blob, $found)) {
            foreach ($found[1] as $raw) {
                if ((int)str_replace(',', '', $raw) !== (int)$amount) {
                    $otherPounds = true;
                }
            }
        }
        if (!str_contains($blob, $display) || $otherPounds || !preg_match('/\bPOA\b/i', $blob)) {
            $meta['intro'] = rtrim((string)($meta['intro'] ?? ''), '.')
                . '. Published price: ' . $published['label'] . ' ' . $display
                . '. Other scopes stay POA — no other invented prices.';
            $meta['meta_desc'] = $published['label'] . ' ' . $display
                . '. Other scopes in the North West are POA. No other invented £ prices from Icomply, Stockport.';
            $existing[$slug] = $meta;
            $patchedPoa++;
        }
        continue;
    }
    $needsPoa = !preg_match('/\bPOA\b/i', $blob);
    $hasInvented = (bool)preg_match('/£\s*\d/', $blob);
    if (!$needsPoa && !$hasInvented) {
        continue;
    }
    $meta['intro'] = rtrim((string)($meta['intro'] ?? ''), '.')
        . '. Pricing for this search is POA after we confirm scope — Icomply does not invent pound prices.';
    $meta['body'] = rtrim((string)($meta['body'] ?? ''), '.')
        . ' Cost and price enquiries are answered with a written POA quote, never a guessed £ figure.';
    if ($hasInvented && isset($meta['meta_desc'])) {
        $meta['meta_desc'] = preg_replace('/£\s*\d+[,\d]*/', 'POA', (string)$meta['meta_desc']);
    }
    if (empty($meta['meta_desc']) || !preg_match('/\bPOA\b/i', (string)$meta['meta_desc'])) {
        $meta['meta_desc'] = $name . ' in the North West is POA after scope. No invented £ prices from Icomply, Stockport.';
    }
    $existing[$slug] = $meta;
    $patchedPoa++;
}

ksort($existing);
saveJsonData('keywords', $existing);

$elec = 0;
$gas = 0;
foreach ($existing as $meta) {
    $s = is_array($meta) ? ($meta['service'] ?? '') : '';
    if ($s === 'electrical') {
        $elec++;
    } elseif ($s === 'gas-systems') {
        $gas++;
    }
}

echo "Electrical + gas keyword expand\n";
echo "Added: {$added}\n";
echo "Skipped existing: {$skipped}\n";
echo "POA-patched existing cost keywords: {$patchedPoa}\n";
echo "Electrical keywords: {$elec}\n";
echo "Gas keywords: {$gas}\n";
echo "Total keywords: " . count($existing) . "\n";
echo "Areas: " . count(getAreas()) . "\n";
echo "Electrical × area routes: " . ($elec * count(getAreas())) . "\n";
echo "Gas × area routes: " . ($gas * count(getAreas())) . "\n";

if ($elec < 100 || $gas < 100) {
    fwrite(STDERR, "WARNING: expected ~100 keywords per family (electrical={$elec}, gas={$gas})\n");
    exit(1);
}

echo "OK\n";
