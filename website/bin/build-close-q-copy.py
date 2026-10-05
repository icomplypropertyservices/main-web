#!/usr/bin/env python3
"""Build CLOSE-Q content packs. Does not touch shared templates.

Output:
  website/data/close-q/keyword-hubs.json
  website/data/close-q/job-hubs.json
  website/data/close-q/town-prose.json
  website/data/close-q/area-faqs.json
  website/data/close-q/aov-hub.json

Also writes body/meta/image fields onto keyword records that PR #113
does not edit, and the fire-alarm-call-out record in fire-alarms-lane.json.
"""
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
DATA = ROOT / "website" / "data"
OUT = DATA / "close-q"
NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"
GAS = (
    "Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. "
    "iComply is not Gas Safe registered."
)

TOWNS = {
    "manchester": {
        "name": "Manchester",
        "districts": "M1–M40",
        "stock": "city-centre flats, Victorian terraces and multi-let offices",
        "focus": "high-rise residential and city commercial buildings",
        "near": "Salford, Stockport and Trafford",
    },
    "stockport": {
        "name": "Stockport",
        "districts": "SK1–SK8",
        "stock": "suburban housing, town-centre retail and industrial units",
        "focus": "landlord portfolios and SME commercial sites",
        "near": "Manchester, Cheadle and Hazel Grove",
    },
    "bolton": {
        "name": "Bolton",
        "districts": "BL1–BL7",
        "stock": "terraced housing, converted mills and retail parks",
        "focus": "landlord housing and light industry",
        "near": "Horwich, Farnworth and Bury",
    },
    "salford": {
        "name": "Salford",
        "districts": "M3, M5–M7 and M50",
        "stock": "quayside apartments, older terraces and industrial estates",
        "focus": "mixed-use blocks and MediaCity offices",
        "near": "Manchester, Eccles and Swinton",
    },
    "wigan": {
        "name": "Wigan",
        "districts": "WN1–WN6",
        "stock": "housing estates, town retail and industrial corridors",
        "focus": "multi-site commercial and rented housing",
        "near": "Leigh, Hindley and Ashton-in-Makerfield",
    },
    "oldham": {
        "name": "Oldham",
        "districts": "OL1–OL4 and OL8–OL9",
        "stock": "terraced streets, mill buildings and industrial estates",
        "focus": "residential portfolios and warehouse units",
        "near": "Rochdale, Chadderton and Shaw",
    },
}

IMAGES = {
    "fire-alarms": [
        ("/assets/images/services/fire-alarms.jpg", "Fire alarm panel and devices"),
        ("/assets/images/services/fire-alarms-photo.jpg", "Fire alarm installation on site"),
        ("/assets/images/manufacturers/apollo-fire.jpg", "Apollo fire detection devices"),
    ],
    "aov-air-handling": [
        ("/assets/images/services/aov-air-handling.jpg", "Automatic opening vent for smoke control"),
        ("/assets/images/services/aov-air-handling-photo.jpg", "AOV actuator and stair vent"),
        ("/assets/images/services/fire-alarms.jpg", "Fire alarm interface that opens the vent"),
    ],
    "cctv": [
        ("/assets/images/services/cctv.jpg", "CCTV cameras and recorder"),
        ("/assets/images/services/cctv-photo.jpg", "CCTV installation on a commercial building"),
        ("/assets/images/manufacturers/hikvision.jpg", "Hikvision camera used on surveyed systems"),
    ],
    "electrical": [
        ("/assets/images/services/electrical.jpg", "Consumer unit prepared for an EICR"),
        ("/assets/images/services/electrical-photo.jpg", "Electrical inspection and testing"),
        ("/assets/images/manufacturers/hager-consumer-unit.jpg", "Hager consumer unit"),
    ],
    "access-control": [
        ("/assets/images/services/access-control.jpg", "Access control reader and door hardware"),
        ("/assets/images/services/access-control-photo.jpg", "Access control installation"),
        ("/assets/images/manufacturers/paxton.jpg", "Paxton access control equipment"),
    ],
    "gas-systems": [
        ("/assets/images/services/gas-systems.jpg", "Gas appliance prepared for a safety check"),
        ("/assets/images/services/gas-systems-photo.jpg", "Landlord gas safety visit"),
        ("/assets/images/manufacturers/worcester-bosch.jpg", "Worcester Bosch appliance on a surveyed site"),
    ],
    "door-entry": [
        ("/assets/images/services/door-entry.jpg", "Door entry panel"),
        ("/assets/images/services/door-entry-photo.jpg", "Door entry handset and cabling"),
        ("/assets/images/manufacturers/fermax-door-entry.jpg", "Fermax door entry equipment"),
    ],
    "barriers": [
        ("/assets/images/services/barriers.jpg", "Rising-arm car park barrier"),
        ("/assets/images/manufacturers/came.jpg", "CAME barrier cabinet"),
        ("/assets/images/services/access-control.jpg", "Access reader that releases a barrier lane"),
    ],
    "fra": [
        ("/assets/images/services/fire-risk-assessments.jpg", "Fire risk assessment notes on a building visit"),
        ("/assets/images/services/fire-risk-assessments-photo.jpg", "Survey of escape routes and fire doors"),
        ("/assets/images/services/fire-alarms.jpg", "Existing fire alarm recorded in the assessment"),
    ],
}


def words(text: str) -> int:
    return len(text.split())


def join(parts: list[str]) -> str:
    return "\n\n".join(p.strip() for p in parts if p and p.strip())


def fit_desc(what: str, where: str, who: str) -> str:
    """Build a 140–160 character meta description ending with the POA line."""
    tail = "Request a quote — POA"
    if where:
        body = f"{what} in {where} for {who}"
    else:
        body = f"{what} for {who} across Greater Manchester"
    phrases = [
        ", arranged from Stockport",
        ", Offerton SK2",
        ", Cheshire",
        " — iComply Property Services",
        " today",
        " — call 07517806082",
    ]
    if len(f"{body}. {tail}") > 160:
        body = f"{what} in {where}" if where else what
    for phrase in phrases:
        if 140 <= len(f"{body}. {tail}") <= 160:
            break
        trial = f"{body}{phrase}. {tail}"
        if len(trial) <= 160:
            body = body + phrase
    desc = f"{body}. {tail}"
    if len(desc) < 140:
        filler = " — visit from Offerton, Stockport SK2 5DE"
        need = 140 - len(desc)
        room = 160 - len(desc)
        chunk = filler[:room].rstrip()
        if len(chunk) < need:
            raise SystemExit(f"meta desc cannot pad {len(desc)}: {desc}")
        trimmed = chunk
        while True:
            cut = trimmed.rfind(" ")
            if cut >= need:
                trimmed = trimmed[:cut].rstrip()
            else:
                break
        if len(trimmed) >= need:
            chunk = trimmed
        desc = f"{body}{chunk}. {tail}"
    if not (140 <= len(desc) <= 160) or not desc.endswith(tail):
        raise SystemExit(f"meta desc {len(desc)}: {desc}")
    return desc


def images_for(family: str, label: str, place: str = "") -> list[dict]:
    rows = []
    where = f" in {place}" if place else ""
    for src, alt in IMAGES[family]:
        rows.append({"src": src, "alt": f"{alt}{where} — {label}, iComply Property Services"})
    return rows


def faqs(intent: str, place: str = "") -> list[dict]:
    where = f" in {place}" if place else " across Greater Manchester"
    gas = intent.startswith("Gas") or "gas" in intent.lower()
    rows = [
        {
            "q": f"How do I ask for {intent}{where}?",
            "a": (
                f"Send the address, the building type and what {intent} needs to cover. "
                "iComply replies with a written scope. The price is on application. "
                "Nothing on this page is a fee list."
            ),
        },
        {
            "q": f"Who attends {intent}{where}?",
            "a": (
                (GAS + " ")
                if gas
                else "The visit is carried out by qualified people for that trade. "
            )
            + "The office is in Offerton, Stockport. Travel is part of the POA quote, not a surprise extra.",
        },
        {
            "q": f"Do you publish a price for {intent}?",
            "a": "No. Quotes are price on application after the building, access and scope are known. We do not invent a starting fee.",
        },
        {
            "q": "What should I have ready?",
            "a": (
                "Postcode, floor count, whether the building is occupied, and any existing certificate or panel brand. "
                "Photos of the board, panel or appliance speed the reply. "
                f"The base is {NAP}."
            ),
        },
    ]
    return rows


def local_block(intent: str, town_slug: str) -> str:
    t = TOWNS[town_slug]
    return join([
        (
            f"{intent} in {t['name']} is quoted against the buildings that are actually there, "
            f"not against a national script with the town name swapped in. Outward codes {t['districts']} "
            f"cover {t['stock']}. The work we are usually asked to scope is {t['focus']}. "
            f"A shared stair in that stock is a different visit from a single house on a side street, "
            f"and the quote stays price on application until the address is known."
        ),
        (
            f"Neighbouring cover that often sits on the same diary run includes {t['near']}. "
            f"If the site is just over the boundary, say so. We still price one visit rather than inventing a second product. "
            f"Engineers leave from {NAP}. "
            f"Phone 07517806082 or use the contact form and name {intent} in {t['name']}."
        ),
        (
            f"What we need before a date in {t['name']} is ordinary and specific: the postcode, who is instructing us, "
            f"whether anyone sleeps in the building, and what is already on the wall or in the plant room. "
            f"We will not guess a category, a camera count or a certificate outcome from the district alone. "
            f"{t['name']} agents who hold several units can send one list. Each address is still scoped on its own."
        ),
    ])


# Hand-written hub essays. Each must stand alone at ≥800 words.
ESSAYS: dict[str, str] = {}

ESSAYS["fire-alarm-installation"] = join([
    "Fire alarm installation is the design, supply, fit and commissioning of a detection and alarm system that matches the building in front of us. It is not a tray of domestic smoke alarms treated as if they were a BS 5839 system for a shared stair. iComply arranges installation for landlords, managing agents and commercial occupiers from the workshop at " + NAP + ". The figure is price on application after survey. This page does not carry a per-device price.",
    "The first split is which part of BS 5839 the building actually needs. BS 5839-6 is the domestic recommendation set, used with care on houses and some flats. BS 5839-1 is the non-domestic code for offices, shops, warehouses and many common parts. A house in multiple occupation, a purpose-built block and a mill conversion do not share one diagram. We say which document we are working to before devices are ordered. If the fire risk assessment or the licence conditions already name a category, we read that and challenge it only when the building does not match the paper.",
    "A survey walks the escape route, the rooms where people sleep or work, the plant spaces and the places a panel can actually be read. Ceiling heights, voids, kitchen heat, dusty workshops and outdoor canopies change device choice. We note what is already on the wall: Kentec, Advanced, C-Tec, Morley, Hochiki, Apollo and the mixed systems that appear after several contractors. Keeping a sound panel is often the right job. Replacing it because a catalogue prefers another brand is not.",
    "Category language is plain once it is tied to the building. A manual system, an automatic life-protection system and a property-protection system are different promises. Call points at storey exits, detection in bedrooms or escape routes, and sound levels through doors are design choices, not decorations. Cause and effect — what the panel does to door holders, lifts, vents or plant — is written down. We do not leave an interface as a spare core that nobody has tested.",
    "Installation itself is containment, cable type, device bases, sounders, beacons where noise or hearing means a bell is not enough, and a panel position the responsible person can reach. Loop integrity, address labels and zone charts are part of the job, not a later tidy-up. On an occupied building we agree isolation windows. Silencing a live alarm to make the fit easier, without the responsible person knowing, is not how we work.",
    "Commissioning is the point the system becomes evidence. We prove devices, battery standby, charger health and the interfaces named in the design. The pack left on site includes the certificate for the work done, a logbook start, a zone chart and a short note of what the responsible person tests weekly. That weekly test stays with the building. It is not a visit we pretend to have done from Stockport.",
    "Variations are written in English. If a bedroom is not covered, if a void is inaccessible, or if an existing device is retained with a known limit, the certificate says so. A clean page that hides those limits is worse than a page with a variation. Insurers and fire officers read the notes. So do the agents who inherit the file next year.",
    "After handover, servicing is a separate instruction. For many BS 5839-1 systems the gap between competent-person visits should not exceed six months, and the user still does the weekly test. This installation page is not a maintenance contract and it is not a call-out tariff. Faults, false alarms and device changes are quoted when they arise. Price on application. No menu.",
    "Ask for the work with the full address, storeys, whether anyone sleeps there, the last fire risk assessment if you have it, and a photo of the current panel. Manchester, Stockport, Bolton, Salford and Wigan are on the Greater Manchester diary. Other published towns are quoted the same way: scope first, then a written price. Call 07517806082 or use the contact form. Related reading sits on the fire alarms service page and the fire alarm job lane.",
    "We will not certify a system we have not proved, we will not invent a British Standard category to win a short email, and we will not describe iComply as a scheme-registered fire alarm body if that badge is not held. The useful promise is narrower: a surveyed design, devices that match it, a commissioning pack, and a quote that stays price on application until the building is known.",
])

ESSAYS["fire-alarm-call-out"] = join([
    "A fire alarm call-out is the visit you book when the panel is in fault, the system will not reset, or the building cannot safely carry on with the indication it has. It is not a planned service, and it is not an installation. iComply arranges that attendance from " + NAP + ". The price is on application once we know the panel, the fault text and whether people are still inside. There is no call-out menu on this page.",
    "Before anyone travels, tell us the panel brand if you can read it, the exact text on the display, and whether the sounders are still running. Kentec, Advanced, C-Tec, Morley, Hochiki and Apollo covers are common in Greater Manchester. A photograph of the panel door, taken only if it is safe, saves a wasted first hour. If you can smell burning or you believe there is a fire, follow the premises procedure and call 999. This page is for the alarm system after life safety has been dealt with.",
    "The engineer’s first job is to understand the event, not to silence the panel and leave. Silencing without a record is how false alarms become a habit and how real faults get lost. We agree with the responsible person what may be isolated, for how long, and what remains protected. Isolation is written down. A zone left off overnight without a note is not a repair.",
    "Common causes are tired batteries, a detector in a kitchen or dusty void, a call point that has been left operated, water in a device, or a loop fault after other trades have been in the ceiling. We carry ordinary service parts where that is honest. If the fault needs a replacement device, a loop repair or a panel decision, that work is a separate quote. We do not fold an unknown repair into a pretend fixed fee.",
    "False alarms are investigated rather than shrugged at. A kitchen detector that burns toast every Friday is a device-choice question against the fire strategy, not a reason to bag the head. We will say if a heat or multi-sensor device is the better fit, and we will not swap heads ad hoc when the category depends on smoke detection in that room. The fire risk assessment and any licence conditions stay in view.",
    "Documentation from a call-out is short and specific: time on site, panel text, what was reset, what was isolated, parts fitted, and the recommended next step. That note belongs in the logbook. Insurers and councils ask for it after a run of unwanted alarms. We do not replace the logbook with a text message.",
    "Occupied buildings need a plan for the hour we are there. Shops may need the visit before opening. Shared houses need the responsible person or a keyholder who can make decisions. We will not accept a verbal instruction from someone who cannot authorise isolation. If access fails, the abort is still a real event and is reported as such.",
    "Planned maintenance is the way to have fewer of these visits. For many commercial systems the competent-person gap should not exceed six months, with the weekly user test remaining on site. A call-out does not reset that clock into a full service unless the visit was scoped as one. If you want both, say so when you book and we will price the larger scope.",
    "Coverage is Greater Manchester first, including Manchester, Stockport, Bolton, Salford and Wigan, with other published towns quoted from the same Stockport base. Travel is inside the POA figure. Phone 07517806082 and lead with the town, the panel and whether the system is still in alarm. The contact form takes the same facts overnight.",
    "Related pages are the fire alarms service, the fire alarm installation guide and the fire alarm job lane. Use those for new systems and for servicing. Use this page when something is wrong today. The quote remains price on application. We do not publish a night rate or a minimum hour on this hub.",
])

ESSAYS["aov-installation"] = join([
    "AOV installation is the work of fitting automatic opening vents so a stair, lobby or corridor can clear smoke when the fire alarm calls for it. The vent may be a roof hatch, a façade window, a louvre or a shaft damper. iComply surveys and installs that equipment for blocks and commercial stairs from " + NAP + ". The price is on application after the smoke strategy and the free area are known. This is not a catalogue hatch price.",
    "The usual references are the building’s fire strategy, BS 9991 where a residential block is being designed or refurbished to that code, and the EN 12101 family for the ventilators and controls. We do not invent a free-area number to suit a vent we already have on the van. If the strategy names a geometric area or an aerodynamic area, the survey checks the opening that is actually available, including restricts, curtains and furniture that have crept in front of the sash.",
    "A stair vent that also tries to be a comfort window is a common failure. Rain sensors and temperature control must lose to the fire input. On commissioning we simulate the fire-alarm contact and watch whether a wet sensor still holds the vent shut. If it does, the logic is wrong and the hatch is not a smoke vent until that is corrected. We write that test into the handover note.",
    "Controls, actuators and batteries are the part residents never see and the part that fails quietly. Chain actuators seize. Hinges drop. Batteries date out while a green LED still looks healthy. Installation includes the panel location, the battery capacity for the connected load, and a manual override the responsible person can find. We label the key location. A panel locked in a cupboard nobody can open is not commissioned.",
    "The fire alarm interface is part of the AOV job when the strategy says the vent opens on a specified zone or on a common-alarm signal. We identify the panel, the output and the cause-and-effect line. Taking a spare core and hoping is not an interface. If the fire alarm contractor is someone else, we still need a witnessed test or we record that the interface was not proved.",
    "Roof work and façade work need access equipment, weather and a plan for an occupied block. Residents cannot be left with a stair that is open to the weather overnight because a hatch was removed and the weather turned. We agree the sequence: make safe, fit, prove, and only then leave. Scaffold or a cherry picker is in the quote when the survey shows it is required, not as a surprise on the day.",
    "Retained systems are common on refurbishments. An existing actuator and panel stay when they can still be tested and supported. We name the manufacturer on the note. A takeover is offered only when the brand can still be worked on. Mixing a new chain onto a tired vent without measuring the stroke is how sashes get torn off their hinges.",
    "Handover is a certificate for the work installed, a short user note, and the test we actually did: fire input, battery open, manual override, and rain-sensor priority. The responsible person should be able to describe the weekly or monthly look they own. We do not pretend a maintenance contract exists because an installer once visited.",
    "Tell us the address, storeys, stair count, whether the strategy already exists, and a photo of any current AOV panel. Greater Manchester blocks in Manchester, Salford, Stockport, Bolton and Wigan are typical of the diary. The same questions apply in every published town. Call 07517806082. Related pages are the AOV hub and the fire alarms service, because the interface often sits on that panel.",
    "We will not certify a vent we have not seen travel, we will not bridge out a safety or fire input to force a demonstration, and we will not price the job as a domestic window. Smoke control is price on application after the stair is surveyed. The office remains " + NAP + ".",
])

ESSAYS["cctv-installation"] = join([
    "CCTV installation here means a system that produces footage a manager can actually use: the right view, enough light, a recorder that keeps the days you asked for, and a way for a named person to export a clip. iComply designs and fits that for shops, yards, landlords and offices from " + NAP + ". The quote is price on application after a walk of the site. Camera counts are not sold as a bundle price.",
    "The survey marks entrances, tills, loading doors, car parks and the corridors that matter, then rejects the views that only film sky or a neighbour’s garden. Lens, mounting height and lighting decide whether a face or a number plate is realistic. We say so before the cable goes in. A wide overview and a tight identification view are different cameras. Putting one device in the middle and hoping is how disputes end with useless footage.",
    "Recorders, switches and disks are sized for the retention you need at the quality you need, including nights. True high resolution on every camera is often the wrong spend if the network and the disks cannot hold it. We mix detail where it helps and simpler views where it does not. Remote viewing is configured for named users, with passwords that stay under the client’s control. We do not leave a factory password on a router.",
    "Cabling is part of the design. Power-over-Ethernet budgets, external containment and a sensible place for the recorder matter as much as the camera body. Wireless is offered only where a cable is genuinely unreasonable, and we say when a cable would be more reliable. Hybrid recorders stay in play where an older analogue run is still sound. Ripping it out for the sake of a new logo is not the default.",
    "Data protection is practical on this page, not a lecture. Cameras should film the client’s property and the approaches they are entitled to watch. Signage is part of handover where the site needs it. Audio is not added as a novelty. We will not aim a camera into a neighbour’s window to fill a corner of the plan. If a landlord and a commercial tenant both want access, the user list is written down.",
    "Brands we regularly meet include Hikvision, Dahua and Axis, plus the recorders already in cupboards. We support what is on site when it can still be worked on, and we specify a replacement when the recorder cannot hold the streams or the firmware path is dead. Naming a manufacturer is not a claim that iComply is that manufacturer’s exclusive partner.",
    "Commissioning checks focus, infrared or white light at night, recording on motion or continuous as agreed, time sync, and an export of a test clip while we are still there. If the client cannot find yesterday’s footage with the instructions we leave, the job is not finished. A one-page note of camera names beats a login nobody remembers.",
    "Maintenance and extra cameras are later quotes. A failed disk, a camera down after a knock, or a request to add the rear gate are price on application. This installation page is not a monitoring-station contract and it does not promise police response. It is the system on the wall and the evidence it can produce.",
    "Send the address, what you need to see, roughly how many days of footage you want to keep, and whether anyone already has a recorder. Manchester yards, Stockport industrial units, Bolton retail parks, Salford quayside blocks and Wigan commercial corridors are all in range from Stockport. Call 07517806082 or use the contact form.",
    "We will not invent a camera count to match a budget slogan, we will not hide a recurring fee inside the install, and we will not quote a monitoring centre we do not provide. The written price follows the survey. Until then the work is price on application.",
])

ESSAYS["cctv-near-me"] = join([
    "People searching for CCTV near me usually need a local engineer who can survey this week, not a national script and a van from the other side of the country. iComply works from " + NAP + ". Greater Manchester is the core diary: Manchester, Stockport, Bolton, Salford, Wigan and the other published towns. The quote is price on application after we know the building. Travel is not padded as a separate mystery charge.",
    "Near me covers three different jobs, and we ask which one you mean. A new installation is a design and fit. A repair is a camera, a disk, a switch or a password that has locked the client out. A short service visit is a check that the views you rely on are still recording. Quoting all three as one product is how the wrong person arrives with the wrong parts. Say which it is, and send a photo if something has already failed.",
    "Local knowledge changes the survey. Stockport industrial estates, Manchester rear alleys, Bolton retail parks and Salford apartment car parks do not share a mounting height or a lighting problem. We look at the actual wall, the power, the place a recorder can live, and who is allowed to see the pictures. A landlord and a shop tenant often both think they own the system. We write down who instructs us.",
    "The technical standard is the same as a planned installation. Views have to be usable, nights have to be considered, and the recorder has to keep the days you asked for. We do not fit a domestic doorbell camera and call it a yard system. We also do not overspecify a dozen high-resolution cameras when four honest views would answer the question. The survey is where that is decided, on site, near you.",
    "Existing brands stay when they are sound. Hikvision, Dahua, Axis and a long list of older recorders are what we find in cupboards. If we can work on it, we say so. If the recorder cannot take another camera or the passwords are unknown and unrecoverable, replacement is a separate line on the quote. We do not scrap a working camera to enlarge the job.",
    "Reactive visits are arranged around occupation. A shop that can only close the lane before 8am, or a block where the recorder is in a landlord’s cupboard, needs that fact in the booking. Attendance is booked against the diary. It is not promised for every postcode on every afternoon. If we cannot attend in the window you need, we say so before you wait in.",
    "Data protection still applies because the engineer is local. Cameras film the client’s risk, not the neighbour’s kitchen. Signage and named users are part of handover. We will not share logins with a third party because someone at the door asked. Export of a clip is shown to the instructing client.",
    "Multi-trade visits are possible when you want them. Access control or door entry on the same entrance can be surveyed in one walk, then quoted as the trades they are. They are not silently included in a CCTV price. Price on application for each scope.",
    "To book, send the postcode first. Then the building type, whether this is a new system or a fault, and a phone number that is answered on the day. Call 07517806082. Email and the contact form land in the same queue. We reply with what we think the visit is, and we correct it if we have misunderstood.",
    "This page is the local route into CCTV. The installation guide covers design in more depth. The CCTV service page is the hub. None of them carries a package price. The work near you is price on application after the address is known.",
])

ESSAYS["eicr"] = join([
    "An Electrical Installation Condition Report, the EICR, is the inspection and test of the fixed wiring: consumer unit, circuits, and the accessories that form part of the installation. It is not a portable appliance test and it is not a gas record. iComply carries out EICRs for landlords, agents and commercial occupiers from " + NAP + ". The quote is price on application once we know the boards, the property type and the access. There is no per-circuit menu on this page.",
    "In England, private landlords of relevant homes generally need the installation inspected and tested on the cycle set out in the electrical safety standards and the current GOV.UK guidance. Five years is the figure people repeat. The report itself can demand a shorter interval, and a change of tenancy can bring the date forward. This page is not legal advice. Read the current guidance for the dwelling you actually let. HMOs can have licence conditions on top. Check the council as well as the statute.",
    "The visit opens the distribution board, samples or tests circuits as the inspection requires, and records observations with the usual codes: C1 for danger present, C2 for potentially dangerous, C3 for improvement recommended, and FI where further investigation is required. A satisfactory or unsatisfactory outcome is the report’s conclusion, not a sticker we choose. We explain the codes in plain English so an agent can see what must be done and what is advisory.",
    "Access is the part that makes or breaks the appointment. We need the board, the rooms the circuits serve, and enough time to isolate where isolation is required. Tenants should know we are coming. Commercial sites should say which boards cannot be switched off during trading. If a board is buried in a locked shopfront or a locked bedroom, the limitation goes on the report. We do not write a satisfactory outcome for circuits we could not see.",
    "Remedial work is a separate instruction. An unsatisfactory report is not, by itself, the repair. We can quote the C1 and C2 items, and the further investigation, after the report exists. Some items are done on a return visit once parts arrive; others need a second access. Price on application. We do not sell a pass. A retest covers what was repaired, and the paperwork should show that, not a silent edit of the first PDF.",
    "Consumer unit condition, earthing, bonding, and obvious thermal damage are part of what the inspector is there to see. We do not upgrade a board because it is old if the report does not require it. We also do not ignore a cracked enclosure or a board with signs of overheating because a landlord hoped for a one-line satisfactory. The observation is written. The quote for work comes after.",
    "Portfolios are booked as a list, not as a discount slogan. Each address still has its own board and its own access. An agent can send postcodes, last report dates and key details. We schedule against the diary from Stockport. Combining an EICR with other compliance on the same day is discussed when you ask. It is not assumed, and it does not change the electrical scope.",
    "The pack you receive is the report, the schedules, and a short note of what we could not access. Digital copies suit most agents. Keep them with the tenancy file. The next due date is the one the report states, not a reminder we invent in a marketing email. If you want a diary note from us as well, ask for it.",
    "Send the postcode, house or flat or commercial, how many boards you know about, and the last EICR date if you have it. Manchester terraces, Stockport semis, Bolton mills, Salford apartments and Wigan estates are all attended from the same base. Call 07517806082. The contact form takes the same facts.",
    "We will not issue a satisfactory report to win a renewal, we will not test only the kitchen and call it the installation, and we will not quote a made-up call-out fee. The EICR is price on application after scope. Landlord gas safety, where it applies, is a different trade: " + GAS,
])

ESSAYS["eicr-certificate"] = join([
    "When someone asks for an EICR certificate they almost always need the Electrical Installation Condition Report itself: the document that says whether the fixed wiring was satisfactory and what was observed. iComply produces that report for rented homes and commercial units from " + NAP + ". We do not sell a certificate detached from the inspection. If the wiring has not been inspected and tested, there is nothing honest to issue.",
    "The word certificate is loose in adverts. The report is the record. It includes the outcome, the observation codes, the circuit details the inspector could verify, and the recommended next inspection date. Agents file it. Tenants may need a copy under the current English guidance. This page does not restate the statute as if it were complete. Check current GOV.UK wording for who must receive what, and by when.",
    "A satisfactory outcome means the inspector did not find danger that makes the installation unsatisfactory, within the limits of what was accessible. It does not mean every C3 recommendation was ignored as irrelevant, and it does not mean the portable appliances were tested. PAT is a different visit. An unsatisfactory outcome means C1, C2 or FI items need action. The certificate you wanted is then a report that tells you to do more work, which is the correct document, not a failure of the visit.",
    "People also ask us to replace a missing report when a previous electrician cannot be contacted. We cannot recreate someone else’s test results. We can carry out a new inspection and issue our own report. Limitations are stated if furniture, locked rooms or a live shop floor stop a full look. A report with limitations is more useful than a confident page about circuits nobody opened.",
    "Coding is explained when we hand the file over. C1 is dealt with as danger present, often before we leave if the isolation is agreed. C2 is potentially dangerous and needs remedial work on a defined timescale, which your own duty and the report will frame. C3 is improvement. FI means we could not conclude without opening something further. We do not collapse those into a single traffic light.",
    "Remedials and any retest are quoted after the report, price on application. The retest should identify what changed. We do not overwrite history. Landlords who need the electrical report and a gas record in the same week can ask for both to be scheduled. They remain different scopes. " + GAS,
    "For commercial units the interval is not automatically five years. It follows the use, the previous report and the insurer or client rule you are actually under. Tell us if a shop, office or warehouse is the building. A flat above a shop can be two conversations: the dwelling and the commercial installation. We will not force one report to cover both if the supplies are separate.",
    "What to send: postcode, dwelling or commercial, number of consumer units if you know it, and whether a previous PDF exists. A photo of the board helps the quote. It does not replace the visit. Access arrangements matter more than the brand of the board.",
    "Greater Manchester coverage includes Manchester, Stockport, Bolton, Salford and Wigan, arranged from Stockport. Call 07517806082 or use the contact form and say you need the EICR report, not a PAT sticker and not a visual only. The electrical service page is the wider hub. The EICR guide sits beside this certificate page so the wording stays honest.",
    "The price is on application. We do not offer a same-day certificate without the test, and we do not add a scheme logo the report is not entitled to carry. You receive the report for the installation we inspected, with the limitations written in it.",
])

ESSAYS["access-control-near-me"] = join([
    "Access control near me is the local request for readers, tokens, door release and the programming that decides who can come in. iComply surveys and fits that from " + NAP + ", across Greater Manchester including Manchester, Stockport, Bolton, Salford and Wigan. The quote is price on application after the doors are known. A maglock on a pedestrian door is not the same job as a car park barrier, and we do not price them as one item.",
    "The near-me part matters because doors fail on a Tuesday and the person with the software password may have left the company. We attend to look at the controller, the reader, the lock and the power supply, not to sell a new system by default. Paxton and other common UK brands are what we find. If we can work on the software, we say so. If the tokens are an unknown format, we say that too, before anyone promises a clone of every fob.",
    "A new installation starts with the door, not the reader colour. Leaf, frame, escape, fire-alarm release and how the door is used at 8am decide the lock: maglock, strike, or a different piece of hardware. We do not put a maglock on a fire escape and forget the release. The fire alarm interface, the green break-glass and the mechanical override are part of the survey when the door is on an escape route.",
    "Programming is the part that keeps the system honest after we leave. Joiners, leavers, lost cards and time zones should be something your administrator can do, or a visit you book. We document the naming of doors and cards. We do not leave a single master fob as the only way in. If you want us to hold programming, that is a scoped arrangement, price on application, not an invisible subscription.",
    "Blocks and offices differ. A block may need trades buttons, resident fobs and a door-entry panel that releases the same lock. An office may need departments and a door that locks on a schedule. We write the rules down. Door entry is a related trade with its own page. CCTV on the same entrance can be surveyed in the same walk and is quoted separately.",
    "Barriers are the job we refuse to hide inside an access quote. A rising arm, loops and safety edges are a lane. Manchester and Stockport have plenty of private yards where someone asks for a fob that also opens the boom. The boom is specified on its own. The reader that gives the signal can sit on the access quote. The two scopes stay visible.",
    "Repairs quote from symptoms: door will not release, door will not lock, reader dead, tokens declined, or a fire-alarm release that has been left link-fitted. We will not bridge a fire release to force a lock on. If a previous contractor did that, we record it and price the reinstatement. Life safety is not a convenience setting.",
    "Cabling in occupied buildings is agreed before we drill. Listed doors, glass, and landlord consent in a multi-let block change the method. If consent is missing, we wait. A neat reader on a door we were not allowed to touch becomes your problem the next morning.",
    "Send the postcode, the number of doors, the brand if you know it, and whether the trouble is a new entrance or a fault. Photos of the reader and the power supply help. Call 07517806082. The access control service page is the hub. This page is the local way in.",
    "Prices stay on application. We do not publish a per-door fee, we do not claim to be the manufacturer’s exclusive agent, and we do not describe a barrier lane as a door. The engineer is local to the Stockport base. The survey still has to see the door.",
])

ESSAYS["gas-safety-certificate"] = join([
    "A landlord gas safety record — often called a CP12 — is the written result of a safety check on the gas appliances, flues and relevant pipework that fall under the landlord’s duty. " + GAS + " iComply arranges the visit from " + NAP + ". The price is on application after the address and the appliance list are known. This page does not show a registration number or a badge.",
    "The check is not automatically a full manufacturer service, and it is not an EICR. The engineer records what was inspected, the results, and defects or follow-up. That paper, under its formal name, is what belongs in the tenancy file. Industry shorthand still says CP12. Use the name that is on the document when you hand it to an agent or a tenant.",
    "Where the duty applies, landlords plan a check at least every 12 months. New tenancies need the record in the file on the timetable set out in current HSE and GOV.UK guidance. This page does not invent a day-count. Read that guidance, and give tenants a copy within the period it states. Keep proof of when the copy was given. HMO licence conditions in Greater Manchester can ask for more. Check the council as well.",
    "What is usually in scope, when those items are the landlord’s responsibility, includes boilers, gas fires and cookers that the duty covers, the flues and ventilation that serve them, and the relevant installation pipework. Appliances the tenant owns can sit outside the landlord duty. Tell us what is whose. We will not guess from a photograph of a kitchen.",
    "Defects are written, not smoothed over. An appliance that fails the check needs the action the record describes. Do not treat next year’s visit as the remedy. Follow-up work is quoted separately, still carried out by Gas Safe registered engineers, still price on application. iComply does not publish a repair menu and does not claim the registration for itself.",
    "No-gas properties are a different question. If there is no supply and no gas appliance, the paperwork position may differ. Confirm that against official guidance rather than asking a marketing page to decide. We would rather record no gas, where that is true, than invent an appliance list.",
    "Agents with several addresses can send a schedule: postcode, access, meter location, and last record date. Each property is still its own check. Combining the gas record with an EICR on one diary day is possible when you ask and when capacity allows. The trades stay separate. The electrical report does not make the gas record valid, or the reverse.",
    "Smell of gas is not this booking. Open windows if it is safe, avoid switches and flames, turn the supply off at the meter if you can do so safely, leave if advised, and call the National Gas Emergency Service on 0800 111 999. Contact us after the supply is safe if you need a recorded check or a repair visit.",
    "Greater Manchester towns including Manchester, Stockport, Bolton, Salford and Wigan are arranged from Offerton. Call 07517806082 and say gas safety record, the town, and how many appliances you believe are in the landlord’s scope. The contact form takes the same note. The gas systems service page is the wider hub.",
    "We will not put a Gas Safe logo on this page, we will not quote a made-up certificate fee, and we will not describe iComply as Gas Safe registered. The sentence that matters is the true one: " + GAS + " The price of the visit is on application.",
])

ESSAYS["door-entry"] = join([
    "Door entry is the panel, the handsets or monitors, and the lock release that lets a resident or a receptionist decide who comes through. iComply surveys and replaces that equipment from " + NAP + ". The quote is price on application after we know the panel, the handset count and the cable. An audio-only system in a small block is not the same job as an IP video system across several cores.",
    "The survey starts at the entrance and ends at the lock. We need to see whether the panel still has a clean release into the lock, whether the cable is a historic multi-core or a network, and whether residents’ handsets are the original brand. Mixing a new panel onto unknown cable without testing is how a block loses every flat’s call on the same afternoon. We test before we commit to a swap.",
    "Fire and escape sit beside the convenience. If the entrance is also an escape door, the release has to fail the way the fire strategy requires. We do not add a fancy monitor and leave a fire-alarm release disconnected. Access control hardware on that door, including a maglock, is included in the survey when it is part of the same release. It is still described as what it is.",
    "Video, audio and IP are choices after the building, not before. A four-flat conversion may not need a networked monitor in every room. A large block may be unable to live with a buzzing audio handset and no visitor image. We say which we are quoting. Handset count drives labour and hardware. A guess from the street is not a count.",
    "Brands we meet include Fermax and other common UK and European panels, plus the orphan systems where the manufacturer support has thinned. We keep a sound system when parts exist. We specify replacement when the panel cannot be expanded or the cable cannot carry what you now want. That recommendation is written. It is not a preference for whatever is on the van.",
    "Resident programming, trades buttons and timed release are set up and then shown to the person who will live with them. We do not leave the engineer’s code as the only administrator. If a managing agent wants a written list of which core calls which flats, we produce it as part of handover when the survey found the labelling was wrong.",
    "Cabling in occupied blocks is the slow part. Lift bookings, residents who work nights, and a panel mounted in a way that water has already entered all change the visit. Those facts belong in the quote. Price on application means we would rather ask than arrive and abort.",
    "A barrier or a car park gate sometimes shares a button with the door panel. The signal can be scoped. The barrier lane itself is not a door-entry job. Loops, booms and safety edges stay on the barrier page. We will say if the panel can give a clean contact, and we will not pretend that contact is the whole lane.",
    "Send the postcode, the number of flats or the handset count if you know it, and a photo of the outdoor panel. Manchester cores, Salford quayside blocks, Stockport conversions, Bolton mills and Wigan town flats are typical calls. Phone 07517806082. The door entry service page is the hub.",
    "No per-handset price is published here. No claim is made that iComply manufactures the panel. The work is surveyed, then quoted on application, then fitted with a handover the agent can file.",
])

ESSAYS["electrical-service"] = join([
    "Electrical work from this service covers condition reports, remedial repairs and installation: consumer units, circuits, and the fixed equipment that belongs with them. iComply attends from " + NAP + ". Quotes are price on application after the board, the property and the access are known. An EICR is the report. A rewire or a board change is a different instruction. This page does not merge them into one fee.",
    "Landlords usually arrive here because a report is due or a report has come back unsatisfactory. The EICR guide sets out the codes and the English private-rented context. On this service page the practical point is the same: we inspect and test the fixed installation, we write what we find, and we quote remedials separately. We do not sell a satisfactory outcome. PAT testing of plug-in appliances is not included unless you ask for that separate visit.",
    "Installations we quote include consumer unit replacements where the report or the condition justifies them, additional circuits, lighting and power in refurbishments, and making safe after damage. Cable routes, isolation and certification follow BS 7671 practice. EV charging is quoted only when the supply can take it, under the rules that apply to that circuit. We do not add a charger because the consumer unit looks new.",
    "Commercial boards are a different pace. Shops and workshops may not be able to lose a circuit at noon. Tell us. Limitations go in writing if we cannot isolate. A visual glance at a closed lid is not an EICR and we will not call it one.",
    "Greater Manchester housing stock changes the job. Manchester terraces, Stockport semis, Bolton converted mills, Salford apartments and Wigan estate houses do not share a board location or an earthing history. The survey looks. The district name does not tell us the size of the tails.",
    "Documentation is the certificate or report for the work that was actually done, plus a note of limitations. Agents get digital copies. The next date is the one written on the report, not a marketing interval. If you need the gas record in the same week, ask. " + GAS,
    "Send the postcode, the type of building, what you think you need (report, repair, or install), and a photo of the consumer unit if you have one. Call 07517806082. The price remains on application. We do not publish a call-out ladder on this page.",
    "We will not rewire a house on a guess from a phone call, we will not code a board we have not opened, and we will not add accreditation logos the paperwork is not entitled to. The electrical job is scoped, then priced, then recorded.",
    "Related guides are the EICR page and the EICR certificate page. Use those when the only thing you need is the condition report. Use this service page when the work might include installation or remedial repairs as well. Both stay price on application.",
    "The workshop address is " + NAP + ". Travel across Greater Manchester is part of the quote. If the site is occupied, say who will give access. A missed board is a missed section of the report, and we would rather move the appointment than invent the readings.",
])

ESSAYS["landlord-compliance"] = join([
    "Landlord compliance, on this job, is the practical pack of records a rented home in England is often expected to hold: the electrical report, the gas safety record where gas applies, and the fire documents that the building type and any licence actually require. iComply arranges those visits from " + NAP + ". Each element is scoped. The price is on application. This page is not a single invented bundle price and it is not legal advice.",
    "The electrical piece is an EICR on the fixed installation, with codes written in the report and remedials quoted as found. It does not include the tenant’s portable appliances unless you add that visit. The gas piece, where there are landlord gas appliances or flues, is a landlord gas safety record. " + GAS + " If the property has no gas, say so. We will not invent a boiler.",
    "Fire is the part that varies most. A single let may need smoke and carbon monoxide alarms sited and recorded. A shared house or a block may need a fire risk assessment and a real fire alarm system. We will not sell a pair of domestic detectors as if they were BS 5839 for a three-storey HMO. If the layout needs a designed system, that quote sits on the fire alarm pages.",
    "Licence conditions in Greater Manchester councils can ask for evidence that goes beyond the national minimum. We do not grant the licence and we do not promise an enforcement outcome. We supply the certificates and the practical work you book. You and the council own the application. Bring the licence draft or the last conditions if you want the visit list to follow them.",
    "An agent instructing several properties should send a sheet: address, tenancy dates, last EICR, last gas record, storeys, and whether anyone shares facilities. We turn that into a diary, not into one blended fee. Access is still per house. A locked room is a limitation, written down.",
    "Order of work matters. An unsatisfactory EICR may need remedials before you rely on the file. A failed gas appliance may need to be isolated by the engineer who is qualified to do it, then repaired under a separate quote. Fire actions may be management items you do yourselves, or physical work you ask us to price. We keep those lists separate so you can see what was our visit and what is still yours.",
    "Paperwork is digital unless you ask otherwise. File names should carry the address and the date. We do not replace your compliance database. We give you the documents the database is supposed to store. The next due dates are the ones on the reports.",
    "Stockport is the base, so Manchester, Salford, Bolton, Wigan and the rest of the published Greater Manchester set are ordinary travel. The quote includes that travel rather than adding a fuel line afterwards. Call 07517806082 with the first address and we will tell you what is missing from the brief.",
    "Related job pages cover the EICR, the gas safety record and HMO compliance in more detail. Use them when you already know which document is late. Use this page when the whole file is the question. Either way the price is on application after scope.",
    "We will not badge the company with accreditations it does not hold, we will not call a visual walk an EICR, and we will not call iComply Gas Safe registered. The engineers’ qualifications for each trade are the ones that trade requires. The coordinating office is " + NAP + ".",
])

ESSAYS["hmo-compliance"] = join([
    "HMO compliance support is the evidence a shared house often needs before and during a licence: the fire risk assessment where one is required, the electrical report, the gas safety record if gas is present, and the fire precautions the layout and the council conditions actually name. iComply arranges that work from " + NAP + ". We do not issue the HMO licence. The council does. The price of our visits is on application unless a separate published list on the job page already states a figure for a defined typical house. This copy does not add a new price.",
    "A typical shared house and a building that only looks like one are different. Storeys, basements, inner rooms, and whether the stair is protected change the fire conversation. We read the last licence conditions if you have them. We do not assume every council in Greater Manchester uses the same checklist. Manchester, Salford, Stockport, Bolton and Wigan each publish their own landlord pages. Bring the PDF you were sent.",
    "Fire precautions may be management (closers that are missing, extinguishers that are not the right answer, signage) or physical (detection, emergency lighting, doors). Detection for a shared stair is often a designed system, not a domestic alarm on a landing. We will say which we think you are being asked for, and we will quote the install separately from the assessment. The assessment does not include the install unless the quote says it does.",
    "The EICR covers the fixed wiring of the house as scoped. Bedsits with their own boards need those boards on the brief. A single board in the hall may not be the whole installation. Limitations are written if a room is locked. The gas record, where the duty applies, is carried out by Gas Safe registered engineers. iComply is not Gas Safe registered. No-gas houses should be described as no-gas so nobody expects a record that cannot exist.",
    "Occupancy and amenity questions — bathrooms, kitchens, room sizes — are mostly your measurements and the council’s standard. We do not redraw the plans as a planning consultant. If a condition asks for a certificate we do provide, it goes on the list. If it asks for something we do not do, we say so rather than stretching the visit.",
    "The file you should end up with is boring and complete: reports named by address, a note of remedials still open, and dates. We do not store your licence on your behalf. We do not talk to the council as your agent unless you have asked us, in writing, to explain a technical point about work we did.",
    "Remedials after an unsatisfactory electrical report, a failed detector, or a door that does not close are each price on application. Doing them is what makes the file true. Leaving them as a PDF action with no follow-up is how licences stall. We can quote the work. We cannot mark it done if it is not done.",
    "Access in a lived-in shared house is the hardest part. Tell every occupant. We need the boards, the stair, the kitchens and the rooms the brief names. A room we cannot enter is a gap, not a pass. Night working is only booked when the house can actually let us in.",
    "Call 07517806082 with the postcode, the number of storeys, the number of households, and whether gas is present. The HMO job page is the operational hub. The fire risk assessment and EICR pages carry those single subjects. This note is the joined-up brief, still quoted on application.",
    "The office is " + NAP + ". We will not promise a licence, we will not invent council conditions, and we will not use the word registered for gas about iComply itself. The work you book is the work that gets a document.",
])

ESSAYS["fire-risk-assessment"] = join([
    "A fire risk assessment is the suitable and sufficient look at a building’s fire hazards, the people at risk, and the measures that are already there, ending in a prioritised action list. iComply writes that assessment for shared houses, blocks, offices and other workplaces that need one, from " + NAP + ". It is not a fire alarm certificate and it is not an HMO licence. The price of the assessment is confirmed in writing for the building. This pack does not invent a new fee.",
    "The Fire Safety Order is the usual reason a workplace or a building’s common parts needs an assessment. A single private dwelling is a different legal picture from a shared house or a block stair. We ask what the building is before we book. If you are a landlord of one house let to one household, do not assume this page applies. If people share a stair, assume you should ask.",
    "The visit records the premises, the occupants, ignition sources, the escape routes, doors, detection, lighting, signage and management. Actions are prioritised. An action is not automatically a contract for us to do the work. Alarm upgrades, door repairs and emergency lighting are quoted afterwards if you want them done. The assessment stays useful even when you appoint someone else for the remedials.",
    "We do not copy last year’s PDF and change the date. If you have a previous assessment we will read it, because actions that were never closed are part of the new picture. If you have none, we start from the building. Plans help. A sketch of storeys is enough when plans do not exist. Locked plant rooms and locked flats are limitations, written into the document.",
    "Higher-risk or larger buildings take longer and are confirmed before the visit. Sleeping risk, basements, inner rooms and complicated escape all change the time on site. We would rather say that in the quote than discover it in the hallway. Price on application where the building is outside a simple brief. No casual figure is added here.",
    "The responsible person still owns the actions. We can explain them in ordinary language. We cannot accept the legal duty for you. Staff training, drills and the weekly alarm test, where an alarm exists, remain management tasks unless a separate visit is booked to demonstrate them.",
    "Greater Manchester shared houses and small commercial units are the ordinary diary from Stockport, including Manchester, Salford, Bolton, Stockport and Wigan. UK mainland coverage exists for this assessment type where the service is published. Northern Ireland, the Scottish Highlands and Islands, the Isle of Man and the Channel Islands are not the list we work from on these pages.",
    "Send the address, use, storeys, whether anyone sleeps there, and the last assessment date. Call 07517806082. The fire risk assessment service page is the hub. Installation of any missing detection is the fire alarm installation guide, quoted on its own.",
    "You receive a document with findings and actions, not a guarantee of an enforcement outcome. If the council or the fire service has already written to you, attach that letter. The assessment should answer the building, and it should not pretend a letter does not exist.",
    "We will not badge the assessment as a licence, we will not hide actions to make a sale of alarm equipment, and we will not price the remedials inside a vague all-in number on this data pack. The assessment is the written judgement. The works are the next quote, price on application.",
])

ESSAYS["car-park-barrier"] = join([
    "A car park barrier job is the rising arm, the cabinet, the induction loops or other safety devices, and the way the lane decides to open. iComply surveys that lane from " + NAP + ". The price is on application after the boom length, the island and the safety equipment are known. This is not a door maglock and it is not priced as one.",
    "CAME is the manufacturer we fit when a new lane needs that cabinet. Existing brands stay when the survey shows the springs, edges and controls are still the right kit. We do not scrap a sound barrier to put our preferred badge on it. We also do not mix a new boom onto a tired cabinet without writing down why.",
    "Safety is the part that gets missed in a photograph. Edges, photocells and loops stop the arm for a vehicle or a person. We will not bridge a loop out to make a barrier close faster for a demo. Manual release is identified and demonstrated to the person who will use it when the power fails. If nobody knows where the release key is, that is a finding, not a footnote.",
    "What opens the lane is a separate sentence in the quote: fob, keypad, intercom, telephone entry, or a signal from an access control reader. The reader can be Paxton or another system you already own. The barrier manufacturer and the access manufacturer are not the same trade. Both can be required. Both are listed.",
    "Boom length is measured. A five-metre opening is not a guess from the kerb. Articulated arms exist because some car parks turn before a straight arm can rise. We say if a straight arm will not clear. Lighting, signage and the slope of the lane affect how the arm behaves in wind. Those are survey notes.",
    "Damage visits — a struck boom, a lane stuck up, a lane stuck down — are scoped from what we find. Parts follow the cabinet we actually open. There is no published call-out fee. Price on application. If the lane has to stay open or closed for safety while parts are ordered, we agree that with you and we do not leave it ambiguous.",
    "Private land only. We do not install barriers on the public highway, and we do not control a council street. Yards, resident car parks and staff lanes in Manchester, Stockport, Bolton, Salford and Wigan are the ordinary request. If the lane is shared with a neighbour, say who can authorise the work.",
    "A pedestrian gate beside the lane is out of scope unless you put it on the brief. It may be a maglock or a door-entry release. It gets its own lines. People often ask for one fob to do both. The fob can, when the wiring allows. The hardware is still two jobs.",
    "Send a photo of the cabinet label, the approximate opening width, and what currently makes the arm move. Call 07517806082. The barriers service page and the CAME page carry the brand context. This job page is the lane itself.",
    "We will not quote a barrier from a boom colour, we will not disable a safety device to finish early, and we will not describe iComply as the manufacturer. The survey comes first. The written price comes after. Until then the work is price on application.",
])

ESSAYS["maglock"] = join([
    "Maglock installation is the electromagnetic lock, its power supply, the request-to-exit, and the release that lets the door open in a fire or on a break-glass. iComply fits and repairs that hardware from " + NAP + ". It is a pedestrian-door job. It is not a car park barrier. The quote is price on application after the door and the escape condition are known.",
    "The survey looks at the leaf and the frame, whether the door is on an escape route, and what already releases it. A maglock that stays locked when the fire alarm sounds is a defect, not a feature. We identify the fire-alarm output, the green break-glass and the mechanical override. If a previous fit bridged the release out, we record it and price the correction. We will not copy that bridge.",
    "Holding force and bracket choice follow the door. A light internal door and a heavy external leaf are not the same magnet. Armature alignment is why some locks buzz and never quite hold. We set that on site. We do not leave a magnet hanging on a timber screw into a soft frame and call it finished.",
    "Access control is the brain that decides who may drop the magnet: reader, token, keypad or a signal from a door-entry panel. That brain can already exist. We can add the lock to it when the power and the cable allow, or quote the reader as well. The two are itemised. Door entry has its own page because the panel and the handsets are a different pile of equipment.",
    "Power supplies and battery backup are part of a honest quote. A maglock that releases on power failure is a different safety behaviour from one that holds. The fire strategy chooses. We do not choose the convenient one. The handover note says which way the door fails.",
    "Occupied offices and blocks need agreement before we drill. Fire doors must still be fire doors after the ironmongery changes. If a door certification is sensitive to the hardware, we stop and say so rather than drilling first. Price on application includes that caution.",
    "Fault visits are: lock will not hold, lock will not release, break-glass left operated, or a power supply that has failed. Tell us which. A photo of the magnet and the PSU saves time. Parts are quoted when the fault is not a simple reinstatement of a connection.",
    "Greater Manchester doors in Manchester, Salford, Stockport, Bolton and Wigan are attended from Stockport. Send the postcode and whether the door is a fire exit. Call 07517806082. The access control page is the parent subject. The barrier page is the wrong parent.",
    "No per-door catalogue price is published. No claim is made that the maglock replaces a proper latch on every door. The written scope names the door, the release and the reader if there is one.",
    "The office is " + NAP + ". The price stays on application until the leaf, the frame and the fire release are known. That is the whole of the promise, and it is enough to book the survey.",
])


def deepen(intent: str, checks: list[str], avoid: str) -> str:
    lines = " ".join(checks)
    return join([
        (
            f"On a {intent} quote the useful detail is the checklist, not a slogan. "
            f"We confirm each of these before the written price: {lines} "
            f"If one of those items is unknown, the quote says it is unknown. "
            f"We do not smooth the gap so the email looks finished."
        ),
        (
            f"What we will not do on {intent} is {avoid} "
            f"The written scope names the address, the person instructing iComply, and the limit of the visit. "
            f"Travel from {NAP} is inside the price on application, not a line added after you have said yes. "
            f"Call 07517806082 or use the contact form. Ask for {intent} and give the postcode first. "
            f"Attach photos of the existing equipment when you have them. "
            f"We confirm the visit in writing before anyone is dispatched from Offerton. "
            f"If the building is occupied, name the person who can open the doors. "
            f"The figure remains price on application after that scope. "
            f"We would rather ask one more question than attend and find the brief was a different job. "
            f"Say if a previous visit left anything isolated, bridged or unfinished. "
            f"Related hubs stay linked from the page that renders this copy. "
            f"Until the scope is agreed there is no fee to publish and no fee to invent."
        ),
    ])


DEEP = {
    "fire-alarm-installation": (
        "Fire alarm installation",
        [
            "which part of BS 5839 applies.",
            "whether anyone sleeps in the building.",
            "the panel brand already on the wall.",
            "cause and effect to doors, plant or vents.",
            "a zone chart the responsible person can read.",
            "battery standby rather than a green lamp alone.",
            "variations written in English.",
            "a logbook start, not a promise that we do the weekly test.",
        ],
        "certify a system we have not proved, or describe a handful of domestic detectors as a full commercial system.",
    ),
    "fire-alarm-call-out": (
        "Fire alarm call-out",
        [
            "the panel text before travel.",
            "whether sounders are still running.",
            "who may authorise isolation.",
            "a written note for the logbook.",
            "parts fitted only when they are the cause.",
            "a separate quote if the fault needs a return.",
            "no silencing without a record.",
            "999 first if there is an actual fire.",
        ],
        "publish a call-out tariff or leave a zone isolated overnight without a note.",
    ),
    "aov-installation": (
        "AOV installation",
        [
            "the fire strategy and the free area it names.",
            "roof, window or louvre, stated separately.",
            "rain-sensor logic that loses to the fire input.",
            "battery open, not only a mains test.",
            "the fire-alarm contact actually witnessed.",
            "actuator stroke measured against the vent.",
            "a key location the responsible person can find.",
            "weather and access equipment included when the survey shows them.",
        ],
        "price a smoke vent as a domestic window or bridge out a fire input to force a demonstration.",
    ),
    "cctv-installation": (
        "CCTV installation",
        [
            "the views that need identification rather than a wide overview.",
            "night lighting, not just megapixels.",
            "retention days at the quality you asked for.",
            "named users and no factory passwords.",
            "cable routes and recorder location.",
            "signage where the site needs it.",
            "a test export while we are still there.",
            "no camera aimed into a neighbour’s window.",
        ],
        "sell a camera count as a bundle price or promise a monitoring centre we do not provide.",
    ),
    "cctv-near-me": (
        "CCTV near me",
        [
            "whether you need an install, a repair or a check.",
            "the postcode before any diary claim.",
            "who instructs us, landlord or tenant.",
            "the recorder brand if one already exists.",
            "a photo of the fault if something has failed.",
            "occupation limits on when we can attend.",
            "local travel from Stockport inside the POA quote.",
            "a separate line if access control is on the same door.",
        ],
        "promise same-day attendance for every town or scrap a working camera to enlarge the job.",
    ),
    "eicr": (
        "EICR",
        [
            "dwelling or commercial, stated up front.",
            "how many boards you know about.",
            "access to the rooms the circuits serve.",
            "C1, C2, C3 and FI written as themselves.",
            "limitations where we could not look.",
            "remedials quoted after the report, not instead of it.",
            "no PAT items slipped in unless you asked.",
            "the next date taken from the report.",
        ],
        "issue a satisfactory outcome for circuits we did not see, or publish a per-circuit fee.",
    ),
    "eicr-certificate": (
        "EICR certificate",
        [
            "that the document required is the condition report itself.",
            "no recreation of another electrician’s lost results.",
            "limitations written rather than implied.",
            "codes explained at handover.",
            "a retest only for what was repaired.",
            "commercial intervals not assumed to be five years.",
            "flat and shop supplies kept separate when they are.",
            "digital copy named with the address and date.",
        ],
        "offer a same-day certificate without the inspection.",
    ),
    "access-control-near-me": (
        "Access control near me",
        [
            "door count and whether any leaf is an escape.",
            "existing brand and whether the software password is known.",
            "maglock, strike or other hardware named in the quote.",
            "fire-alarm release and break-glass still in circuit.",
            "token format checked before anyone promises a clone.",
            "barrier lanes kept off this quote.",
            "administrator handover so one master fob is not the system.",
            "consent to drill in a multi-let block.",
        ],
        "bridge a fire release to keep a door locked, or price a barrier as a door.",
    ),
    "gas-safety-certificate": (
        "Gas safety certificate",
        [
            "appliance list and who owns each appliance.",
            "flues and ventilation in the agreed scope.",
            "the formal record name, not only the CP12 shorthand.",
            "defects written with the action the record describes.",
            "no-gas properties described as no-gas.",
            "0800 111 999 first if you smell gas.",
            "engineers who are Gas Safe registered doing the check.",
            "no registration number printed for iComply.",
        ],
        "describe iComply as Gas Safe registered or publish a certificate fee.",
    ),
    "door-entry": (
        "Door entry",
        [
            "panel photo and handset count.",
            "cable type, multi-core or network.",
            "lock release tested before a panel swap.",
            "escape behaviour of the entrance door.",
            "audio, video or IP named in the quote.",
            "resident programming shown to the agent.",
            "barrier contacts described separately from the lane.",
            "occupied-block access agreed before drilling.",
        ],
        "swap a panel onto unknown cable and hope every flat still rings.",
    ),
    "electrical-service": (
        "Electrical",
        [
            "report, repair or install, named as the instruction.",
            "board photos if you have them.",
            "isolation limits on a trading floor.",
            "BS 7671 certification for the work actually done.",
            "EV charging only when the supply can take it.",
            "PAT excluded unless requested.",
            "unsatisfactory findings quoted as remedials, not edited away.",
            "gas records kept as a different trade.",
        ],
        "call a visual glance an EICR or publish a call-out ladder.",
    ),
    "landlord-compliance": (
        "Landlord compliance",
        [
            "which documents are actually late.",
            "gas or no gas.",
            "single let or shared stair.",
            "licence conditions attached if the council has sent them.",
            "EICR and gas record kept as separate scopes.",
            "fire system versus domestic alarms decided from the layout.",
            "open remedials listed, not buried.",
            "one address per visit even on a portfolio sheet.",
        ],
        "sell one invented bundle price or grant a licence.",
    ),
    "hmo-compliance": (
        "HMO compliance",
        [
            "storeys, households and whether facilities are shared.",
            "the council PDF, not a guess at local rules.",
            "sleeping risk and inner rooms.",
            "boards in bedsits called out on the brief.",
            "gas present or explicitly absent.",
            "assessment and install quoted apart.",
            "locked rooms recorded as gaps.",
            "the licence application left with you and the council.",
        ],
        "promise an HMO licence or mark remedial work done when it is not.",
    ),
    "fire-risk-assessment": (
        "Fire risk assessment",
        [
            "use of the building and whether anyone sleeps there.",
            "storeys and any basement.",
            "previous assessment and open actions.",
            "escape routes walked, not described from a plan alone.",
            "detection and lighting recorded as found.",
            "actions prioritised and not silently converted into a works contract.",
            "enforcement letters attached if you have them.",
            "limitations where rooms were locked.",
        ],
        "re-date last year’s PDF or hide actions to sell alarm equipment.",
    ),
    "car-park-barrier": (
        "Car park barrier",
        [
            "opening width measured, not guessed.",
            "cabinet label photographed.",
            "loops, edges and photocells named.",
            "what currently opens the lane.",
            "manual release key located.",
            "straight or articulated arm decided on site.",
            "pedestrian gate kept off the lane quote unless listed.",
            "private land confirmed.",
        ],
        "bridge a safety loop or quote the lane from a boom colour.",
    ),
    "maglock": (
        "Maglock installation",
        [
            "whether the door is a fire exit.",
            "fail-locked or fail-unlocked chosen from the strategy.",
            "break-glass and fire-alarm release in circuit.",
            "bracket and holding force matched to the leaf.",
            "power supply and battery included.",
            "existing reader or a new one, itemised.",
            "fire door hardware left compatible.",
            "no link fitted across a release.",
        ],
        "treat a maglock as a car park barrier or drill a fire door without saying so.",
    ),
}
for _key, (_intent, _checks, _avoid) in DEEP.items():
    ESSAYS[_key] = ESSAYS[_key] + "\n\n" + deepen(_intent, _checks, _avoid)


def require_essay(key: str) -> str:
    text = ESSAYS[key]
    count = words(text)
    if count < 800:
        raise SystemExit(f"{key} hub essay is {count} words")
    return text


def page_record(path: str, intent: str, essay_key: str, family: str, town: str = "", who: str = "landlords and managing agents") -> dict:
    essay = require_essay(essay_key)
    if town:
        tname = TOWNS[town]["name"]
        body = local_block(intent, town) + "\n\n" + essay
        title = f"{intent} in {tname} | iComply Property Services"
        where = tname
        place = tname
    else:
        body = essay
        title = f"{intent} | iComply Property Services"
        where = ""
        place = ""
    if words(body) < 800:
        raise SystemExit(f"{path} body is {words(body)} words")
    what = intent
    return {
        "path": path,
        "title": title,
        "description": fit_desc(what, where, who),
        "body": body,
        "word_count": words(body),
        "images": images_for(family, intent, place),
        "faqs": faqs(intent, place),
        "poa": True,
        "nap": NAP,
    }


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    for key in ESSAYS:
        require_essay(key)

    keyword_meta = {
        "fire-alarm-installation": ("Fire alarm installation", "fire-alarms", "landlords and commercial occupiers"),
        "aov-installation": ("AOV installation", "aov-air-handling", "block managers and landlords"),
        "cctv-installation": ("CCTV installation", "cctv", "businesses and landlords"),
        "cctv-near-me": ("CCTV near me", "cctv", "businesses and landlords"),
        "eicr": ("EICR", "electrical", "landlords and letting agents"),
        "eicr-certificate": ("EICR certificate", "electrical", "landlords and letting agents"),
        "access-control-near-me": ("Access control near me", "access-control", "offices, blocks and landlords"),
        "gas-safety-certificate": ("Gas safety certificate", "gas-systems", "landlords and letting agents"),
    }
    keyword_hubs = {
        "_note": "CLOSE-Q4 keyword hub copy. Existing keyword template reads body, faq, meta_desc and seo_title from keywords.json. This file is the full pack, including image paths for the Q2 image slot. fire-alarm-installation is NOT written back into keywords.json because PR #113 already edits that record.",
        "hubs": {},
    }
    for slug, (intent, family, who) in keyword_meta.items():
        keyword_hubs["hubs"][slug] = page_record(f"/pages/keywords/{slug}", intent, slug if slug in ESSAYS else slug, family, "", who)
        keyword_hubs["hubs"][slug]["essay_key"] = slug

    job_meta = {
        "fire-alarm-call-out": ("Fire alarm call-out", "fire-alarm-call-out", "fire-alarms", "sites with a panel fault"),
        "hmo-compliance": ("HMO compliance", "hmo-compliance", "fra", "HMO landlords and agents"),
        "eicr": ("EICR", "eicr", "electrical", "landlords and letting agents"),
        "gas-safety-cp12": ("Gas safety certificate", "gas-safety-certificate", "gas-systems", "landlords and letting agents"),
    }
    job_hubs = {
        "_note": "CLOSE-Q4 job hubs. fire-alarm-call-out body is also written into website/data/fire-alarms-lane.json content.body, which the job renderer already prints. eicr and gas-safety-cp12 are image lists for hubs the word count already clears. hmo-compliance prose is here for the HMO content slot; hmo-jobs.php is left unchanged.",
        "hubs": {},
    }
    for slug, (intent, essay_key, family, who) in job_meta.items():
        rec = page_record(f"/pages/jobs/{slug}", intent, essay_key, family, "", who)
        rec["images_only"] = slug in {"eicr", "gas-safety-cp12"}
        if rec["images_only"]:
            rec = {
                "path": rec["path"],
                "images_only": True,
                "images": rec["images"],
                "_note": "CLOSE-Q4 images only. These hubs already clear 800 words. Drop these three src values into the image partial. Do not replace the existing page body, FAQ, or meta.",
            }
        job_hubs["hubs"][slug] = rec

    towns5 = ["manchester", "stockport", "bolton", "salford", "wigan"]
    town_pages = {}

    def add_town(kind: str, slug: str, essay_key: str, intent: str, family: str, who: str, town_list: list[str]) -> None:
        for town in town_list:
            if kind == "keyword":
                path = f"/pages/keywords/{slug}/{town}"
            elif kind == "job":
                path = f"/pages/jobs/{slug}/{town}"
            else:
                path = f"/pages/{slug}/{town}"
            key = f"{kind}/{slug}/{town}"
            town_pages[key] = page_record(path, intent, essay_key, family, town, who)
            town_pages[key]["kind"] = kind
            town_pages[key]["slug"] = slug
            town_pages[key]["town"] = town

    for slug, (intent, family, who) in keyword_meta.items():
        add_town("keyword", slug, slug, intent, family, who, towns5)
    add_town("service", "cctv", "cctv-installation", "CCTV", "cctv", "businesses and landlords", towns5 + ["oldham"])
    add_town("service", "electrical", "electrical-service", "Electrical", "electrical", "landlords and commercial occupiers", towns5)
    add_town("service", "gas-systems", "gas-safety-certificate", "Gas systems", "gas-systems", "landlords and letting agents", towns5)
    add_town("service", "access-control", "access-control-near-me", "Access control", "access-control", "offices, blocks and landlords", towns5)
    add_town("service", "door-entry", "door-entry", "Door entry", "door-entry", "blocks and managing agents", towns5)
    add_town("service", "fire-alarms", "fire-alarm-installation", "Fire alarms", "fire-alarms", "landlords and commercial occupiers", towns5)
    add_town("job", "eicr", "eicr", "EICR", "electrical", "landlords and letting agents", towns5)
    add_town("job", "fire-alarms", "fire-alarm-installation", "Fire alarms", "fire-alarms", "landlords and commercial occupiers", towns5)
    add_town("job", "gas-safety-cp12", "gas-safety-certificate", "Gas safety certificate", "gas-systems", "landlords and letting agents", towns5)
    add_town("job", "landlord-compliance", "landlord-compliance", "Landlord compliance", "electrical", "landlords and letting agents", towns5)
    add_town("job", "fire-risk-assessment", "fire-risk-assessment", "Fire risk assessment", "fra", "dutyholders and landlords", towns5)
    add_town("job", "fra", "fire-risk-assessment", "Fire risk assessment", "fra", "dutyholders and landlords", towns5)
    add_town("job", "car-park-barrier", "car-park-barrier", "Car park barrier", "barriers", "sites with a private lane", towns5)
    add_town("job", "came-gard-gt4", "car-park-barrier", "CAME barrier", "barriers", "sites with a private lane", towns5)
    add_town("job", "maglock-installation", "maglock", "Maglock installation", "access-control", "offices and blocks", towns5)
    add_town("job", "fire-alarm-call-out", "fire-alarm-call-out", "Fire alarm call-out", "fire-alarms", "sites with a panel fault", towns5)
    add_town("job", "hmo-compliance", "hmo-compliance", "HMO compliance", "fra", "HMO landlords and agents", towns5)

    town_doc = {
        "_note": "CLOSE-Q1 and CLOSE-Q2 data for thin ×town pages. Drop body into the prose slot, images into the three image partials, faqs into the FAQ partial. Do not edit town-matrix.js in this pack. Keys are kind/slug/town. Unique local opening uses the town stock and districts; the essay is the same subject written for the hub.",
        "towns": towns5,
        "flaw_samples": [
            "/pages/jobs/eicr/manchester",
            "/pages/cctv/stockport",
            "/pages/cctv/oldham",
            "/pages/fire-alarms/bolton",
            "/pages/access-control/stockport",
            "/pages/keywords/gas-safety-certificate/stockport",
            "/pages/keywords/access-control-near-me/manchester",
            "/pages/jobs/gas-safety-cp12/wigan",
        ],
        "pages": town_pages,
    }

    area_faqs = {
        "_note": "CLOSE-Q5 FAQ copy for WT’s FAQ partial and FAQPage JSON-LD. This file is not the partial. Manchester entry is images only (CLOSE-Q2).",
        "homepage": {
            "path": "/",
            "faqs": [
                {
                    "q": "What does iComply quote for?",
                    "a": "Electrical reports, fire alarms, emergency lighting, CCTV, access control, door entry and related compliance visits. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered. The price is on application.",
                },
                {
                    "q": "Where are you based?",
                    "a": f"The office is {NAP}. Greater Manchester is the core diary, including Manchester, Stockport, Bolton, Salford and Wigan.",
                },
                {
                    "q": "How do I request a quote?",
                    "a": "Call 07517806082 or use the contact form with the address and the job. We reply with a scope. Request a quote — POA. No fee is printed as a starting price on this homepage pack.",
                },
                {
                    "q": "Do you cover a town outside Greater Manchester?",
                    "a": "Only where that service is already published for the town. If the live URL redirects, use the hub instead of a town page that is not indexed.",
                },
            ],
        },
        "stockport": {
            "path": "/pages/areas/stockport",
            "faqs": faqs("Property compliance", "Stockport"),
        },
        "bolton": {
            "path": "/pages/areas/bolton",
            "faqs": faqs("Property compliance", "Bolton"),
        },
        "wigan": {
            "path": "/pages/areas/wigan",
            "faqs": faqs("Property compliance", "Wigan"),
        },
        "manchester_images": {
            "path": "/pages/areas/manchester",
            "_note": "CLOSE-Q2 images only. FAQ markup stays with WT.",
            "images": images_for("fire-alarms", "Property compliance", "Manchester")[:1]
            + images_for("electrical", "Property compliance", "Manchester")[:1]
            + images_for("cctv", "Property compliance", "Manchester")[:1],
        },
    }

    aov = page_record("/pages/aov", "AOV installation", "aov-installation", "aov-air-handling", "", "block managers and landlords")
    aov["_note"] = "CLOSE-Q6 body, FAQ and image paths for /pages/aov. Shell markup stays with WT. Do not point this pack at /pages/services/aov."
    aov["h1"] = "AOV and smoke control"

    def dump(name: str, payload: dict) -> None:
        path = OUT / name
        path.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
        print(f"wrote {path.relative_to(ROOT)}")

    dump("keyword-hubs.json", keyword_hubs)
    dump("job-hubs.json", job_hubs)
    dump("town-prose.json", town_doc)
    dump("area-faqs.json", area_faqs)
    dump("aov-hub.json", aov)

    # Wire keyword records PR #113 does not own.
    keywords_path = DATA / "keywords.json"
    keywords = json.loads(keywords_path.read_text(encoding="utf-8"))
    skip = {"fire-alarm-installation"}
    for slug, rec in keyword_hubs["hubs"].items():
        if slug in skip:
            continue
        row = keywords[slug]
        row["body"] = rec["body"]
        row["seo_title"] = rec["title"]
        row["meta_desc"] = rec["description"]
        row["images"] = rec["images"]
        if slug == "cctv-near-me":
            row["intro"] = row["intro"].replace(
                "fixed-price quotes without long-distance call-out padding",
                "quotes that are price on application, with no published call-out fee",
            )
            row["focus_points"] = [
                point.replace("with transparent fixed pricing", ", price on application")
                for point in row.get("focus_points") or []
            ]
        if slug == "access-control-near-me":
            row["intro"] = row["intro"].replace(
                "fixed-price work without inflated travel",
                "a quote that is price on application",
            )
            row["focus_points"] = [
                point.replace("with fixed pricing", ", price on application")
                for point in row.get("focus_points") or []
            ]
        if slug == "gas-safety-certificate":
            row["intro"] = (
                "A landlord gas safety certificate records that appliances and flues have been checked. "
                + GAS
                + " iComply arranges the visit from Stockport for single homes or portfolios. "
                "The price is on application. This page does not show a registration number."
            )
            row["focus_points"] = [
                "Checks carried out by Gas Safe registered engineers",
                "iComply is not Gas Safe registered",
                "Clear defect notes, with remedials quoted separately",
                "Portfolio booking from Stockport, price on application",
            ]
        if "h1" not in row or not str(row.get("h1") or "").strip():
            row["h1"] = rec["title"].split("|")[0].strip()
        # Keep existing FAQ. Lengthen only if fewer than 3.
        faq = row.get("faq") or []
        if len(faq) < 3:
            row["faq"] = [[item["q"], item["a"]] for item in rec["faqs"]]
        keywords[slug] = row
    keywords_path.write_text(json.dumps(keywords, ensure_ascii=False, indent=4) + "\n", encoding="utf-8")
    print("updated keywords.json for", ", ".join(s for s in keyword_meta if s not in skip))

    lane_path = DATA / "fire-alarms-lane.json"
    lane = json.loads(lane_path.read_text(encoding="utf-8"))
    call = job_hubs["hubs"]["fire-alarm-call-out"]
    found = False
    for job in lane.get("jobs", []):
        if job.get("slug") == "fire-alarm-call-out":
            content = job.setdefault("content", {})
            content["body"] = call["body"]
            content["meta_desc"] = call["description"]
            content["seo_title"] = call["title"]
            content["images"] = call["images"]
            found = True
            break
    if not found:
        raise SystemExit("fire-alarm-call-out missing from lane json")
    lane_path.write_text(json.dumps(lane, ensure_ascii=False, indent=4) + "\n", encoding="utf-8")
    print("updated fire-alarms-lane.json fire-alarm-call-out")

    # Sanity
    bad = []
    for slug, rec in keyword_hubs["hubs"].items():
        if rec["word_count"] < 800 or len(rec["images"]) < 3 or len(rec["faqs"]) < 3:
            bad.append(slug)
        if not (140 <= len(rec["description"]) <= 160):
            bad.append("desc " + slug)
        if "subcontract" in rec["body"].lower() or "iComply is Gas Safe registered" in rec["body"]:
            bad.append("wording " + slug)
    for key, rec in town_pages.items():
        if rec["word_count"] < 800 or len(rec["images"]) < 3:
            bad.append(key)
    if bad:
        raise SystemExit("sanity failed: " + ", ".join(bad[:20]))
    print(f"town pages {len(town_pages)}")
    print("sample desc", keyword_hubs["hubs"]["eicr"]["description"], len(keyword_hubs["hubs"]["eicr"]["description"]))
    print("eicr words", keyword_hubs["hubs"]["eicr"]["word_count"])
    print("call-out words", job_hubs["hubs"]["fire-alarm-call-out"]["word_count"])


if __name__ == "__main__":
    main()
