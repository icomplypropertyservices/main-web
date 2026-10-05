#!/usr/bin/env python3
"""Build TOP 5000 town data and the 76 nationwide P0 keyword hubs.

Reads the locked town CSV and P0 slug list. Writes:
  website/data/uk-top5000-towns.json
  website/data/nationwide-3line-p0.json
  website/data/nationwide-3line-p0-keywords.json
  website/pages/keywords/{slug}.php stubs for slugs that do not already have one

Does not rewrite area allowlists. The 10 GM-core places missing from TOP 5000
stay on the dual FULL LOCAL list and are recorded only as a note.
"""
from __future__ import annotations

import csv
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
DATA = ROOT / "website" / "data"
PAGES = ROOT / "website" / "pages" / "keywords"
UPLOADS = Path("/home/ubuntu/.cursor/projects/workspace/uploads")

CSV_PATH = UPLOADS / "UK-TOP5000-TOWNS-BY-POP-2026-10-05_f980.csv"
P0_PATH = UPLOADS / "NATIONWIDE-3LINE-KEYWORDS-P0_e44c.txt"

GM_NOT_IN_TOP = [
    "cadishead",
    "chorlton",
    "heywood",
    "pendlebury",
    "romiley",
    "tameside",
    "trafford",
    "withington",
    "worsley",
    "wythenshawe",
]

NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"
BRAND = " | iComply Property Services"
SHORT_BRAND = " — iComply"

AOV = [
    "aov-engineer",
    "aov-engineer-near-me",
    "aov-engineers",
    "aov-engineers-near-me",
    "aov-installation",
    "aov-installation-near-me",
    "aov-installer",
    "aov-installer-near-me",
    "aov-installers",
    "aov-installers-near-me",
    "commercial-aov-installation",
    "commercial-aov-installer",
    "emergency-aov-installer",
    "emergency-aov-repair",
    "smoke-control-engineer",
    "smoke-control-engineers",
    "smoke-control-installation",
    "smoke-control-installation-near-me",
    "smoke-control-installer",
    "smoke-control-installers",
    "smoke-vent-installation",
    "smoke-vent-installer",
    "smoke-ventilation-installation",
    "smoke-ventilation-installer",
]
BARRIER = [
    "automatic-barrier-installer",
    "barrier-engineer",
    "barrier-engineers",
    "barrier-installation",
    "barrier-installation-near-me",
    "barrier-installer",
    "barrier-installer-near-me",
    "barrier-installers",
    "barrier-installers-near-me",
    "barrier-maintenance",
    "barrier-maintenance-near-me",
    "barrier-repair",
    "barrier-repair-near-me",
    "barrier-service",
    "barrier-service-near-me",
    "car-park-barrier-installation",
    "car-park-barrier-installer",
    "commercial-barrier-installation",
    "commercial-barrier-installer",
    "emergency-barrier-installer",
    "emergency-barrier-repair",
    "parking-barrier-installation",
    "parking-barrier-installer",
    "rising-arm-barrier-installation",
    "rising-arm-barrier-installer",
    "vehicle-barrier-installation",
    "vehicle-barrier-installer",
]
FIRE_SERVICE = {
    "emergency-lighting-installation": "emergency-lighting",
    "fire-door-installation": "fire-doors",
    "fire-door-installer": "fire-doors",
    "fire-extinguisher-service": "fire-extinguishers",
    "fire-protection-company": "fire-alarms",
    "fire-risk-assessment": "fire-risk-assessments",
    "fire-stopping-installer": "fire-stopping",
    "fire-suppression-installer": "fire-suppression",
    "sprinkler-system-installer": "sprinkler-systems",
}

SITES = [
    "a converted mill with one stair",
    "a podium car park under shops",
    "a purpose-built block of flats",
    "a school with a long corridor",
    "a warehouse with a mezzanine",
    "a care home with night staff",
    "an HMO over a shop",
    "a clinic with a protected lobby",
    "a multi-let office on a business park",
    "a retail unit with a rear yard",
    "a hotel stair that also serves a plant room",
    "a light-industrial unit with a roller shutter",
    "a landlord turnaround between tenancies",
    "a managing-agent block with a paper logbook",
    "a facilities floor that cannot close at lunchtime",
    "a new shell before the tenant fit-out",
    "a refurbished stair with an existing vent that sticks",
    "a basement car park with a low soffit",
    "a surface car park beside a clinic",
    "a private lane that is not a public highway",
    "a loading bay shared by two occupiers",
    "a residents' compound with a pedestrian side gate",
    "a staff car park under a canopy",
    "a visitor lane in front of a reception",
    "a bin-store approach that must stay clear of the boom",
    "a rising-arm lane measured kerb to kerb",
    "a photocell line that currently false-triggers",
    "a loop that was cut and never drawn",
    "a cabinet footing that has settled",
    "a boom that clips a lighting column",
    "a fire-alarm interface that was never proved",
    "a conventional panel in a small office",
    "an addressable loop in a larger block",
    "a category the fire risk assessment has already named",
    "a weekly test the site manager is unsure how to run",
    "a zone chart that no longer matches the building",
    "a call point by a final exit",
    "a sounder that does not reach a plant room",
    "a domestic alarm rule that is not a BS 5839 design",
    "a commercial category that does need a BS 5839 design",
    "emergency lighting on an escape route",
    "a fire door that does not self-close",
    "an extinguisher service the insurer has asked to see",
    "a compartment line where services pass through",
    "a suppression system the fire strategy already names",
    "a sprinkler valve set the site has not had explained",
    "a smoke shaft with a vent that only opens on mains power",
    "an actuator that has seized after a flat battery",
    "a louvre packed with debris",
    "a window AOV on an occupied landing",
    "a roof vent that needs safe access before anyone quotes",
    "a control panel with a standing fault",
    "a cause-and-effect line that has never been witnessed",
    "paperwork the previous contractor did not leave",
    "a freeholder who wants one written scope",
    "a tenant who must not be told a catalogue price",
    "a night visit because the lane cannot close by day",
    "a daytime survey while the building stays in use",
    "a return visit once parts are identified",
    "a quote that includes travel from Stockport",
    "a neighbour town on the same instruction",
    "a postcode the form did not quite match to a town name",
    "a dutyholder who keeps the logbook, not the installer",
    "a design that follows the fire strategy already in the building",
    "a private boom, not a highway barrier",
    "a smoke-control survey before any vent is ordered",
    "a commissioning slot booked only after the scope is signed",
    "a defect list instead of a pretend certificate",
    "a parent service page the enquiry can start from",
    "related keyword guides for the same trade",
    "no BAFE badge and no NSI badge on this page",
    "a price that stays on application until the building is known",
    "the Stockport workshop address when a location is needed",
    "Greater Manchester area hubs where the town is on that list",
    "TOP 5000 coverage for this nationwide keyword",
    "a page that does not invent a day rate",
]


def words(text: str) -> int:
    return len(re.findall(r"[A-Za-z0-9'’—-]+", text))


def display_name(slug: str) -> str:
    acronyms = {"aov": "AOV", "uk": "UK", "bs": "BS"}
    parts = []
    for part in slug.split("-"):
        parts.append(acronyms.get(part, part.capitalize()))
    name = " ".join(parts)
    return name.replace("Near Me", "Near Me")


def line_of(slug: str) -> str:
    if slug in AOV:
        return "aov"
    if slug in BARRIER:
        return "barrier"
    return "fire"


def service_of(slug: str) -> str:
    if slug in AOV:
        return "aov-air-handling"
    if slug in BARRIER:
        return "barriers"
    return FIRE_SERVICE.get(slug, "fire-alarms")


def intent_of(slug: str) -> str:
    if "near-me" in slug:
        return "near-me"
    if slug.startswith("emergency-") or "emergency-" in slug:
        return "emergency"
    if "repair" in slug:
        return "repair"
    if "maintenance" in slug:
        return "maintenance"
    if "service" in slug and "services" not in slug:
        return "service"
    if "installer" in slug or slug.endswith("-engineer") or slug.endswith("-engineers"):
        return "installer"
    if "installation" in slug:
        return "installation"
    if "company" in slug:
        return "company"
    if "assessment" in slug:
        return "assessment"
    return "installer"


def title_for(name: str, town: str | None = None) -> str:
    lead = name if not town else f"{name} in {town}"
    full = lead + BRAND
    if len(full) <= 60:
        return full
    short = lead + SHORT_BRAND
    return short


def nbytes(text: str) -> int:
    """PHP strlen() counts UTF-8 bytes. An em dash is one character and three bytes."""
    return len(text.encode("utf-8"))


def fit_meta(text: str) -> str:
    text = re.sub(r"\s+", " ", text).strip()
    # Keep the em dash when the description is already inside 160 bytes.
    # If it is only the dash that pushes past 160, an ASCII hyphen saves two bytes
    # and leaves the CTA intact.
    if nbytes(text) > 160 and "—" in text:
        text = text.replace("—", "-", 1)
    if nbytes(text) > 160:
        cut = text.encode("utf-8")[:160].decode("utf-8", errors="ignore")
        if " " in cut:
            cut = cut.rsplit(" ", 1)[0]
        text = cut.rstrip(" .,;:—-")
        if not text.endswith("POA"):
            extra = " Request a quote - POA."
            if nbytes(text) + nbytes(extra) <= 160:
                text += extra
    pads = [
        " Scope is written first.",
        " No catalogue price.",
        " Call 07517806082.",
        " Scope first.",
        " POA only.",
    ]
    for pad in pads:
        if nbytes(text) >= 140 and len(text) >= 140:
            break
        if nbytes(text) + nbytes(pad) <= 160:
            text += pad
    size = nbytes(text)
    if not (140 <= size <= 160 and len(text) >= 140):
        raise SystemExit(f"meta out of range bytes={size} chars={len(text)}: {text}")
    return text


def meta_for(name: str, place: str) -> str:
    audience = "landlords, agents and commercial sites"
    text = (
        f"{name} in {place} for {audience}. "
        f"Arranged from Stockport. Request a quote — POA."
    )
    if nbytes(text) > 160:
        text = f"{name} in {place} for landlords and agents. Request a quote — POA."
    if nbytes(text) > 160:
        text = f"{name} in {place}. Scoped from Stockport. Request a quote — POA."
    return fit_meta(text)


STANDARDS = {
    "aov-air-handling": (
        "Smoke control is judged against the fire strategy for that building, with BS 9991 and BS EN 12101 as the usual reference points. "
        "An automatic opening vent is not assumed to work because a magnet dropped out. The fire-alarm interface, the battery, the actuator and the free area are recorded."
    ),
    "barriers": (
        "Vehicle and parking barriers on this page are for private land. A public highway is a different job and is not quoted from this guide. "
        "Lane width, duty cycle, safety devices and who owns the land are agreed before a boom is specified. iComply is a CAME partner for the Gard range. Other brands already on a site can be serviced without a false partnership claim."
    ),
    "fire-alarms": (
        "Fire alarm design, installation and commissioning follow BS 5839 for the category the building actually needs. "
        "A domestic smoke-alarm rule is not the same conversation as a BS 5839 system for a shared house or a commercial premises. The page says which one the enquiry is in."
    ),
    "emergency-lighting": (
        "Emergency lighting is specified against BS 5266 and the escape routes the fire strategy already names. "
        "A failed fitting is a maintenance visit. A missing design is a survey. The two are not given one invented price."
    ),
    "fire-doors": (
        "Fire door work follows BS 8214 and the rating of the door that is actually in the opening. "
        "Gaps, seals, closers and glazing are recorded. A door is not called a fire door because it looks heavy."
    ),
    "fire-extinguishers": (
        "Extinguisher servicing follows BS 5306 practice for the types on site. "
        "The visit lists what was seen. It does not invent a scheme badge."
    ),
    "fire-risk-assessments": (
        "A fire risk assessment records the premises as found and the actions that follow. "
        "It is not a certificate that the building is safe forever, and it is not a substitute for the fire alarm or the fire doors."
    ),
    "fire-stopping": (
        "Fire-stopping is specified to the compartment line and a tested detail for the services that pass through it. "
        "A photo of pink foam is not a specification."
    ),
    "fire-suppression": (
        "Suppression is quoted only for the system the fire strategy names. "
        "The reference standard is the one that applies to that system. This page does not borrow a registration from another trade."
    ),
    "sprinkler-systems": (
        "Sprinkler work follows the British Standard that matches the system, commonly BS 9251 for residential and domestic scopes or BS EN 12845 where that is the design basis. "
        "The valve set, the water supply and the cause-and-effect with the fire alarm are part of the scope, not a footnote."
    ),
}

ANGLES = {
    "installer": "The installer is the person who attends and leaves a record, not a call centre that only takes a postcode.",
    "installation": "Installation means supply, fix and commissioning against a written scope, not a carton left in the riser.",
    "near-me": "Near me means the town page for the place you searched, with travel from Stockport included in the quote rather than hidden.",
    "emergency": "An emergency visit makes the immediate fault safe or open, then books the lasting repair. It is not a promise of an unscoped overnight rebuild.",
    "repair": "Repair starts from the fault that is there. Parts are identified before they are ordered. The quote stays on application.",
    "maintenance": "Maintenance is a planned visit with a result the dutyholder can file. It is not a rolling contract invented on this page.",
    "service": "Service means the agreed inspection and the notes that come back. Defects are listed. They are not quietly absorbed into a fixed fee.",
    "company": "A company enquiry is for the trade as a whole. The first reply names which service page the work sits on.",
    "assessment": "An assessment is a written record of the premises. It recommends work. It does not pretend the work is already done.",
}


def essay(slug: str, name: str, place: str, county: str, region: str, pop: str, near: str, miles: str, site: str) -> str:
    service = service_of(slug)
    intent = intent_of(slug)
    standard = STANDARDS[service]
    angle = ANGLES[intent_of(slug)]
    parent = {
        "aov-air-handling": "AOV and smoke control",
        "barriers": "vehicle and parking barriers",
        "fire-alarms": "fire alarms",
        "emergency-lighting": "emergency lighting",
        "fire-doors": "fire doors",
        "fire-extinguishers": "fire extinguishers",
        "fire-risk-assessments": "fire risk assessments",
        "fire-stopping": "fire-stopping",
        "fire-suppression": "fire suppression",
        "sprinkler-systems": "sprinkler systems",
    }[service]
    paras = [
        (
            f"{name} is arranged from the Stockport workshop for {place}. "
            f"The enquiry this page is written for is {site}. {angle} "
            f"{standard} The quote is price on application after the scope is written down. "
            f"There is no catalogue fee and no day rate on this page."
        ),
        (
            f"People searching {name} usually need a survey, not a brochure. "
            f"Send the postcode, the building use and who keeps the logbook. "
            f"iComply confirms what will be looked at and what is outside the visit. "
            f"Travel to {place} is part of that quote. The workshop is about {miles} from the pin used for this town, "
            f"at {NAP}. Nearby places on the same town list include {near}."
        ),
        (
            f"{place} sits in {county}, {region}. Published population context for this page is {pop}. "
            f"That figure is context for travel and building mix. It is not a claim that every street has already been surveyed. "
            f"{name} follows the building in front of the engineer. A {site} is scoped on its own drawings and its own access, "
            f"not by swapping a town name into a national script and calling it local."
        ),
        (
            f"The first conversation covers who instructs the work. Landlords, managing agents and facilities managers "
            f"are the usual clients for {parent}. A commercial tenant can ask for {name} when the lease says they must, "
            f"and the reply will say whether the freeholder still has to approve the scope. "
            f"Occupied floors are booked around access. A planned daytime visit, an out-of-hours attendance where the site cannot close, "
            f"and a return visit once parts are known are different instructions. Each one is quoted on application."
        ),
        (
            f"On site, the visit for {name} starts with what is already installed. "
            f"Labels, zones, battery dates, previous certificates and obvious damage are noted before any new work is promised. "
            f"If the existing equipment can be kept, the scope says so. If it cannot, the reason is written in plain language. "
            f"iComply does not claim BAFE or NSI badges. Competent people carry out the visit for {parent}. "
            f"Where a specialist ticket is required, that ticket is named in the quote. The customer wording on this page does not call that arrangement a hidden extra workforce."
        ),
        (
            f"Paperwork is part of {name}, not an optional extra invented after the invoice. "
            f"The dutyholder should be able to file the note, the test sheet or the commissioning record with the building. "
            f"Zone charts, device lists and user notes are updated when the work changed them. "
            f"A missing historical certificate is reported as missing. This page does not offer to paper over an unfinished job. "
            f"For {site}, the record names the limitation: access, a locked riser, a vent that could not be reached, or a lane that could not be measured."
        ),
        (
            f"Pricing stays on application because {name} changes with access, parts and the standard that applies. "
            f"A single stair is not the same visit as a multi-core block. A straight boom is not the same visit as a low-headroom arm. "
            f"A BS 5839 category review is not the same visit as a domestic alarm check. "
            f"Publishing one figure would be a guess. The written quote names the {parent} scope before a date is fixed. "
            f"Call 07517806082 or use the contact form. Say whether the building is occupied."
        ),
        (
            f"Coverage for this keyword is the UK mainland TOP 5000 towns by population, England, Scotland and Wales. "
            f"{place} is on that list when this is a town page, or the guide is the nationwide hub when it is not. "
            f"Greater Manchester area hubs stay in place for local services, including the ten marketing places that are not in the TOP 5000 population cut. "
            f"Those ten are not dropped from the local area list. They are simply not the driver for this nationwide keyword family. "
            f"Manufacturer pages stay on the Greater Manchester core. This keyword does not widen that manufacturer matrix."
        ),
        (
            f"Internal links from {name} go to the parent {parent} service, to related keyword guides in the same P0 set, "
            f"and to the town page when the place is published. "
            f"AOV and smoke-control enquiries can also open the AOV town pages. Barrier enquiries can open the barrier town pages. "
            f"Fire alarm enquiries open the fire alarm service. Doors, extinguishers, emergency lighting, stopping, suppression and sprinklers stay on their own service pages "
            f"when that is the work, so a fire-protection company enquiry is not forced into a single product."
        ),
        (
            f"What {name} does not include is as important as what it does. "
            f"It does not publish a fee. It does not include a fake accreditation. "
            f"It does not include Northern Ireland or the islands excluded from the mainland town build. "
            f"It does not include air-handling plant that sits under a different service. "
            f"It does not include place-locked barrier pages that were written only for one North West town. "
            f"If the instruction is really a different trade, the reply says so and points at the service that fits."
        ),
        (
            f"A practical sequence for {site} runs like this. "
            f"First the address in {place} and a short description of the fault or the project. "
            f"Second a written scope for {name} that a dutyholder can accept or question. "
            f"Third the visit, with access agreed. Fourth the record, left with the instructing client. "
            f"If parts have to be ordered, the fourth step waits. The page does not pretend a part is on the van when it has not been identified. "
            f"If the survey shows the work is larger than the first note, the quote is revised before anyone starts. "
            f"That revision is still price on application. It is not a surprise catalogue line."
        ),
        (
            f"Readers comparing {name} with a national advert should look for the town, the standard and the address. "
            f"This guide names {place}, {county}, and the Stockport workshop at {NAP}. "
            f"Population context is {pop}. Neighbours used for travel context are {near}. "
            f"The same keyword in another TOP 5000 town has its own page so the place is not only a swapped noun in one template. "
            f"The hub remains the parent. The town page adds the place. Both are price on application."
        ),
        (
            f"Handover for {name} is a conversation with the person who will live with the result. "
            f"For smoke control, that is how to spot a standing fault and who to call. "
            f"For a barrier, that is how the lane opens if the power fails or a fire signal is proved, where the design requires it. "
            f"For a fire alarm, that is the weekly test and where the logbook lives. "
            f"For a fire door, that is what a closer and a seal are for. "
            f"For emergency lighting, that is the duration test the site is expected to know about. "
            f"For an assessment, that is which actions are theirs. "
            f"The note is written for that audience. It is not a generic certificate pasted with a town name."
        ),
        (
            f"If you are instructing {name} for {site}, include photos of the panel, the vent, the boom or the door before asking for a date. "
            f"Photos do not replace the survey. They stop the first visit being only a discovery that could have been an email. "
            f"Tell us about asbestos notices, fragile roofs, night-only access and any fire strategy you already have. "
            f"iComply will say if another service has to be quoted beside {parent}. "
            f"Linked guides on this page are there so you can open the sibling keyword without searching again. "
            f"The contact path is the form, the phone number 07517806082, or WhatsApp from the hub. "
            f"Every one of those paths is asked to keep the reply as price on application."
        ),
    ]
    # Intent-specific extra so near-me, repair and emergency are not the same essay.
    extras = {
        "near-me": (
            f"The near-me wording is the town page, not a promise that an engineer lives in {place}. "
            f"The base is Stockport. Attendance in {place} is scheduled from that base once the scope for {name} is accepted. "
            f"If a closer town on the TOP 5000 list is a better description of the site, use that town's page. "
            f"The work does not change because the search said near me. The place on the paperwork does."
        ),
        "emergency": (
            f"Emergency {name} means the building has a fault that should not wait for a routine diary slot. "
            f"The first attendance aims to make safe, to open a lane, or to silence a genuine unwanted alarm condition within the agreed scope. "
            f"A full new system is a different instruction and is quoted separately, still on application. "
            f"Say if people are still in the building and if the fire service has already attended."
        ),
        "repair": (
            f"Repair for {name} is fault-finding plus the agreed fix. "
            f"The page does not list a price per actuator, per boom or per detector. "
            f"If the repair would cost more than replacement, that comparison is made in the quote after the survey, not as a teaser figure here."
        ),
        "maintenance": (
            f"Maintenance for {name} is booked as a round or a single site. "
            f"The interval comes from the fire strategy, the manufacturer note or the insurer's request. "
            f"This page does not invent a universal twice-yearly rule and then sell it as law."
        ),
        "installation": (
            f"A new {name} is designed before it is fitted. "
            f"Existing cables, structure and fire strategy are surveyed. "
            f"Commissioning is part of the instruction when the quote says it is. It is not assumed from a headline."
        ),
        "installer": (
            f"Asking for an installer, an engineer or a team is the same first step. "
            f"You get a written scope for {name} from iComply Property Services. "
            f"You do not get a list of unnamed subcontractors as the product."
        ),
        "service": (
            f"A service visit for {name} has a checklist agreed in the quote. "
            f"Items outside the checklist are reported, not quietly done and billed as if they were in the original line."
        ),
        "company": (
            f"A fire-protection company search often mixes alarms, doors, extinguishers and stopping. "
            f"This guide starts with {parent} and links the other services. "
            f"You can ask for more than one trade. Each trade is scoped. None is given a made-up package price."
        ),
        "assessment": (
            f"The assessment visit needs access to the places the risk actually sits: plant, stairs, stores and bedrooms used as offices. "
            f"A walk-by of the front door is not {name}."
        ),
    }
    paras.append(extras[intent])
    paras.append(
        f"Use {name} when the instruction matches {parent}. "
        f"If it does not, ask and the reply will point to the service that does. "
        f"The preferred address when a location is printed is {NAP}. "
        f"Phone 07517806082. The quote remains price on application."
    )
    return "\n\n".join(paras)


def build_keywords(slugs: list[str]) -> dict:
    by_line: dict[str, list[str]] = {"aov": [], "barrier": [], "fire": []}
    for slug in slugs:
        by_line[line_of(slug)].append(slug)
    out = {}
    for index, slug in enumerate(slugs):
        name = display_name(slug)
        line = line_of(slug)
        siblings = by_line[line]
        related = siblings[(siblings.index(slug) + 1) % len(siblings)]
        site = SITES[index % len(SITES)]
        intro = (
            f"{name} from iComply Property Services is a UK-mainland guide, not a Greater Manchester-only page. "
            f"It is written for {site}. "
            f"{ANGLES[intent_of(slug)]} "
            f"Quotes are price on application. The workshop, when a location is shown, is {NAP}."
        )
        body = essay(
            slug,
            name,
            "the UK mainland",
            "England, Scotland and Wales",
            "the UK",
            "the TOP 5000 towns by population",
            "London, Birmingham, Leeds, Glasgow and Cardiff",
            "the distance quoted from Stockport",
            site,
        )
        if words(body) < 800:
            raise SystemExit(f"{slug} body has {words(body)} words")
        meta = meta_for(name, "the UK mainland")
        meta_bytes = nbytes(meta)
        if not (140 <= meta_bytes <= 160):
            raise SystemExit(f"{slug} meta bytes {meta_bytes}: {meta}")
        title = title_for(name)
        out[slug] = {
            "name": name,
            "service": service_of(slug),
            "related": related,
            "h1": name,
            "seo_title": title,
            "intro": intro,
            "body": body,
            "meta_desc": meta,
            "focus_points": [
                f"Written scope for {name} before anyone attends",
                "Price on application — no catalogue fee",
                STANDARDS[service_of(slug)].split(". ")[0] + ".",
                f"Record left with the instructing client. Workshop: {NAP}.",
            ],
            "faq": [
                [
                    f"What does {name} include?",
                    f"{name} includes the survey and the work named in the written scope for {service_of(slug).replace('-', ' ')}. "
                    "It does not publish a fee. Travel from Stockport is part of the quote.",
                ],
                [
                    f"How is {name} priced?",
                    "Price on application after the building, the access and the standard are known. This page does not publish a fee.",
                ],
                [
                    f"Do you claim BAFE or NSI for {name}?",
                    "No. iComply does not claim BAFE or NSI badges. The visit follows the standard named for this trade and leaves a record.",
                ],
                [
                    f"Where is {name} covered?",
                    "UK mainland towns in the TOP 5000 population list, from the Stockport workshop. "
                    f"The address is {NAP}.",
                ],
            ],
            "seo_keywords": f"{name}, {name} UK, {service_of(slug).replace('-', ' ')}, price on application",
            "line": line,
        }
    intros = [row["intro"] for row in out.values()]
    if len(set(intros)) != len(intros):
        raise SystemExit("duplicate intros")
    metas = [row["meta_desc"] for row in out.values()]
    if len(set(metas)) != len(metas):
        raise SystemExit("duplicate meta descriptions")
    titles = [row["seo_title"] for row in out.values()]
    if len(set(titles)) != len(titles):
        raise SystemExit("duplicate titles")
    return out


def image_pool(service: str) -> list[str]:
    lanes = {
        "aov-air-handling": sorted((ROOT / "website/assets/images/lanes/aov-air-handling").glob("*.jpg")),
        "barriers": sorted((ROOT / "website/assets/images/lanes/barriers").glob("*.jpg")),
    }
    if service in lanes and len(lanes[service]) >= 3:
        return [str(path.relative_to(ROOT / "website")).replace("\\", "/") for path in lanes[service]]
    fallback = {
        "fire-alarms": [
            "assets/images/services/fire-alarms.jpg",
            "assets/images/services/fire-alarms-photo.jpg",
            "assets/images/services/fire-compartmentation.jpg",
        ],
        "emergency-lighting": [
            "assets/images/services/emergency-lighting.jpg",
            "assets/images/services/emergency-lighting-photo.jpg",
            "assets/images/services/fire-alarms.jpg",
        ],
        "fire-doors": [
            "assets/images/services/fire-doors.jpg",
            "assets/images/services/fire-compartmentation.jpg",
            "assets/images/services/fire-stopping.jpg",
        ],
        "fire-extinguishers": [
            "assets/images/services/fire-extinguishers.jpg",
            "assets/images/services/fire-signage.jpg",
            "assets/images/services/fire-alarms.jpg",
        ],
        "fire-risk-assessments": [
            "assets/images/services/fire-risk-assessments.jpg",
            "assets/images/services/fire-risk-assessments-photo.jpg",
            "assets/images/services/fire-doors.jpg",
        ],
        "fire-stopping": [
            "assets/images/services/fire-stopping.jpg",
            "assets/images/services/fire-compartmentation.jpg",
            "assets/images/services/fire-doors.jpg",
        ],
        "fire-suppression": [
            "assets/images/services/fire-suppression.jpg",
            "assets/images/services/kitchen-fire-suppression.jpg",
            "assets/images/services/sprinkler-systems.jpg",
        ],
        "sprinkler-systems": [
            "assets/images/services/sprinkler-systems.jpg",
            "assets/images/services/fire-suppression.jpg",
            "assets/images/services/fire-alarms.jpg",
        ],
    }
    paths = fallback[service]
    for rel in paths:
        if not (ROOT / "website" / rel).is_file():
            raise SystemExit(f"missing image {rel}")
    return ["/" + rel if not rel.startswith("/") else rel for rel in paths]


def write_towns() -> int:
    towns = []
    with CSV_PATH.open(newline="", encoding="utf-8") as handle:
        for row in csv.DictReader(handle):
            towns.append(
                {
                    "rank": int(row["rank"]),
                    "name": row["name"],
                    "slug": row["slug"],
                    "population": int(row["population"]),
                    "nation": row["nation"],
                    "county": row["county"],
                    "region": row["region"],
                    "lat": float(row["lat"]),
                    "lng": float(row["lng"]),
                    "source": row["source"],
                }
            )
    if len(towns) != 5000:
        raise SystemExit(f"town count {len(towns)}")
    slugs = [town["slug"] for town in towns]
    if len(set(slugs)) != 5000:
        raise SystemExit("duplicate town slugs")
    missing = [slug for slug in GM_NOT_IN_TOP if slug in set(slugs)]
    if missing:
        raise SystemExit(f"GM-only places leaked into TOP5000: {missing}")
    payload = {
        "meta": {
            "lock": "UK-TOP5000-TOWNS-BY-POP-2026-10-05",
            "towns": 5000,
            "order": "population descending",
            "mainland": "England, Scotland and Wales",
            "min_population": towns[-1]["population"],
            "max_population": towns[0]["population"],
            "gm_core_not_in_this_list": GM_NOT_IN_TOP,
            "note": "Dual 269 FULL LOCAL stays on its own allowlist. These 10 GM-core places are intentionally absent here and must stay on that local list.",
        },
        "towns": [{key: town[key] for key in ("name", "slug", "population", "nation", "county", "region", "lat", "lng", "source")} | {"rank": town["rank"]} for town in towns],
    }
    (DATA / "uk-top5000-towns.json").write_text(
        json.dumps(payload, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    (DATA / "UK-TOP5000-TOWNS.md").write_text(
        """# UK TOP 5000 towns

Nationwide AOV, barrier and fire keyword × town driver. Built from the 2026-10-05 population lock.

- Count: **5000**
- Order: population descending
- File: `uk-top5000-towns.json`

The previous `uk-mainland-towns-10k.json` list (995 towns over 10,000) stays the driver for existing AOV and barrier town pages. It is not replaced by this file.

These 10 Greater Manchester places are **not** in TOP 5000 and stay on the dual FULL LOCAL allowlist. Do not delete them from area hubs:

cadishead, chorlton, heywood, pendlebury, romiley, tameside, trafford, withington, worsley, wythenshawe.
""",
        encoding="utf-8",
    )
    return len(towns)


def write_stubs(slugs: list[str]) -> int:
    created = 0
    for slug in slugs:
        path = PAGES / f"{slug}.php"
        if path.is_file():
            continue
        path.write_text(
            "<?php\n"
            "/** Nationwide P0 keyword hub — copy is in the keyword catalogue. */\n"
            "require_once __DIR__ . '/../../includes/render.php';\n"
            f"renderKeywordPage({slug!r});\n",
            encoding="utf-8",
        )
        created += 1
    return created


def main() -> None:
    slugs = [line.strip() for line in P0_PATH.read_text(encoding="utf-8").splitlines() if line.strip()]
    if len(slugs) != 76 or len(set(slugs)) != 76:
        raise SystemExit(f"P0 count {len(slugs)}")
    expected = set(AOV) | set(BARRIER) | set(FIRE_SERVICE) | {
        s for s in slugs if s not in set(AOV) | set(BARRIER) | set(FIRE_SERVICE)
    }
    if set(slugs) != set(AOV) | set(BARRIER) | (set(slugs) - set(AOV) - set(BARRIER)):
        raise SystemExit("slug partition mismatch")
    town_count = write_towns()
    keywords = build_keywords(slugs)
    hubs = []
    images = {}
    for slug in slugs:
        service = service_of(slug)
        pool = image_pool(service)
        start = index_of(slug, slugs) % len(pool)
        picked = [pool[(start + offset) % len(pool)] for offset in range(3)]
        if len(set(picked)) < 3:
            raise SystemExit(f"{slug} does not have 3 images")
        for rel in picked:
            disk = rel[1:] if rel.startswith("/") else rel
            if not (ROOT / "website" / disk).is_file() and not (ROOT / disk).is_file():
                # pool from lanes is relative to website/ without leading slash in one branch
                candidate = ROOT / "website" / rel.lstrip("/")
                if not candidate.is_file():
                    raise SystemExit(f"missing {rel}")
        images[slug] = ["/" + path.lstrip("/") for path in picked]
        hubs.append(
            {
                "slug": slug,
                "line": line_of(slug),
                "service": service,
                "name": keywords[slug]["name"],
                "images": images[slug],
            }
        )
    family = {
        "towns": "uk-top5000-towns",
        "wave": "P0",
        "follow_on": "P1 hubs (the other 1924 of the 2000) and P1 ×town are not in this PR. Job-types pack is separate. TOP5000 also supersedes the 995-town fire-alarm-installer wave when that family is merged.",
        "gm_core_not_in_top5000": GM_NOT_IN_TOP,
        "hubs": hubs,
    }
    (DATA / "nationwide-3line-p0.json").write_text(
        json.dumps(family, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    (DATA / "nationwide-3line-p0-keywords.json").write_text(
        json.dumps(keywords, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    created = write_stubs(slugs)
    existing = len(slugs) - created
    print(f"towns={town_count} hubs={len(slugs)} stubs_created={created} stubs_already_present={existing}")
    sample = keywords["aov-installer"]
    print("sample words", words(sample["body"]), "title", sample["seo_title"], "meta", len(sample["meta_desc"]))


def index_of(slug: str, slugs: list[str]) -> int:
    return slugs.index(slug)


if __name__ == "__main__":
    main()
