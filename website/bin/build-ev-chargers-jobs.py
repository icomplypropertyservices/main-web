#!/usr/bin/env python3
"""Write website/data/ev-chargers-jobs.json — EV chargers job lane catalogue.

Re-run from the repo root:
  python3 website/bin/build-ev-chargers-jobs.py
"""
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "website" / "data" / "ev-chargers-jobs.json"

# Each row is a real install/service angle. No published £ figures.
JOBS = [
    {
        "slug": "ev-charger-installation",
        "name": "EV Charger Installation",
        "related": "home-ev-charger",
        "seo_title": "EV Charger Installation | North West | iComply",
        "h1": "EV Charger Installation",
        "angle": "full install from survey to certificate",
        "intro": "EV charger installation is a dedicated circuit, the right protective device and a charge point matched to the supply you actually have. iComply surveys homes and workplaces from Stockport, then installs and certificates to BS 7671 Section 722.",
        "body": "We look at intake capacity, earthing, cable route and where the vehicle parks before any hardware is ordered. Tethered or socketed units, single bay or a small array, are quoted POA after that survey. Enquire with photos of the consumer unit and the parking position.",
        "points": [
            "Survey of supply, earthing and cable route",
            "Dedicated circuit and Section 722 protection",
            "Commissioning and electrical certification",
            "POA enquire — no published price list",
        ],
        "faqs": [
            ("What does an EV charger installation include?", "Survey, supply circuit, the agreed charge point, testing and the electrical certificate for the work. Extras such as a board upgrade are quoted only if the survey shows they are needed."),
            ("Do you publish a standard install price?", "No. Every job is POA after we see capacity, route and mounting. Enquire and we send a written figure."),
            ("Which standard applies?", "BS 7671, including Section 722 for electric vehicle charging, plus the charger manufacturer's instructions."),
        ],
    },
    {
        "slug": "home-ev-charger",
        "name": "Home EV Charger",
        "related": "ev-charger-installation",
        "seo_title": "Home EV Charger Install | Stockport and North West",
        "h1": "Home EV Charger",
        "angle": "domestic driveway or garage overnight charging",
        "intro": "A home EV charger replaces a trickle lead with a dedicated charge point on the driveway, garage or house wall. We match the rating to a typical domestic supply and certificate the new circuit.",
        "body": "Many houses suit a single-phase unit once spare capacity and earthing are confirmed. We discuss tethered versus socketed leads, app scheduling and whether a second vehicle is likely later. The quote is POA after survey — enquire with your postcode and a photo of the fuse board.",
        "points": [
            "Domestic survey including spare capacity",
            "Wall or garage mounting with a dedicated circuit",
            "Smart scheduling explained at handover",
            "POA written quote after the visit",
        ],
        "faqs": [
            ("Will a normal house supply take a home charger?", "Often yes at a 7 kW single-phase rate, but only after we check the main fuse, board and other loads. We do not guess from the kerb."),
            ("Can the charger go on a garage wall?", "Yes when the cable route, isolation and mounting are safe. Long routes or a new post are itemised on the POA quote."),
            ("Is the price on the website?", "No. Home EV charger work is enquire / POA. No invented figures on this page."),
        ],
    },
    {
        "slug": "commercial-ev-charging",
        "name": "Commercial EV Charging",
        "related": "workplace-ev-charging",
        "seo_title": "Commercial EV Charging Install | North West",
        "h1": "Commercial EV Charging",
        "angle": "workplaces, fleets and shared commercial parking",
        "intro": "Commercial EV charging covers staff bays, visitor spaces, depots and blocks that share a supply. iComply plans distribution, protection and load management so several vehicles can charge without guessing at the intake.",
        "body": "We survey maximum demand, where pedestals or wall units should sit, and how access or payment will work if you need it. Three-phase options are discussed when the supply supports them. Commercial schemes are POA — enquire with site photos and a note of how many bays you want now.",
        "points": [
            "Workplace, fleet and shared-bay planning",
            "Load management sized to the intake",
            "Distribution, protection and certification",
            "POA after a site survey",
        ],
        "faqs": [
            ("Can we fit two bays now and add more later?", "Yes. We can leave board space and routes so later bays are less disruptive. That future allowance is written into the POA scope."),
            ("Do you apply to the DNO for us?", "Where notice or a supply upgrade is required we prepare the electrical information. We are not the network operator."),
            ("How is commercial work priced?", "Enquire / POA. Bay count, civils and supply upgrades change the figure. Nothing is priced on this page."),
        ],
    },
    {
        "slug": "7kw-ev-charger",
        "name": "7kW EV Charger",
        "related": "home-ev-charger",
        "seo_title": "7kW EV Charger Installation | Single Phase",
        "h1": "7kW EV Charger",
        "angle": "single-phase overnight home rate",
        "intro": "A 7kW EV charger is the usual single-phase home rate: about a dedicated 32 A circuit when the supply and board allow it. We confirm that before we specify the unit.",
        "body": "The name describes the charge rate, not a promise that every house can take it. Diversity, the main fuse and existing loads decide. If 7 kW is too much we say so and quote a managed or lower arrangement POA. Enquire with the main fuse rating if you know it.",
        "points": [
            "Single-phase 7 kW only when capacity allows",
            "Dedicated circuit and correct protection",
            "Honest downgrade if the supply is tight",
            "POA — rate is not a price",
        ],
        "faqs": [
            ("Does 7kW mean every home can have one?", "No. 7 kW is the charger rating. The supply has to support it. We check before we install."),
            ("Is 7kW three phase?", "No. A typical 7 kW unit is single phase. Three-phase workplace rates are a different job."),
            ("What does it cost?", "POA after survey. Enquire — we do not print a 7 kW tariff or install fee here."),
        ],
    },
    {
        "slug": "22kw-ev-charger",
        "name": "22kW EV Charger",
        "related": "three-phase-ev-charger",
        "seo_title": "22kW EV Charger | Three Phase Workplace",
        "h1": "22kW EV Charger",
        "angle": "higher-rate three-phase charge point",
        "intro": "A 22kW EV charger needs a three-phase supply and a vehicle that can actually use that rate. We only specify it when both are true.",
        "body": "Many cars charge slower than 22 kW on AC even if the post can deliver it. We say that at survey so you do not pay for capacity the fleet cannot use. Distribution, protection and DNO notice are part of the POA scope. Enquire with the supply type and the vehicles you run.",
        "points": [
            "Three-phase supply confirmed first",
            "Matched to what the vehicles can accept",
            "Distribution and protection designed in",
            "POA enquire — no rate card",
        ],
        "faqs": [
            ("Will a 22kW post charge my car faster?", "Only if the car's onboard charger accepts that AC rate. We check the vehicles before recommending 22 kW."),
            ("Can a normal house have 22kW?", "Not on a standard single-phase domestic supply. This job is for sites that already have, or will upgrade to, three phase."),
            ("Do you list a 22kW install price?", "No. Enquire for a POA figure after the supply and bay layout are known."),
        ],
    },
    {
        "slug": "tethered-ev-charger",
        "name": "Tethered EV Charger",
        "related": "untethered-ev-charger",
        "seo_title": "Tethered EV Charger Install | Cable Attached",
        "h1": "Tethered EV Charger",
        "angle": "charge point with the lead fixed to the unit",
        "intro": "A tethered EV charger has the charging lead fixed to the unit, so drivers do not fetch a cable from the boot. It suits a household or fleet that uses one connector type.",
        "body": "We confirm the connector your vehicles need, the lead length for the parking position, and a holster that keeps the cable off the ground. The electrical install is the same dedicated-circuit job as any other charge point, quoted POA. Enquire if you want the lead left on the unit.",
        "points": [
            "Fixed lead matched to your connector",
            "Holster and length planned for the bay",
            "Same Section 722 electrical install",
            "POA after we see the parking layout",
        ],
        "faqs": [
            ("What is a tethered charger?", "The cable lives on the charge point. You plug the vehicle end in. You do not carry a separate lead."),
            ("Can one tethered lead suit every car?", "No. Connector type has to match the vehicles. We confirm that before the unit is ordered."),
            ("Is tethered more expensive on this page?", "We do not publish a comparison price. Both types are POA. Enquire and the written quote shows the unit we specified."),
        ],
    },
    {
        "slug": "untethered-ev-charger",
        "name": "Untethered EV Charger",
        "related": "tethered-ev-charger",
        "seo_title": "Untethered EV Charger | Socketed Charge Point",
        "h1": "Untethered EV Charger",
        "angle": "socketed unit, driver brings the lead",
        "intro": "An untethered EV charger is a socketed charge point. Drivers use their own lead, which helps when several different vehicles share one bay.",
        "body": "Socket type still has to match the leads you own. We check that, plus weatherproofing and how the socket sits relative to the vehicle inlet. Electrical certification is included in the install scope. Pricing is POA — enquire with the vehicles that will share the point.",
        "points": [
            "Socketed unit for shared or mixed vehicles",
            "Lead compatibility checked before order",
            "Weather and mounting considered",
            "Enquire / POA, no socket surcharge listed",
        ],
        "faqs": [
            ("What does untethered mean?", "The charge point has a socket. The driver brings a compatible lead. Nothing is permanently attached."),
            ("Is an untethered unit better for a workplace?", "Often, when staff cars use different leads. A single-vehicle home may prefer tethered. We advise at survey."),
            ("Do you sell the loose lead on this page?", "No shop price is published here. The install is POA. Leads are specified with the quote if you need them."),
        ],
    },
    {
        "slug": "smart-ev-charger",
        "name": "Smart EV Charger",
        "related": "home-ev-charger",
        "seo_title": "Smart EV Charger Install | App Scheduling",
        "h1": "Smart EV Charger",
        "angle": "app control and scheduled charging",
        "intro": "A smart EV charger can schedule charging, report sessions and sometimes follow a tariff window. The electrical install is still a BS 7671 job — the app does not replace protection or certification.",
        "body": "We connect the unit, walk through the app at handover, and leave you able to start a charge without calling us. Wi-Fi or hard-wired data needs are noted at survey. Smart features are included in the POA description of the unit, not priced as a menu on this page. Enquire if you want scheduling.",
        "points": [
            "App handover as part of commissioning",
            "Scheduling explained, not assumed",
            "Data connection checked at survey",
            "POA — no subscription fees invented here",
        ],
        "faqs": [
            ("Does smart charging need the internet?", "Most apps do. We check Wi-Fi reach at the parking position and say if a booster or cable is a separate item."),
            ("Will you set up off-peak times?", "We show you how. Your energy tariff and account stay yours. We do not invent a saving figure."),
            ("Are app subscriptions included?", "Only if the manufacturer includes them. We do not publish a subscription price. Enquire and the quote names the unit."),
        ],
    },
    {
        "slug": "three-phase-ev-charger",
        "name": "Three Phase EV Charger",
        "related": "22kw-ev-charger",
        "seo_title": "Three Phase EV Charger Install | North West",
        "h1": "Three Phase EV Charger",
        "angle": "charge point on a three-phase supply",
        "intro": "A three phase EV charger uses a three-phase supply so higher AC rates are possible. We confirm the supply is actually three phase before any unit is chosen.",
        "body": "Homes and small units on single phase are not converted to three phase as part of a casual charger visit. If the site already has three phase, we design the circuit, protection and load share. If it does not, a supply upgrade is a DNO matter and is excluded until they offer it. All of this is POA. Enquire with a photo of the incoming supply if you can.",
        "points": [
            "Three-phase intake verified on site",
            "No pretend upgrade of a single-phase house",
            "Load share across phases considered",
            "POA enquire after the supply is known",
        ],
        "faqs": [
            ("How do I know if I have three phase?", "The incoming head and meter usually show it. We confirm on the survey rather than from a phone description alone."),
            ("Can you arrange a three-phase upgrade?", "We can describe the electrical load. The DNO decides whether a supply upgrade is offered and on what terms."),
            ("Is three phase priced differently here?", "We do not print a premium. The job is POA once we know the supply and the bays."),
        ],
    },
    {
        "slug": "solar-ev-charger",
        "name": "Solar EV Charger",
        "related": "myenergi-zappi-ev-charger",
        "seo_title": "Solar EV Charger | Divert Surplus Generation",
        "h1": "Solar EV Charger",
        "angle": "charge from on-site generation when it exists",
        "intro": "A solar EV charger can divert surplus generation into the car when the PV system and the charger are designed to work together. We do not bolt a divert mode onto a roof we have not seen.",
        "body": "Survey covers the existing inverter or generation kit, the charger protocol, and whether the household still needs a grid-timed charge. If there is no solar, we say so and quote a standard charge point instead. Divert hardware is POA. Enquire with the PV brand and a photo of the consumer unit.",
        "points": [
            "Existing generation checked before divert is promised",
            "Charger protocol matched to the PV kit",
            "Grid charging still available when the sun is not",
            "POA — no savings claim and no fee list",
        ],
        "faqs": [
            ("Will a solar charger run the car for free?", "No. We do not promise free miles. Surplus divert only helps when the panels are generating more than the house is using."),
            ("Do you install the solar as well?", "Solar PV is a related electrical service. This page is the charge point and the divert interface. Each scope is quoted POA."),
            ("Which brands divert solar?", "Some do, including certain Myenergi units. We specify after we see your inverter, not from a logo on this page."),
        ],
    },
    {
        "slug": "driveway-ev-charger",
        "name": "Driveway EV Charger",
        "related": "pedestal-ev-charger",
        "seo_title": "Driveway EV Charger Install | External Mount",
        "h1": "Driveway EV Charger",
        "angle": "external parking bay on a drive",
        "intro": "A driveway EV charger is mounted for an external bay: house wall, garage pier or a post, with a cable route that stays sensible in weather.",
        "body": "We look at where the inlet sits on the car, how far that is from the board, and whether the lead would cross a path. Groundworks for a new post are identified separately if the house wall will not serve the bay. The electrical install is POA. Enquire with a photo of the drive from the consumer unit.",
        "points": [
            "External mount and weather route",
            "Lead path that does not cross the footpath if we can avoid it",
            "Post or wall decided at survey",
            "POA including any civils we can see",
        ],
        "faqs": [
            ("Can the charger sit on the front wall?", "Often yes. If the bay is too far from the wall we talk about a post instead of an overlong lead."),
            ("Do you dig the drive?", "Only if a post or buried duct is required, and only when that is written into the POA quote."),
            ("What about a shared drive?", "We need to know who owns the wall and the bay. We will not fix to a neighbour's property on assumption."),
        ],
    },
    {
        "slug": "garage-ev-charger",
        "name": "Garage EV Charger",
        "related": "driveway-ev-charger",
        "seo_title": "Garage EV Charger Installation | Indoor Bay",
        "h1": "Garage EV Charger",
        "angle": "charge point inside a garage",
        "intro": "A garage EV charger keeps the unit indoors, with the cable route from the house board or a garage distribution circuit thought through before we drill.",
        "body": "We check isolation, how the door and the parked car share the lead, and whether the garage supply is a spur that cannot take the charger. Ventilation and storage of the lead are practical points, not a claim about fire strategy. Quote is POA. Enquire if the consumer unit is in the house rather than the garage.",
        "points": [
            "Indoor mount and door clearance checked",
            "Garage supply capacity confirmed",
            "Lead stored so it is not a trip hazard",
            "POA — house-to-garage route itemised",
        ],
        "faqs": [
            ("Can you fit a charger in an integral garage?", "Yes, subject to the cable route from the main board and a safe mounting height beside the car."),
            ("What if the garage only has a lighting circuit?", "We will not hang a charger on a lighting circuit. A suitable supply is part of the POA scope."),
            ("Is an indoor unit a different product?", "Sometimes the same hardware, mounted inside. IP rating and lead length are chosen for that bay."),
        ],
    },
    {
        "slug": "wallbox-ev-charger",
        "name": "Wallbox EV Charger",
        "related": "ev-charger-installation",
        "seo_title": "Wallbox EV Charger | Wall-Mounted Install",
        "h1": "Wallbox EV Charger",
        "angle": "wall-mounted charge point",
        "intro": "Wallbox EV charger on this page means a wall-mounted charge point — a unit fixed to a suitable wall, not a pedestal in the bay. The brand name Wallbox is one option among others.",
        "body": "We check the wall construction, fixing zone, and cable entry so the unit is not bridging a damp course badly or sitting where a bumper will hit it. Electrical design is unchanged: dedicated circuit, Section 722 protection, certificate. POA after we see the wall. Enquire with a photo of the proposed fixing point.",
        "points": [
            "Wall construction and fixing zone checked",
            "Cable entry planned before the unit is hung",
            "Brand chosen for the site, not assumed",
            "POA enquire with a photo of the wall",
        ],
        "faqs": [
            ("Do you only fit the Wallbox brand?", "No. Wall-mounted units from Rolec, Myenergi, Easee, Ohme and others are in this lane. Wallbox the brand is available when it suits the job."),
            ("Can it go on a boundary wall?", "Only if you control that wall and the fixings are sound. We say no when the structure is not ours to use."),
            ("Is wall-mounted cheaper than a post?", "Not published here. A post adds civils. Both are POA."),
        ],
    },
    {
        "slug": "pedestal-ev-charger",
        "name": "Pedestal EV Charger",
        "related": "car-park-ev-charging",
        "seo_title": "Pedestal EV Charger | Post-Mounted Bay",
        "h1": "Pedestal EV Charger",
        "angle": "post or pedestal in the parking bay",
        "intro": "A pedestal EV charger stands in or beside the bay when a wall is too far away. The post, base and duct are part of the survey, not an afterthought.",
        "body": "We mark where the pedestal can go without blocking doors, pedestrian routes or access aisles. Civils may be by us or by your groundworker — the POA quote says which. Electrical containment, protection and certification stay with iComply. Enquire with a bay plan or a wide photo.",
        "points": [
            "Post position marked against doors and aisles",
            "Base and duct identified before order",
            "Civils responsibility written on the quote",
            "POA — pedestal is not a hidden extra",
        ],
        "faqs": [
            ("Do you supply the post as well as the charger?", "When the quote says so. Some sites already have a plinth. We do not assume either way."),
            ("Who digs the trench?", "Named on the POA quote. If it is excluded, you will see that before you accept."),
            ("Are pedestals only for car parks?", "No. A long domestic drive can need one too. The survey decides."),
        ],
    },
    {
        "slug": "workplace-ev-charging",
        "name": "Workplace EV Charging",
        "related": "commercial-ev-charging",
        "seo_title": "Workplace EV Charging | Staff Bays North West",
        "h1": "Workplace EV Charging",
        "angle": "staff and visitor charging at a workplace",
        "intro": "Workplace EV charging is for staff and visitor bays at offices, yards and light-industrial units. We plan how many vehicles can charge at once on the supply you have.",
        "body": "Load management, signage positions and who is allowed to plug in are agreed before install. We certificate the electrical work and show a site contact how to spot a tripped device. Staff schemes are POA. Enquire with headcount of EVs and a photo of the car park board.",
        "points": [
            "Staff and visitor bays scoped separately",
            "Simultaneous charging limited to real capacity",
            "Site contact shown basic fault signs",
            "POA enquire — no per-bay menu",
        ],
        "faqs": [
            ("Can every parking space have a charger?", "Only if the supply and the distribution allow it. We often start with a managed group of bays."),
            ("Do you run a payment app for staff?", "If you need one we specify hardware that supports it. We do not take the energy payments ourselves."),
            ("How soon can a workplace be surveyed?", "Enquire with the postcode. Survey and the POA quote come before any install date is promised."),
        ],
    },
    {
        "slug": "fleet-ev-charging",
        "name": "Fleet EV Charging",
        "related": "workplace-ev-charging",
        "seo_title": "Fleet EV Charging | Depot Charge Points",
        "h1": "Fleet EV Charging",
        "angle": "depot charging between shifts",
        "intro": "Fleet EV charging is about vehicles that must be ready for a shift, not a car that sits overnight on a drive. Dwell time and duty cycle decide the rate more than a brochure does.",
        "body": "We ask when vans return, when they leave, and what onboard chargers they have. That stops a depot buying 22 kW posts for vans that only accept 7 kW. Layout, load management and certification are POA. Enquire with the fleet list and the depot supply.",
        "points": [
            "Duty cycle and dwell time asked first",
            "Charger rate matched to the vans",
            "Depot load managed against other plant",
            "POA — no fleet price matrix",
        ],
        "faqs": [
            ("Do you install rapid DC chargers?", "This lane is AC charge points on the building supply. DC rapid equipment is a different design and is not quoted from this page."),
            ("Can depot chargers share a load manager?", "Yes. That is often how a fleet stays inside the existing intake. We model it at survey."),
            ("Will you guarantee morning range?", "No. We install and certificate the electrical work. Vehicle range depends on the van, the route and the state of charge."),
        ],
    },
    {
        "slug": "car-park-ev-charging",
        "name": "Car Park EV Charging",
        "related": "pedestal-ev-charger",
        "seo_title": "Car Park EV Charging | Multi-Bay Install",
        "h1": "Car Park EV Charging",
        "angle": "multi-bay private or workplace car park",
        "intro": "Car park EV charging means several bays in a private or workplace car park, with posts or wall units, containment and a clear idea of who may charge.",
        "body": "We walk the aisles for door swings, pedestrian routes and where a supply can rise. Lighting and existing barriers are noted so we do not block them. Public-tariff operation is only included if you ask for hardware that supports it. The install is POA. Enquire with bay count and a plan if you have one.",
        "points": [
            "Aisles, doors and pedestrian routes walked",
            "Containment routes agreed before civils",
            "Access rules specified with the hardware",
            "POA for the bay count you actually want",
        ],
        "faqs": [
            ("Is this an on-street public charger?", "No. We install on private and workplace land you control. Highway agreements are outside this job."),
            ("Can customers pay by card?", "Only with hardware and a back office that support it. We name that in the POA quote if you need it."),
            ("How many bays can one board feed?", "As many as load management and the intake allow. We will not invent a bay count from the car park size alone."),
        ],
    },
    {
        "slug": "apartment-block-ev-charging",
        "name": "Apartment Block EV Charging",
        "related": "landlord-ev-charger",
        "seo_title": "Apartment Block EV Charging | Shared Supply",
        "h1": "Apartment Block EV Charging",
        "angle": "shared parking for flats and blocks",
        "intro": "Apartment block EV charging uses a shared intake and shared parking. Who pays, who may park, and how load is shared have to be agreed before we fit the first point.",
        "body": "We survey the landlord's supply, rising mains limits and the bay that is actually available to residents. A single commando socket on a lighting riser is not a design. Certification and a short note for the managing agent are part of the job. POA — enquire as the freeholder or agent, not from a resident's assumption about the intake.",
        "points": [
            "Freeholder or agent authority confirmed",
            "Shared intake and riser limits respected",
            "Resident bays and load share designed together",
            "POA with paperwork for the managing agent",
        ],
        "faqs": [
            ("Can a leaseholder order a charger on the communal supply?", "Only with authority from the freeholder or agent. We will ask who owns the intake before we survey as if it were a house."),
            ("Do all flats have to be wired at once?", "No. A first phase of managed bays is normal. Later phases are easier if the first design left capacity."),
            ("Who gets the certificate?", "The electrical certificate for the work goes to the client named on the quote, usually the agent or freeholder."),
        ],
    },
    {
        "slug": "landlord-ev-charger",
        "name": "Landlord EV Charger",
        "related": "apartment-block-ev-charging",
        "seo_title": "Landlord EV Charger | Rental Bay Install",
        "h1": "Landlord EV Charger",
        "angle": "charge point for a rental property parking bay",
        "intro": "A landlord EV charger is a charge point on a rental house or a bay you control, with paperwork an agent can file next to the electrical certificate.",
        "body": "We still survey capacity and earthing. A let does not change Section 722. We note who will own the unit and who may use the app account so a change of tenant is not a mystery. Quotes are POA. Enquire with the tenancy address and whether the bay is allocated.",
        "points": [
            "Allocated bay confirmed with the landlord",
            "Certificate suitable for the property file",
            "App ownership discussed for tenant changes",
            "POA — not bundled into an EICR price",
        ],
        "faqs": [
            ("Does an EICR include a new charger?", "No. An EICR reports on the installation. A new charger is a separate install, quoted POA."),
            ("Can the tenant pay you directly?", "The contract is with the client who instructs us. That is usually the landlord or agent."),
            ("Will you leave operating notes?", "Yes. Handover covers how to charge and who holds the app login. We do not publish a tenant handbook fee."),
        ],
    },
    {
        "slug": "new-build-ev-charger",
        "name": "New Build EV Charger",
        "related": "ev-charger-consumer-unit",
        "seo_title": "New Build EV Charger | First Fix Containment",
        "h1": "New Build EV Charger",
        "angle": "charge point planned with a new build or extension",
        "intro": "A new build EV charger is easier when containment and a spare way are planned before plaster, not chased in afterwards. We coordinate that with the electrical first fix.",
        "body": "If the plot is still open we mark the duct to the bay and a board position that can take the charger circuit. If the house is already finished this becomes a retrofit and we say so. Both are POA. Enquire with the plot stage and the parking layout.",
        "points": [
            "Duct and spare way planned before finishes where we still can",
            "Retrofit called out when the build is already closed",
            "Coordinated with the electrical first fix",
            "POA against the plot stage",
        ],
        "faqs": [
            ("Is a new house required to have a charger?", "Building rules change and depend on the scheme. We do not quote a legal product from this page. We install the charge point you instruct after survey."),
            ("Can the charger be second fix only?", "Yes if the duct and capacity were left. We verify what is actually in the wall before we promise a one-hour hang."),
            ("Do you work for housebuilders?", "Enquire with the site and the specification. Each plot or phase is POA."),
        ],
    },
    {
        "slug": "rolec-ev-charger",
        "name": "Rolec EV Charger",
        "related": "ev-charger-installation",
        "seo_title": "Rolec EV Charger Installation | North West",
        "h1": "Rolec EV Charger",
        "angle": "Rolec charge point supply and install",
        "intro": "Rolec EV charger installs cover Rolec wall and pedestal units for homes, workplaces and small car parks. We fit and certificate the unit; we are not the manufacturer.",
        "body": "Model choice follows the bay, the supply and whether you want tethered or socketed. Rolec's own instructions sit alongside BS 7671 Section 722. Warranty terms stay with Rolec. Our labour and the electrical certificate are POA. Enquire if you already know the Rolec model.",
        "points": [
            "Rolec wall and pedestal options surveyed",
            "Manufacturer instructions followed with Section 722",
            "We install; Rolec remains the manufacturer",
            "POA labour and materials after model choice",
        ],
        "faqs": [
            ("Are you a Rolec dealer with a published discount?", "No discount table is published here. The install is POA."),
            ("Can you replace an old Rolec unit?", "Often yes, after we test the existing circuit and confirm the new model. That is the replacement job as well as this brand page."),
            ("Do you stock every Rolec pedestal?", "We specify what the bay needs and order it. We do not list live shelf prices."),
        ],
    },
    {
        "slug": "myenergi-zappi-ev-charger",
        "name": "Myenergi Zappi EV Charger",
        "related": "solar-ev-charger",
        "seo_title": "Myenergi Zappi EV Charger Install | North West",
        "h1": "Myenergi Zappi EV Charger",
        "angle": "Zappi install, including solar divert where the kit exists",
        "intro": "A Myenergi Zappi EV charger is a smart unit we install and certificate. Solar divert is only part of the scope when you already have compatible generation.",
        "body": "We set the device up, check CT clamp positions where divert is required, and hand over the app. If there is no solar, the Zappi still works as a charge point and we do not invent a PV saving. Installation is POA. Enquire with whether you have PV and the consumer unit location.",
        "points": [
            "Zappi installed to Myenergi guidance and BS 7671",
            "CT clamps only when divert is in scope",
            "App handover included",
            "POA — no energy-saving figure",
        ],
        "faqs": [
            ("Do I need solar to have a Zappi?", "No. Solar divert is optional. The charger still needs a proper electrical install."),
            ("Will you fit eddi at the same time?", "If you want hot-water divert as well, say so. It is a separate device and is quoted POA, not assumed."),
            ("Is Zappi priced on this page?", "No. Enquire for a POA install figure."),
        ],
    },
    {
        "slug": "easee-ev-charger",
        "name": "Easee EV Charger",
        "related": "ev-charger-load-management",
        "seo_title": "Easee EV Charger Installation | Home and Charge",
        "h1": "Easee EV Charger",
        "angle": "Easee Home or Charge install and load balancing",
        "intro": "Easee EV charger installs cover Easee Home and Easee Charge units, including load balancing where more than one unit shares a supply.",
        "body": "We confirm the backplate, the equalisation lead if several units share a limit, and the protective device the instructions require. Equalisation is designed, not improvised on the day. The job is POA. Enquire with how many Easee units you want on the same board.",
        "points": [
            "Easee Home and Charge surveyed as specified",
            "Load balancing designed when units share a limit",
            "Protection matched to Easee instructions",
            "POA enquire with the unit count",
        ],
        "faqs": [
            ("Can several Easee units share one fuse?", "They can share a configured limit when the installation is designed for it. We do not daisy-chain them casually."),
            ("Do you configure the Easee app?", "We commission the unit and show the account holder the app. The account stays yours."),
            ("Is there an Easee price list here?", "No. Installation is POA."),
        ],
    },
    {
        "slug": "ohme-ev-charger",
        "name": "Ohme EV Charger",
        "related": "smart-ev-charger",
        "seo_title": "Ohme EV Charger Installation | Scheduled Charge",
        "h1": "Ohme EV Charger",
        "angle": "Ohme charge point with scheduled charging",
        "intro": "An Ohme EV charger is a compact smart unit for homes and some workplace bays. We install the circuit, commission the unit and leave scheduled charging understandable.",
        "body": "Tariff linking is done in your Ohme account. We do not hold your energy login and we do not promise a bill reduction. Earthing and protection are checked the same way as any other charge point. POA — enquire with the parking photo and whether you already own the Ohme unit.",
        "points": [
            "Ohme unit or client-supplied unit confirmed",
            "Scheduling explained at handover",
            "Your energy account stays yours",
            "POA with no bill-saving claim",
        ],
        "faqs": [
            ("Can you fit an Ohme I have already bought?", "Yes if it is suitable for the supply and still supported. We confirm that before we agree to fit it. Labour is still POA."),
            ("Will Ohme pick the cheapest tariff on its own?", "Features depend on the account and tariff you connect. We do not guarantee a saving."),
            ("Do you publish Ohme install prices?", "No. Enquire for POA."),
        ],
    },
    {
        "slug": "ev-charger-repair",
        "name": "EV Charger Repair",
        "related": "ev-charger-fault-finding",
        "seo_title": "EV Charger Repair | Faults After Install",
        "h1": "EV Charger Repair",
        "angle": "repair of an existing charge point",
        "intro": "EV charger repair is for a charge point that used to work: dead unit, damaged lead, tripped device that will not reset, or a socket that will not latch.",
        "body": "We test the circuit before we condemn the charger. A failed protective device, a loose termination or water in a gland can look like a dead unit. Parts follow the manufacturer where they are still available. If the unit is obsolete we say so and quote replacement separately. Repair is POA. Enquire with the brand, the symptom and a photo.",
        "points": [
            "Circuit tested before the charger is blamed",
            "Manufacturer parts where they still exist",
            "Obsolete units flagged instead of patched forever",
            "POA call-out described on the quote",
        ],
        "faqs": [
            ("Do you repair every brand?", "We repair charge points we can obtain parts and instructions for. If we cannot, we tell you."),
            ("Is a repair the same visit as a full retest?", "We test what we need to find the fault. A full periodic inspection is a different instruction."),
            ("What does a repair cost?", "POA. The symptom and the brand change the parts. No call-out figure is printed here."),
        ],
    },
    {
        "slug": "ev-charger-maintenance",
        "name": "EV Charger Maintenance",
        "related": "ev-charger-repair",
        "seo_title": "EV Charger Maintenance | Planned Checks",
        "h1": "EV Charger Maintenance",
        "angle": "planned check of existing charge points",
        "intro": "EV charger maintenance is a planned visit: terminals, glands, RCD test where it is safe to do so, lead and holster condition, and a note for the site file.",
        "body": "Workplaces with several bays get more value from a scheduled visit than a house with one unit, but both can be maintained. We do not invent a legal service interval. The visit is POA and the checklist is agreed first. Enquire with how many points are on the site.",
        "points": [
            "Terminals, glands, lead and holster inspected",
            "Functional checks agreed in the scope",
            "Written note for the site file",
            "POA — no invented annual contract price",
        ],
        "faqs": [
            ("How often must a charger be serviced?", "There is no single interval we will invent. Manufacturer guidance and how hard the bay is used decide the visit."),
            ("Does maintenance include repairs?", "Inspection is the visit. Parts and repairs are extra and are quoted before we fit them."),
            ("Can several sites be on one instruction?", "Yes. Enquire with the list. Each site is still scoped, and the total is POA."),
        ],
    },
    {
        "slug": "ev-charger-fault-finding",
        "name": "EV Charger Fault Finding",
        "related": "ev-charger-repair",
        "seo_title": "EV Charger Fault Finding | Dead or Tripping",
        "h1": "EV Charger Fault Finding",
        "angle": "diagnose a charger that will not charge",
        "intro": "EV charger fault finding separates a bad charger from a bad supply, a tripped protective device, a vehicle inlet fault or a setting in the app.",
        "body": "We attend with test equipment, not a replacement unit in the van as a guess. You get a finding: repair, reset, setting, or replacement. The diagnostic visit is POA. Enquire with what the lights are doing and whether other circuits in the building are healthy.",
        "points": [
            "Supply, device, charger and vehicle separated",
            "App and settings checked before parts",
            "Finding written so the next step is clear",
            "Diagnostic visit is POA",
        ],
        "faqs": [
            ("The car charges elsewhere. Is it the charger?", "Often, but not always. The lead, the socket and the protective device are still checked."),
            ("Do you need the car on site?", "It helps. If the vehicle is away we can still test the charge point and say what remains unknown."),
            ("Is fault finding free if you then repair?", "No free figure is published. Both the diagnosis and any repair are POA and written down."),
        ],
    },
    {
        "slug": "ev-charger-replacement",
        "name": "EV Charger Replacement",
        "related": "ev-charger-installation",
        "seo_title": "EV Charger Replacement | Swap an Old Unit",
        "h1": "EV Charger Replacement",
        "angle": "replace an existing charge point",
        "intro": "EV charger replacement swaps an old or failed unit for a current one, and rechecks the circuit rather than assuming the old install was right.",
        "body": "We test the existing cable, protection and earthing. If they are suitable the new unit goes on that circuit. If they are not, the extra work is quoted before we proceed. Removal and making safe of the old unit is in the conversation, not left hanging off the wall. POA. Enquire with the existing brand and a photo.",
        "points": [
            "Existing circuit tested, not trusted",
            "New unit specified for the same bay",
            "Old unit removed or made safe as agreed",
            "POA — reuse of cable only if it tests out",
        ],
        "faqs": [
            ("Can you reuse the old cable?", "Only if it tests and is suitable for the new unit. We do not promise reuse from a photo."),
            ("Will the new charger need a new certificate?", "Yes for the work we do. You receive the certificate that matches that work."),
            ("Do you dispose of the old unit?", "We agree that on the quote. We do not print a disposal fee here."),
        ],
    },
    {
        "slug": "ev-charger-survey",
        "name": "EV Charger Survey",
        "related": "ev-charger-installation",
        "seo_title": "EV Charger Survey | Capacity and Route",
        "h1": "EV Charger Survey",
        "angle": "survey before any charger is ordered",
        "intro": "An EV charger survey is the visit that decides capacity, earthing, route and mounting before a unit is ordered. It is how a POA quote becomes a real scope.",
        "body": "We look at the intake, the consumer unit, the parking position and any constraints such as a listed wall or a shared supply. You get a written scope, not a verbal guess. If the job should not proceed we say that too. The survey itself is POA — enquire with the postcode and access times.",
        "points": [
            "Intake, board, route and bay recorded",
            "Written scope before hardware is ordered",
            "A no is a valid survey outcome",
            "Survey visit quoted POA",
        ],
        "faqs": [
            ("Is a survey compulsory?", "We will not install blind. A survey, or enough photos for a simple house, comes before we commit to a unit."),
            ("Can you survey from photos only?", "Sometimes for a straightforward home. Commercial bays and odd earthing arrangements need a visit."),
            ("Does the survey fee come off the install?", "Only if the written quote says so. We do not imply a discount that is not on the paper."),
        ],
    },
    {
        "slug": "ev-charger-certification",
        "name": "EV Charger Certification",
        "related": "ev-charger-installation",
        "seo_title": "EV Charger Certification | Electrical Certificate",
        "h1": "EV Charger Certification",
        "angle": "certificate for a new or altered charge-point circuit",
        "intro": "EV charger certification is the electrical certificate for the charge-point circuit we install or alter. It is not a substitute for an EICR of the whole building.",
        "body": "New circuits receive an electrical installation certificate. Minor alterations receive the certificate that matches that work. We do not stamp a charger someone else fitted last year unless we are instructed to inspect and we can verify the work. Certification of our own install is part of that job. A standalone inspection is POA. Enquire saying whether we fitted the unit.",
        "points": [
            "Certificate matched to the work actually done",
            "Not sold as a whole-house EICR",
            "Third-party installs inspected only when instructed",
            "Standalone inspection is POA",
        ],
        "faqs": [
            ("Do I get a certificate on install day?", "For our installs, yes, once test results are complete. We do not hand over a blank form."),
            ("Can you certificate a charger bought online and fitted by someone else?", "Only after an inspection we are instructed to do, and only if we can verify it. That visit is POA and may end in a remedial list."),
            ("Is this the same as an EICR?", "No. An EICR is a periodic report on an existing installation. This page is the certificate for the charger work."),
        ],
    },
    {
        "slug": "ev-charger-load-management",
        "name": "EV Charger Load Management",
        "related": "commercial-ev-charging",
        "seo_title": "EV Charger Load Management | Shared Capacity",
        "h1": "EV Charger Load Management",
        "angle": "share a limited supply across charge points",
        "intro": "EV charger load management lets several charge points share a limit so the intake is not overloaded. It is a design, not a setting guessed on the app at the end.",
        "body": "We measure or read the spare capacity, choose hardware that can enforce a limit, and write that limit into the commissioning notes. It does not create spare amps that were never there. If the site needs a supply upgrade we say so. Load management is POA. Enquire with the number of bays and the main fuse.",
        "points": [
            "Spare capacity established before a limit is set",
            "Hardware that can actually enforce the share",
            "Limit recorded in the handover notes",
            "POA — load management is not free extra amps",
        ],
        "faqs": [
            ("Will load management avoid a DNO upgrade?", "Sometimes. If the diversified load still exceeds the supply, the DNO upgrade remains necessary."),
            ("Do cars charge more slowly?", "They can, when several charge at once. That is the point of sharing a limit. We explain the behaviour at handover."),
            ("Is this only for commercial sites?", "No. Two home chargers on one house supply can need it too."),
        ],
    },
    {
        "slug": "ev-charger-consumer-unit",
        "name": "EV Charger Consumer Unit",
        "related": "ev-charger-installation",
        "seo_title": "EV Charger Consumer Unit | Board Upgrade",
        "h1": "EV Charger Consumer Unit",
        "angle": "board changes required by a charge point",
        "intro": "An EV charger consumer unit job is the board work a charge point sometimes needs: a spare way, the right protective device, or a board that can be worked on safely.",
        "body": "Not every install needs a new consumer unit. We only quote one when the existing board cannot take the circuit properly. The charger and the board are itemised separately on the POA quote so you can see both. Enquire with a clear photo of the existing board, cover open only if you can do that safely — otherwise leave it and we open it.",
        "points": [
            "New board only when the existing one cannot take the circuit",
            "Charger and board itemised separately",
            "Protective device selected for Section 722",
            "POA — no board price list",
        ],
        "faqs": [
            ("Does every charger need a new consumer unit?", "No. Spare ways and suitable protection are often already there. We upgrade when they are not."),
            ("Can you add a small garage board instead?", "Sometimes that is the cleaner design. It is still POA and still certificated."),
            ("Will the power be off?", "Yes for the changeover. We agree the window before we start. Duration is not promised as a slogan."),
        ],
    },
    {
        "slug": "ev-charger-earthing",
        "name": "EV Charger Earthing",
        "related": "ev-charger-installation",
        "seo_title": "EV Charger Earthing | PME and Section 722",
        "h1": "EV Charger Earthing",
        "angle": "earthing arrangements for a charge point",
        "intro": "EV charger earthing is the check that decides whether a PME supply can feed the charge point as it is, or whether extra protection is required.",
        "body": "Section 722 and the charger instructions set the rules. We test and record what is there. We do not sell an earth electrode on every house by default, and we do not skip one when it is required. Any additional work is a line on the POA quote. Enquire if you have already been told the supply is PME.",
        "points": [
            "PME and other earthing arrangements identified",
            "Section 722 applied to the arrangement we find",
            "Extra electrodes only when required",
            "POA — earthing is not a flat add-on",
        ],
        "faqs": [
            ("Is every UK house PME?", "No. We identify the arrangement on site. A phone guess is not enough."),
            ("Do you always fit an earth rod?", "No. Only when the design requires it. Unnecessary rods are not a product."),
            ("Can earthing be quoted before the visit?", "Only in outline. The POA figure for extra earthing follows the survey."),
        ],
    },
    {
        "slug": "ev-charger-rcd-protection",
        "name": "EV Charger RCD Protection",
        "related": "ev-charger-consumer-unit",
        "seo_title": "EV Charger RCD Protection | Section 722",
        "h1": "EV Charger RCD Protection",
        "angle": "protective device for the charge-point circuit",
        "intro": "EV charger RCD protection is the protective device on the charge-point circuit, chosen for BS 7671 Section 722 and the unit's instructions — not whatever spare RCD is already in the board.",
        "body": "Some chargers include a device and need a different upstream type. Some need the device in the board. We read the instructions and test the result. A wrong device is a fail, not a shortcut. If the board must change, that is quoted separately POA. Enquire with the charger model if you have already chosen one.",
        "points": [
            "Device type taken from Section 722 and the instructions",
            "Built-in charger protection accounted for",
            "Tested and recorded on the certificate",
            "POA if the board cannot accept the device",
        ],
        "faqs": [
            ("Is a standard socket RCD enough?", "Not by assumption. The charger circuit has its own rules. We select the device for that circuit."),
            ("What if the charger already contains protection?", "Then the upstream device has to be compatible. We follow the manufacturer, not a habit."),
            ("Do you sell RCDs on this page?", "No trade price is listed. The device is part of the POA install."),
        ],
    },
    {
        "slug": "dno-notification-ev-charger",
        "name": "DNO Notification EV Charger",
        "related": "ev-charger-installation",
        "seo_title": "DNO Notification for EV Chargers | What We Prepare",
        "h1": "DNO Notification EV Charger",
        "angle": "network-operator notice for a charge point",
        "intro": "DNO notification for an EV charger is the notice the distribution network operator may require before or after a charge point is connected. iComply prepares the electrical information. We are not the DNO.",
        "body": "Whether a job is notify or apply-before-connect depends on the supply and the equipment. We tell you which path the survey shows and what information we can submit. Approval, reinforcement and any DNO charge are the network's, not a figure we invent. Our work to prepare and install is POA. Enquire with the postcode and the MPAN if you have the bill to hand.",
        "points": [
            "Notify versus apply-first explained after survey",
            "Electrical information prepared for the DNO",
            "We are not the network operator",
            "DNO charges are theirs; our work is POA",
        ],
        "faqs": [
            ("Do you guarantee the DNO will approve?", "No. We submit accurate electrical information. The DNO decides."),
            ("Will the DNO charge me?", "Sometimes, for network work. That invoice is from them. We do not reprint it as our price."),
            ("Can you install before they reply?", "Only when the process allows connection before or without prior approval. If they must agree first, we wait."),
        ],
    },
    {
        "slug": "ozev-ev-charger",
        "name": "OZEV EV Charger",
        "related": "home-ev-charger",
        "seo_title": "OZEV EV Charger | Grant Rules Checked at Survey",
        "h1": "OZEV EV Charger",
        "angle": "charge point where a grant scheme might apply",
        "intro": "OZEV EV charger enquiries are about whether a current grant scheme applies to your property and the unit. We do not publish grant amounts and we do not promise that a scheme is open.",
        "body": "Eligibility, approved equipment and paperwork change. We check what is live at survey and say if your job does not qualify. The electrical install is quoted POA either way, so a grant decision is not the only reason to survey. Enquire with the property type — home, flat, workplace or landlord — and we will tell you what we need to look up.",
        "points": [
            "Current scheme rules checked at survey, not assumed",
            "No grant amount printed on this page",
            "Install still quoted POA if no grant applies",
            "Eligibility is not guaranteed",
        ],
        "faqs": [
            ("How much is the OZEV grant?", "We do not publish a figure. Scheme amounts change and may not apply to you. We check at survey."),
            ("Does every home qualify?", "No. Property type, parking and the equipment list all matter. Many jobs do not qualify, and we will say so."),
            ("Is the install free if a grant exists?", "No. A grant, if one applies, is not the whole job. Labour, materials and any extra electrical work are POA."),
        ],
    },
]


def main() -> None:
    jobs = []
    for row in JOBS:
        faqs = [[q, a] for q, a in row["faqs"]]
        jobs.append(
            {
                "slug": row["slug"],
                "name": row["name"],
                "service": "ev-chargers",
                "related": row["related"],
                "seo_title": row["seo_title"],
                "h1": row["h1"],
                "intro": row["intro"],
                "body": row["body"],
                "meta_desc": (
                    row["name"]
                    + " across Greater Manchester and the North West. "
                    + row["angle"][:1].upper()
                    + row["angle"][1:]
                    + ". POA — enquire after survey. iComply, Stockport."
                ),
                "seo_keywords": (
                    row["name"]
                    + ", EV charger, BS 7671 Section 722, North West, Stockport, POA"
                ),
                "focus_points": row["points"],
                "faq": faqs,
            }
        )
    slugs = [j["slug"] for j in jobs]
    if len(slugs) != len(set(slugs)):
        raise SystemExit("duplicate slugs")
    for slug in slugs:
        if "ev-charg" not in slug:
            raise SystemExit(f"slug must contain ev-charg for gitignore: {slug}")
    titles = [j["seo_title"] for j in jobs]
    h1s = [j["h1"] for j in jobs]
    metas = [j["meta_desc"] for j in jobs]
    for label, values in (("title", titles), ("h1", h1s), ("meta", metas)):
        if len(values) != len(set(values)):
            raise SystemExit(f"duplicate {label}")
    blob = json.dumps({"count": len(jobs), "service": "ev-chargers", "jobs": jobs}, indent=2, ensure_ascii=False)
    if any(ch == "£" for ch in blob):
        raise SystemExit("pound sign in catalogue")
    OUT.write_text(blob + "\n", encoding="utf-8")
    print(f"wrote {len(jobs)} jobs → {OUT}")


if __name__ == "__main__":
    main()
