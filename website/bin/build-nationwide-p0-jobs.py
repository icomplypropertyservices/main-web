#!/usr/bin/env python3
"""Build unique W1a P0 job-hub copy for /pages/jobs/{slug}.

Slugs that already exist in keywords.json, barriers-keywords.json, or
KEYWORD_CANONICAL are not given a second body. They 301 to
/pages/keywords/{slug}. Every other P0 slug gets a hub with
800+ words, FAQs, three images and full meta.
"""
from __future__ import annotations

import csv
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
CSV_PATH = ROOT / "data" / "nationwide-p0.csv"
KEYWORDS_PATH = ROOT / "data" / "keywords.json"
OUT_JSON = ROOT / "data" / "nationwide-p0-jobs.json"
JOBS_DIR = ROOT / "pages" / "jobs"

# Keyword wins. These hubs ship on nationwide keywords P0, not as job pages.
KEYWORD_CANONICAL = {
    "barrier-installation",
    "barrier-maintenance",
    "barrier-repair",
    "barrier-service",
    "car-park-barrier-installation",
    "emergency-aov-repair",
    "rising-arm-barrier-installation",
    "smoke-ventilation-installation",
    "vehicle-barrier-installation",
}

# Job page stays canonical even when keywords.json also has the slug (#113 overlap).
JOB_CANONICAL = {
    "addressable-fire-alarm-installation",
    "conventional-fire-alarm-installation",
}

BRAND_SUFFIX = " | iComply Property Services"
CTA = "Request a quote — POA."
NAP = (
    "iComply Property Services, 17 Woodlands Park Road, Offerton, Stockport, "
    "Cheshire SK2 5DE. Call 07517806082 or email info@icomplypropertyservices.co.uk."
)

TOKEN = {
    "aov": "AOV",
    "d-h": "D+H",
    "se": "SE",
    "controls": "Controls",
    "came": "CAME",
    "gard": "GARD",
    "gt4": "GT4",
    "bft": "BFT",
    "faac": "FAAC",
    "c-tec": "C-Tec",
    "ppm": "PPM",
    "nice": "Nice",
    "kentec": "Kentec",
    "morley": "Morley",
    "gent": "Gent",
    "colt": "Colt",
    "advanced": "Advanced",
}

# Display names that a token join would get wrong.
NAME_OVERRIDE = {
    "d-h-aov-repair": "D+H AOV repair",
    "se-controls-aov-repair": "SE Controls AOV repair",
    "se-controls-aov-service": "SE Controls AOV service",
    "came-gard-gt4-installation": "CAME GARD GT4 installation",
    "c-tec-fire-alarm-repair": "C-Tec fire alarm repair",
    "advanced-fire-alarm-repair": "Advanced fire alarm repair",
}

SERVICE = {
    "aov-smoke": ("aov-air-handling", "AOV and smoke control", "/pages/services/aov-air-handling"),
    "barrier": ("barriers", "Barriers", "/pages/services/barriers"),
}

FIRE_SERVICE = {
    "emergency-lighting": ("emergency-lighting", "Emergency lighting", "/pages/services/emergency-lighting"),
    "fire-door": ("fire-doors", "Fire doors", "/pages/services/fire-doors"),
    "fire-extinguisher": ("fire-extinguishers", "Fire extinguishers", "/pages/services/fire-extinguishers"),
}

AOV_IMAGES = [
    "/assets/images/services/aov-air-handling.jpg",
    "/assets/images/lanes/aov-air-handling/02.jpg",
    "/assets/images/lanes/aov-air-handling/05.jpg",
    "/assets/images/lanes/aov-air-handling/08.jpg",
    "/assets/images/lanes/aov-air-handling/11.jpg",
    "/assets/images/lanes/aov-air-handling/21.jpg",
    "/assets/images/lanes/aov-air-handling/38.jpg",
    "/assets/images/lanes/aov-air-handling/41.jpg",
    "/assets/images/lanes/aov-air-handling/43.jpg",
]
BARRIER_IMAGES = [
    "/assets/images/services/barriers.jpg",
    "/assets/images/lanes/barriers/01.jpg",
    "/assets/images/lanes/barriers/04.jpg",
    "/assets/images/lanes/barriers/06.jpg",
    "/assets/images/lanes/barriers/08.jpg",
    "/assets/images/lanes/barriers/11.jpg",
    "/assets/images/lanes/barriers/14.jpg",
    "/assets/images/lanes/barriers/18.jpg",
    "/assets/images/lanes/barriers/22.jpg",
]
FIRE_IMAGES = {
    "emergency-lighting": [
        "/assets/images/services/emergency-lighting.jpg",
        "/assets/images/services/fire-alarms.jpg",
        "/assets/images/services/fire-signage.jpg",
    ],
    "fire-door": [
        "/assets/images/services/fire-doors.jpg",
        "/assets/images/services/fire-alarms.jpg",
        "/assets/images/services/fire-stopping.jpg",
    ],
    "fire-extinguisher": [
        "/assets/images/services/fire-extinguishers.jpg",
        "/assets/images/services/fire-alarms.jpg",
        "/assets/images/services/fire-signage.jpg",
    ],
    "default": [
        "/assets/images/services/fire-alarms.jpg",
        "/assets/images/services/emergency-lighting.jpg",
        "/assets/images/services/fire-doors.jpg",
        "/assets/images/services/fire-extinguishers.jpg",
        "/assets/images/services/fire-signage.jpg",
        "/assets/images/services/smoke-co-alarms.jpg",
    ],
}

SCENES_BY_LINE = {
    "fire": [
        "a stair core in an occupied block where sounders must still be heard during the visit",
        "a school plant room that is only free after the pupils have left",
        "a care-home corridor where residents stay in their rooms while we work",
        "an office riser that is shared with other tenants",
        "a retail unit with the shop trading on the ground floor",
        "the common parts of a house in multiple occupation",
        "a hotel plant deck that is reached from a service stair",
        "a hospital plant room booked around clinical hours",
        "an industrial unit with a mezzanine and a single stair",
        "a housing-association block with a concierge log we have to sign",
        "a purpose-built block of flats with detectors on each landing",
        "a mixed-use building where the commercial demise sits under flats",
        "a student block with a short window between check-in days",
        "a leisure centre with wet changing rooms near the panel",
        "a clinic with a reception that cannot lose its escape lighting",
        "a theatre plant space reached by a cat ladder",
        "a data room where the panel is inside a locked cupboard",
        "a church hall used as a weekday nursery",
        "a light-industrial estate unit with a roller shutter and a side stair",
        "a sheltered scheme with a house manager on site",
    ],
    "aov-smoke": [
        "a stair core in an occupied block where the vents must still open during the visit",
        "a school plant room that is only free after the pupils have left",
        "a warehouse loading door with a smoke vent above the shutter",
        "a care-home corridor where residents stay in their rooms while we work",
        "an office riser that is shared with other tenants",
        "a retail unit with the shop trading on the ground floor",
        "a hotel plant deck that is reached from a service stair",
        "a hospital plant room booked around clinical hours",
        "an industrial unit with a mezzanine and a single stair",
        "a housing-association block with a concierge log we have to sign",
        "a purpose-built block of flats with a smoke shaft at the stair head",
        "a mixed-use building where the commercial demise sits under flats",
        "a student block with a short window between check-in days",
        "a leisure centre with wet changing rooms near the panel",
        "a theatre plant space reached by a cat ladder",
        "a light-industrial estate unit with a roller shutter and a side stair",
        "a sheltered scheme with a house manager on site",
        "a clinic with a reception that cannot lose its escape lighting",
    ],
    "barrier": [
        "a car-park lane that cannot stay closed for a full day",
        "a multi-storey car park with a rising-arm at the entry island",
        "a logistics yard where HGVs queue if the barrier stays down",
        "a retail unit with the shop trading on the ground floor",
        "a housing-association block with a concierge log we have to sign",
        "a mixed-use building where the commercial demise sits under flats",
        "a student block with a short window between check-in days",
        "a leisure centre with wet changing rooms near the panel",
        "a light-industrial estate unit with a roller shutter and a side stair",
        "a hotel plant deck that is reached from a service stair",
        "an industrial unit with a mezzanine and a single stair",
        "a sheltered scheme with a house manager on site",
        "a warehouse loading door beside the entry lane",
        "an office car park shared with other tenants",
        "a care-home staff car park that must stay open for ambulances",
        "a school site that is only free after the pupils have left",
    ],
}

ACCESS_BY_LINE = {
    "fire": [
        "the roof is only reached with a planned access method, not a borrowed ladder",
        "keys are held by the managing agent and released against a timed booking",
        "the ceiling void is congested, so we photograph the route before we cut anything",
        "the panel is in a cupboard that also stores cleaning kit, which we move and put back",
        "evening attendance is required because the building is open to the public by day",
        "induction and a permit are required before we enter the plant room",
        "the escape route stays clear; we do not leave boards or tools in the stair",
        "parking for the van is off the main entrance and agreed before the day",
        "the incumbent contractor's log is on site and we read it before we isolate",
        "a second person is needed at the panel while the other walks the floors",
    ],
    "aov-smoke": [
        "the roof is only reached with a planned access method, not a borrowed ladder",
        "keys are held by the managing agent and released against a timed booking",
        "the ceiling void is congested, so we photograph the route before we cut anything",
        "the panel is in a cupboard that also stores cleaning kit, which we move and put back",
        "evening attendance is required because the building is open to the public by day",
        "a second person is needed at the vent while the other watches the panel",
        "induction and a permit are required before we enter the plant room",
        "the escape route stays clear; we do not leave boards or tools in the stair",
        "parking for the van is off the main entrance and agreed before the day",
        "the incumbent contractor's log is on site and we read it before we isolate",
    ],
    "barrier": [
        "the lane must stay usable for residents while we work on one barrier",
        "keys are held by the managing agent and released against a timed booking",
        "evening attendance is required because the building is open to the public by day",
        "the car park is ticketed, so the operator stays with us until the lane is proven",
        "induction and a permit are required before we enter the plant room",
        "parking for the van is off the main entrance and agreed before the day",
        "the incumbent contractor's log is on site and we read it before we isolate",
        "the escape route stays clear; we do not leave boards or tools in the stair",
        "a second person is needed at the island while the other watches the cabinet",
        "the roof is only reached with a planned access method, not a borrowed ladder",
    ],
}

MEASURES_BY_LINE = {
    "fire": [
        "loop or zone resistance before any device is changed",
        "battery voltage under load, recorded rather than guessed",
        "duration of the emergency fitting once the supply is dropped",
        "sound pressure at the furthest bedroom or workstation",
        "cause-and-effect from the call point to the sounders",
        "the extinguisher's weight, gauge and service date against the label",
        "detector contamination or sensing chamber condition against the panel reading",
        "zone continuity after the last device is landed",
        "panel fault LED and log entry before and after the repair",
        "isolation of the correct loop or zone without silencing the whole building",
        "battery standby calculation against the load already fitted",
        "device address and label against the as-fitted schedule",
    ],
    "aov-smoke": [
        "travel of the actuator against the vent's free movement",
        "loop or zone resistance before any device is changed",
        "battery voltage under load, recorded rather than guessed",
        "cause-and-effect from the fire panel input to the vent output",
        "door-closer speed and latch without holding the leaf shut by hand",
        "end-of-line signal at the vent after the actuator has moved",
        "open and close time of the vent against the strategy note",
        "manual override still opens the vent when the panel is isolated",
        "current draw of the actuator at the start of travel",
        "damper position against the panel status after a fire-input test",
    ],
    "barrier": [
        "safety-edge and loop reaction while the lane is walked",
        "boom length and spring balance on the arm that is actually fitted",
        "current draw of the motor at the start of travel and at stall",
        "whether the manual release still lets the lane open",
        "loop detector sensitivity with a vehicle in the detection zone",
        "limit-switch travel at the fully open and fully closed stops",
        "photocell alignment across the lane before the arm is left in service",
        "cabinet supply voltage under load while the arm is moving",
        "ground loop continuity before any detector pack is changed",
        "arm rest and latch without holding the boom by hand",
    ],
}

ASSUMPTIONS_BY_LINE = {
    "fire": [
        "that a replacement part can be ordered from the nameplate alone",
        "that the previous log's 'reset' means the fault has gone",
        "that every brand on the old panel is still supported",
        "that the fire strategy allows the stair to be used as the only escape during the test",
        "that a like-for-like device is still the right category for the room",
        "that the managing agent wants a full rip-out when a module repair will do",
        "that the existing cause-and-effect still matches the occupied layout",
        "that wireless devices can be added without checking battery and signal margins",
    ],
    "aov-smoke": [
        "that the drawing matches the vent that is fitted",
        "that a replacement part can be ordered from the nameplate alone",
        "that the previous log's 'reset' means the fault has gone",
        "that every brand on the old panel is still supported",
        "that the fire strategy allows the stair to be used as the only escape during the test",
        "that a like-for-like device is still the right category for the room",
        "that the managing agent wants a full rip-out when a module repair will do",
        "that the vent free area still matches the smoke-control strategy",
    ],
    "barrier": [
        "that a replacement part can be ordered from the nameplate alone",
        "that the previous log's 'reset' means the fault has gone",
        "that a new barrier is required when the arm or the loop is the real fault",
        "that the managing agent wants a full rip-out when a module repair will do",
        "that the existing loop layout still matches the lane markings",
        "that a like-for-like motor is still the right duty for the boom length",
        "that the safety edge can be bypassed for a temporary reopen",
        "that the credential system is out of scope when the arm will not travel",
    ],
}


def pools_for(line: str) -> tuple[list[str], list[str], list[str], list[str]]:
    key = line if line in SCENES_BY_LINE else "fire"
    return (
        SCENES_BY_LINE[key],
        ACCESS_BY_LINE[key],
        MEASURES_BY_LINE[key],
        ASSUMPTIONS_BY_LINE[key],
    )

CLOSERS = [
    "If the agreed work needs a return visit, that visit is scoped on its own and quoted again.",
    "We do not leave the system isolated overnight unless the duty holder has accepted that in writing.",
    "A failed device stays labelled so the next person can see what was found.",
    "The instructing client gets the note the same day, not a promise of a report next month.",
    "Where the building has a waking watch or a management procedure, we tell them what changed before we leave.",
    "No figure is given on the phone until the equipment and the access are known.",
]

WHOS = [
    "managing agents and block freeholders",
    "landlords and letting agents",
    "facilities managers",
    "commercial tenants and their agents",
    "housing associations",
    "school and college estates teams",
    "care-home managers",
    "car-park operators",
    "warehouse and industrial occupiers",
    "hotel and leisure operators",
]


def display_name(slug: str) -> str:
    if slug in NAME_OVERRIDE:
        return NAME_OVERRIDE[slug]
    parts = slug.split("-")
    out = []
    i = 0
    while i < len(parts):
        pair = f"{parts[i]}-{parts[i+1]}" if i + 1 < len(parts) else ""
        if pair in TOKEN:
            out.append(TOKEN[pair])
            i += 2
            continue
        token = parts[i]
        out.append(TOKEN.get(token, token))
        i += 1
    if out:
        out[0] = out[0][0].upper() + out[0][1:] if out[0].islower() else out[0]
    # Lowercase ordinary words after the first, keep acronyms.
    pretty = []
    for n, word in enumerate(out):
        if word.isupper() or any(ch.isdigit() for ch in word) or "-" in word or "+" in word:
            pretty.append(word)
        elif n == 0:
            pretty.append(word[:1].upper() + word[1:])
        else:
            pretty.append(word.lower())
    return " ".join(pretty).replace(" call out", " call-out")


def service_for(slug: str, line: str) -> tuple[str, str, str]:
    if line == "fire":
        for prefix, row in FIRE_SERVICE.items():
            if slug.startswith(prefix):
                return row
        return ("fire-alarms", "Fire alarms", "/pages/services/fire-alarms")
    return SERVICE[line]


def images_for(slug: str, line: str, index: int) -> list[tuple[str, str]]:
    name = display_name(slug)
    if line == "aov-smoke":
        pool = AOV_IMAGES
    elif line == "barrier":
        pool = BARRIER_IMAGES
    else:
        pool = FIRE_IMAGES["default"]
        for prefix, rows in FIRE_IMAGES.items():
            if prefix != "default" and slug.startswith(prefix):
                pool = rows
                break
    if len(pool) < 3:
        pool = pool + FIRE_IMAGES["default"]
    picked = []
    for shift in range(len(pool) * 2):
        src = pool[(index + shift) % len(pool)]
        if src not in picked:
            picked.append(src)
        if len(picked) == 3:
            break
    if len(set(picked)) < 3:
        raise SystemExit(f"not enough images for {slug}: {picked}")
    captions = [
        f"{name} — equipment looked at on site by iComply Property Services",
        f"{name} — the control or lane position recorded before any part is changed",
        f"{name} — the note left with the instructing client after the visit",
    ]
    return list(zip(picked, captions))


def intent_of(slug: str) -> str:
    order = [
        ("call-out", "call-out"),
        ("fault-finding", "fault-finding"),
        ("commissioning", "commissioning"),
        ("replacement", "replacement"),
        ("installation", "installation"),
        ("maintenance", "maintenance"),
        ("servicing", "servicing"),
        ("service", "service"),
        ("repair", "repair"),
        ("testing", "testing"),
        ("inspection", "inspection"),
        ("upgrade", "upgrade"),
        ("certificate", "certificate"),
        ("stuck-down", "fault"),
        ("loop-detector", "service"),
        ("ppm", "maintenance"),
    ]
    for needle, intent in order:
        if needle in slug:
            return intent
    return "service"


def equipment_of(slug: str, line: str, brand: str) -> str:
    specific = {
        "aov-actuator-repair": "the AOV actuator, chain drive or spindle motor",
        "aov-call-out": "the AOV panel, vents and actuators already on the building",
        "aov-panel-repair": "the AOV control panel and its fire-alarm interface",
        "aov-replacement": "the AOV system that is being changed, vent by vent",
        "aov-service": "the smoke vents, actuators and AOV panel",
        "colt-aov-service": "the Colt AOV equipment already fitted",
        "d-h-aov-repair": "the D+H actuator, control unit or ventilator already fitted",
        "emergency-aov-repair": "the AOV that has failed open, failed shut, or will not answer the panel",
        "se-controls-aov-repair": "the SE Controls actuator or OS2-style controller already on site",
        "se-controls-aov-service": "the SE Controls smoke-control kit already on site",
        "smoke-control-system-installation": "the smoke-control system being installed to the fire strategy",
        "smoke-control-system-repair": "the smoke-control panel, dampers, vents and interfaces",
        "smoke-damper-testing": "the smoke dampers and their actuators",
        "smoke-vent-maintenance": "the smoke vents, louvres and their drives",
        "smoke-vent-repair": "the smoke vent that is stuck, leaking or not answering",
        "smoke-vent-service": "the smoke vent, its actuator and the end-of-line signal",
        "smoke-ventilation-installation": "the smoke-ventilation installation, natural or mechanical as the strategy requires",
        "automatic-barrier-installation": "the automatic rising-arm barrier and its safety devices",
        "automatic-barrier-maintenance": "the automatic barrier, arm, loop and safety edge",
        "automatic-barrier-repair": "the automatic barrier that has stopped, drifted or lost its safety device",
        "barrier-arm-replacement": "the barrier arm, boom and any skirt or LED strip fitted to it",
        "barrier-call-out": "the barrier that is stuck, open, or cycling",
        "barrier-installation": "the vehicle barrier installation, island, supply and safety devices",
        "barrier-loop-detector": "the induction loop, detector pack and the lead-in",
        "barrier-maintenance": "the barrier motor, arm, springs, loop and photocells",
        "barrier-motor-replacement": "the barrier motor, gearbox and limit switches on the existing cabinet",
        "barrier-repair": "the barrier cabinet, motor and safety circuit",
        "barrier-replacement": "the barrier being replaced, including the island and the safety devices",
        "barrier-service": "the barrier, its manual release and its safety devices",
        "barrier-stuck-down": "a barrier stuck down across the lane",
        "bft-barrier-repair": "the existing BFT barrier",
        "came-barrier-installation": "the CAME barrier being installed",
        "came-barrier-repair": "the existing CAME barrier",
        "came-barrier-service": "the existing CAME barrier and its safety devices",
        "came-gard-gt4-installation": "a CAME GARD GT4 rising-arm barrier",
        "car-park-barrier-installation": "the car-park barrier, island and entry lane",
        "car-park-barrier-maintenance": "the car-park barrier and its loop, edge and arm",
        "car-park-barrier-repair": "the car-park barrier that has failed in the lane",
        "car-park-barrier-servicing": "the car-park barrier on its planned service visit",
        "faac-barrier-repair": "the existing FAAC barrier",
        "nice-barrier-repair": "the existing Nice barrier",
        "rising-arm-barrier-installation": "the rising-arm barrier, boom and safety devices",
        "rising-arm-barrier-repair": "the rising-arm barrier that will not travel or will not lock",
        "vehicle-barrier-installation": "the vehicle barrier controlling the lane",
        "vehicle-barrier-repair": "the vehicle barrier already controlling the lane",
        "addressable-fire-alarm-installation": "the addressable fire alarm, panel, loop devices and sounders",
        "advanced-fire-alarm-repair": "the existing Advanced Electronics fire alarm panel and its loop devices",
        "c-tec-fire-alarm-repair": "the existing C-Tec fire alarm panel and its devices",
        "conventional-fire-alarm-installation": "the conventional fire alarm, zones, detectors and sounders",
        "emergency-fire-alarm-repair": "the fire alarm that is in fault, false alarm or will not reset",
        "false-alarm-investigation": "the device and the cause behind a false alarm or unwanted fire signal",
        "fire-alarm-call-out": "the fire alarm panel that is in fault or will not reset",
        "fire-alarm-panel-repair": "the fire alarm panel, its power supply and its loop or zone cards",
        "fire-alarm-panel-replacement": "the fire alarm panel being changed and the devices that must still match it",
        "fire-alarm-ppm": "the fire alarm on its planned preventive maintenance visit",
        "fire-alarm-replacement": "the fire alarm system being replaced",
        "fire-extinguisher-servicing": "the portable fire extinguishers already on the premises",
        "gent-fire-alarm-service": "the existing Gent fire alarm",
        "kentec-fire-alarm-repair": "the existing Kentec fire alarm panel",
        "morley-fire-alarm-repair": "the existing Morley fire alarm panel",
        "wireless-fire-alarm-installation": "the wireless fire alarm, panel and radio devices",
    }
    if slug in specific:
        return specific[slug]
    if brand:
        return f"the existing {brand} equipment"
    if line == "aov-smoke":
        return "the smoke-control equipment"
    if line == "barrier":
        return "the vehicle barrier"
    return "the fire system"


def standard_of(slug: str, line: str) -> str:
    if slug.startswith("emergency-lighting"):
        return "BS 5266-1"
    if slug.startswith("fire-door"):
        return "the fire-door duty in the building's fire strategy"
    if slug.startswith("fire-extinguisher"):
        return "BS 5306-3"
    if "damper" in slug or "smoke" in slug or line == "aov-smoke":
        return "BS EN 12101 and BS 7346-8"
    if line == "barrier":
        return "BS EN 12453"
    if "wireless" in slug:
        return "BS 5839-1"
    if slug.startswith("addressable") or slug.startswith("conventional") or "fire-alarm" in slug:
        return "BS 5839-1"
    return "BS 5839-1"


def paper_of(slug: str, intent: str) -> str:
    if intent == "installation":
        return "an installation note describing what was fitted, how it was tested, and what the client must do weekly"
    if intent in {"service", "servicing", "maintenance"}:
        return "a service note listing what was inspected, what failed, and the date of the next planned visit"
    if intent == "testing":
        return "a test record with the result for each item and any item that could not be reached"
    if intent == "commissioning":
        return "a commissioning note for the cause-and-effect that was actually witnessed"
    if intent in {"call-out", "fault", "fault-finding"}:
        return "a call-out note naming the fault, the part changed or still required, and whether the system was left online"
    if intent == "replacement":
        return "a replacement note showing what came out, what went in, and the retest"
    if intent == "inspection":
        return "an inspection note the duty holder can file, with defects separated from observations"
    return "a written note of the visit for the site file"


def policy_of(slug: str, brand: str, line: str) -> str:
    if line != "barrier":
        if brand:
            return "brand_service"
        return "none"
    if slug in {"came-barrier-installation", "came-gard-gt4-installation"}:
        return "came_new"
    if slug.startswith("came-"):
        return "came_service"
    if brand:
        return "incumbent"
    if any(word in slug for word in ("installation", "replacement")) and "arm" not in slug and "motor" not in slug:
        return "came_new"
    return "incumbent_generic"


def brand_paragraph(name: str, slug: str, brand: str, policy: str, equipment: str, line: str = "fire") -> str:
    if policy == "came_new":
        product = "CAME GARD GT4" if "gt4" in slug else "CAME"
        return (
            f"{name} is specified as {product}. iComply is a CAME partner. "
            f"We state that in plain words and we do not publish a partner number, "
            f"an accreditation number, or an authorised-dealer claim. "
            f"The lane, the boom length, the supply and the safety devices are measured on site. "
            f"Installation is price on application after that survey. "
            f"We do not print a pack price or a from-price on this page. "
            f"If an older barrier of another make is already in the lane and the incumbent should stay, "
            f"we say so in the note and we repair that existing kit instead of forcing a change of make. "
            f"The model is confirmed on site before parts are ordered."
        )
    if policy == "came_service":
        return (
            f"{name} is for CAME equipment that is already installed. "
            f"iComply is a CAME partner. We do not publish a partner number and we do not describe "
            f"the visit as manufacturer approval. The model, the boom and the safety devices are confirmed "
            f"on site. Parts are identified from the cabinet that is there, not from a guess over the phone. "
            f"Where the right outcome is a new barrier rather than a repair, the replacement is specified as CAME "
            f"and quoted separately, price on application after survey."
        )
    if policy == "incumbent":
        label = brand or "the existing make"
        return (
            f"{name} is repair, service and maintenance of existing {label} equipment only. "
            f"We do not offer a new {label} installation, a {label} upgrade package, or a {label} replacement system. "
            f"The model is confirmed on site. "
            f"iComply is a CAME partner for new barrier installation. "
            f"If the existing {label} barrier should be replaced rather than repaired, the new system is specified as CAME "
            f"unless the incumbent should stay, and that choice is written down before anyone orders a cabinet. "
            f"We do not claim to be an authorised {label} dealer and we do not quote an approval number."
        )
    if policy == "incumbent_generic":
        return (
            f"{name} is carried out on the barrier that is already in the lane. "
            f"The make and model are confirmed on site. "
            f"New barrier installation and full barrier replacement are specified as CAME, "
            f"because iComply is a CAME partner. "
            f"We do not publish a partner number. "
            f"Other makes stay in service when the incumbent should remain: we repair, service and maintain that existing kit, "
            f"and we do not describe that work as a new installation of that other make. "
            f"Arm changes and motor changes on an existing cabinet are parts for the kit that is there, not a new brand install."
        )
    if policy == "brand_service":
        label = {
            "colt": "Colt",
            "d-h-mechatronic": "D+H",
            "se-controls": "SE Controls",
            "advanced-electronics": "Advanced Electronics",
            "c-tec": "C-Tec",
            "gent": "Gent",
            "kentec": "Kentec",
            "morley": "Morley",
        }.get(brand, brand or "the manufacturer")
        return (
            f"{name} covers existing {label} equipment. The model is confirmed on site. "
            f"We service and repair what is already fitted. We do not claim manufacturer approval, "
            f"an authorised-dealer status, or an accreditation number for {label}. "
            f"If a part is obsolete we say so, and we describe the alternative in writing before it is fitted. "
            f"{equipment[0].upper() + equipment[1:]} is identified from the nameplate and the wiring, "
            f"not from a catalogue photograph."
        )
    if line == "barrier":
        return (
            f"The make of {equipment} is confirmed on site. "
            f"We do not claim manufacturer approval or an authorised-dealer status for any brand on this page. "
            f"iComply is a CAME partner for barrier installation only, and that statement stays in plain words with no number."
        )
    return (
        f"The make of {equipment} is confirmed on site. "
        f"We do not claim manufacturer approval or an authorised-dealer status for any brand on this page. "
        f"Brand and model are taken from the equipment that is actually fitted, not from a catalogue photograph."
    )


def fit_meta(name: str, who: str) -> str:
    """140–160 characters, call to action kept intact, no mid-word cut."""
    fillers = [
        "Scope is confirmed on site before anyone attends.",
        "Scope is confirmed on site.",
        "The figure follows the survey.",
        "We attend from our Stockport base.",
        "Landlords, agents and commercial sites can instruct us.",
        "The model is confirmed on site.",
    ]
    base = f"{name} for {who}, arranged UK-wide from Stockport."
    chosen = f"{base} {CTA}"
    if len(chosen) > 160:
        base = f"{name} for {who}, UK-wide from Stockport."
        chosen = f"{base} {CTA}"
    for filler in fillers:
        trial = f"{base} {filler} {CTA}"
        if 140 <= len(trial) <= 160:
            return trial
        if len(trial) < 140:
            chosen = trial
    if len(chosen) < 140:
        # Add the shortest filler that reaches the floor, then trim on a word.
        pad = "The visit is booked around the building."
        trial = f"{base} {pad} {CTA}"
        if len(trial) <= 160:
            chosen = trial
        else:
            chosen = f"{base} {CTA}"
    if len(chosen) > 160:
        # Keep CTA. Trim the middle on a word boundary.
        keep = f" {CTA}"
        head = chosen[: -len(keep)].strip()
        while len(head) + len(keep) > 160 and " " in head:
            head = head.rsplit(" ", 1)[0]
        chosen = f"{head}{keep}"
    return re.sub(r"\s+", " ", chosen).strip()


def paragraphs_for(spec: dict, index: int) -> list[str]:
    name = spec["name"]
    equipment = spec["equipment"]
    fault = spec["fault"]
    standard = spec["standard"]
    paper = spec["paper"]
    who = spec["who"]
    line = spec.get("line", "fire")
    scenes, access_pool, measures, assumptions = pools_for(line)
    scene = scenes[index % len(scenes)]
    access = access_pool[(index * 3) % len(access_pool)]
    measure = measures[(index * 5) % len(measures)]
    assumption = assumptions[(index * 7) % len(assumptions)]
    closer = CLOSERS[(index * 11) % len(CLOSERS)]
    intent = spec["intent"]
    steps = spec["steps"]

    opening = (
        f"{name} is a site visit, not a packaged price. "
        f"We arrange it UK-wide from Stockport for {who}. "
        f"A typical instruction arrives when {fault}. "
        f"The building we are usually called to is {scene}. "
        f"Before a date is offered we ask what equipment is already there, who holds the keys, "
        f"and whether the area has to stay in use. "
        f"We do not claim a local office in that town. "
        f"The engineer comes from our Stockport base at {NAP}"
    )
    access_p = (
        f"Access for {name} is part of the scope. On this sort of visit, {access}. "
        f"We do not start by isolating {equipment} until the duty holder knows the system may be offline "
        f"and for how long. "
        f"The useful first measurement is {measure}. "
        f"We do not assume {assumption}. "
        f"Photographs of the nameplate, the supply and the surrounding fixings are taken before anything is removed, "
        f"so the note matches the building rather than a generic description of {name.lower()}."
    )
    standard_p = (
        f"The work standard we write against on {name} is {standard}. "
        f"That is the standard for the task, not an accreditation and not a certificate number. "
        f"We do not claim a third-party approval body, and we do not invent a membership number. "
        f"Where the fire strategy or the specification asks for a particular category or a particular cause-and-effect, "
        f"we follow that document and we record any place the installed {equipment} does not match it. "
        f"If the strategy is missing, we say so and we stop short of inventing a design."
    )
    brand_p = spec["brand_paragraph"]
    step_paras = []
    shapes = [
        (
            "{step} This is done while {equipment} is still in the condition we found it, "
            "so the note for {name} explains the fault rather than only the repair. "
            "The result is written in plain language for {who}. "
            "If a part is required and is not on the van, we do not pretend it was fitted."
        ),
        (
            "Next we deal with this specific point of {name}: {step} "
            "The check is repeated once after the adjustment so a single lucky result is not treated as finished. "
            "Anything that still fails stays on the list. "
            "Price for the extra item is on application; it is not added as a surprise on the day."
        ),
        (
            "For {name} we also confirm the following, because it changes the scope: {step} "
            "The observation is tied back to {standard}. "
            "We name the room or the stair in the note so a later visit can find {equipment} again."
        ),
        (
            "{step} That step matters on {name} when the building is {scene}. "
            "Rushing it is how a second call-out is created. "
            "We would rather leave a clear defect than a silent temporary fix that the next engineer cannot see."
        ),
        (
            "The instructing client can expect this from the {name} visit: {step} "
            "We explain it to the person who met us, and we put the same words in {paper}. "
            "Verbal-only handovers are not the record."
        ),
        (
            "Before we close {name} we make sure of one more thing: {step} "
            "If that check fails, the system is not described as complete. "
            "The outstanding item is quoted price on application after the survey we have just done, "
            "using the measurements already taken."
        ),
    ]
    for n, step in enumerate(steps):
        template = shapes[n % len(shapes)]
        step_paras.append(
            template.format(
                step=step,
                equipment=equipment,
                name=name,
                who=who,
                standard=standard,
                scene=scene,
                paper=paper,
            )
        )
    paperwork = (
        f"The paperwork for {name} is {paper}. "
        f"It names the address, the date, {equipment}, and the result. "
        f"It does not carry a fake approval logo. "
        f"Certificate-style wording on this page means the note we issue for the work done, "
        f"not a claim that iComply holds a third-party registration. "
        f"The duty holder keeps the copy. We can resend it to the managing agent when the instruction asks for that. "
        f"{closer}"
    )
    came_money = (
        "CAME supply packs, where a barrier job uses them, are also price on application on this page. "
        if line == "barrier"
        else ""
    )
    money = (
        f"Pricing for {name} is price on application after survey. "
        f"We do not publish a pound figure, a band, or a from-price. "
        f"The survey has to see {equipment}, the access, and whether the visit is a repair, a service or a new installation. "
        f"A phone call can book the survey. It cannot produce a serious figure while the model is unknown. "
        f"{came_money}"
        f"We are instructed by {who}, and the quote is addressed to that client."
    )
    nationwide = (
        f"Coverage for {name} is UK-wide from Stockport. "
        f"{NAP} "
        f"Town pages, when they are published in a later wave, will use the real county and region. "
        f"This hub does not pretend we have a branch in every town, and it does not use a Greater Manchester-only town list "
        f"for a nationwide trade. "
        f"The intent of this page is {intent}: we do the work the instruction describes, and we do not pad it with unrelated trades. "
        f"These lines are not gas work, so this page does not use gas-registration wording."
    )
    return [opening, access_p, standard_p, brand_p, *step_paras, paperwork, money, nationwide]


def steps_for(name: str, equipment: str, fault: str, standard: str, paper: str) -> list[str]:
    return [
        f"Identify {equipment} from the nameplate and compare it with the report of {fault}.",
        f"Prove the supply, the signal and the mechanical travel before ordering parts for {name}.",
        f"Record {fault} against the last log entry so the visit does not repeat a fix that already failed.",
        f"Check the safety device or the fire interface that sits next to {equipment}, not only the obvious failed part.",
        f"Write the result against {standard}, as a work standard rather than as an accreditation.",
        f"Hand over {paper}, and list any item still open so {name} is not signed off early.",
    ]


def points_for(spec: dict) -> list[str]:
    return [
        f"Instruction understood before attendance: {spec['fault']}.",
        f"Equipment confirmed on site: {spec['equipment']}.",
        f"Work standard named in the note: {spec['standard']}.",
        f"Record left with the client: {spec['paper']}.",
        "Price on application after survey. No published pound figure.",
        f"Arranged UK-wide from Stockport for {spec['who']}.",
    ]


def faqs_for(spec: dict) -> list[list[str]]:
    name = spec["name"]
    who = spec["who"]
    equipment = spec["equipment"]
    standard = spec["standard"]
    policy = spec["policy"]
    brand_answer = {
        "came_new": "New barrier installation on this page is CAME. iComply is a CAME partner. We do not publish a partner number.",
        "came_service": "This visit is for CAME equipment already on site. iComply is a CAME partner. The model is confirmed on site. We do not publish a partner number.",
        "incumbent": "We repair and service the existing make only. We do not install that other make as a new system. New barrier installation is specified as CAME unless the incumbent should stay.",
        "incumbent_generic": "We work on the barrier that is already there. New installation and full replacement are specified as CAME. iComply is a CAME partner, stated in plain words with no number.",
        "brand_service": "We service and repair the existing manufacturer’s equipment. The model is confirmed on site. We do not claim manufacturer approval.",
        "none": "The make is confirmed on site. We do not claim manufacturer approval on this page.",
    }[policy]
    return [
        [
            f"How is {name} quoted?",
            f"{name} is price on application after we have seen {equipment} and the access. We do not publish a pound figure or a from-price.",
        ],
        [
            f"Do you cover my town for {name}?",
            f"Yes. {name} is arranged UK-wide from our Stockport base for {who}. We do not claim a local office in each town.",
        ],
        [
            f"What standard do you work to on {name}?",
            f"We work to {standard} as the standard for the task. That is not an accreditation and we do not quote a membership number.",
        ],
        [
            f"Which brands does {name} cover?",
            brand_answer,
        ],
        [
            f"What should I have ready when I ask for {name}?",
            "The full address, what has failed or what you want installed, any log or previous note, and who can meet us. Photos of the nameplate help. They do not replace the survey.",
        ],
    ]


def related(slug: str, peers: list[str], names: dict[str, str], service_href: str, service_label: str) -> list[list[str]]:
    links = [[service_href, service_label], ["/pages/jobs", "All job types"]]
    others = [p for p in peers if p != slug]
    # Stable spread so neighbouring hubs do not all link to the same trio.
    if others:
        start = others.index(slug) if slug in others else peers.index(slug) if slug in peers else 0
        # peers includes slug; compute index in the full peer list.
        start = peers.index(slug)
        for shift in (1, 4, 9):
            peer = peers[(start + shift) % len(peers)]
            if peer == slug:
                continue
            links.append([f"/pages/jobs/{peer}", names[peer]])
    # De-duplicate while keeping order.
    seen = set()
    out = []
    for href, label in links:
        if href in seen:
            continue
        seen.add(href)
        out.append([href, label])
    return out


def build() -> None:
    keywords = set(json.loads(KEYWORDS_PATH.read_text()))
    barriers = ROOT / "data" / "barriers-keywords.json"
    if barriers.is_file():
        keywords.update(json.loads(barriers.read_text()))
    keywords.update(KEYWORD_CANONICAL)
    rows = list(csv.DictReader(CSV_PATH.open()))
    assert len(rows) == 90, len(rows)
    missing_files = [src for src in AOV_IMAGES + BARRIER_IMAGES + FIRE_IMAGES["default"] + FIRE_IMAGES["emergency-lighting"] + FIRE_IMAGES["fire-door"] + FIRE_IMAGES["fire-extinguisher"] if not (ROOT / src.lstrip("/")).is_file()]
    if missing_files:
        raise SystemExit("missing images: " + ", ".join(missing_files))

    create_rows = [
        row for row in rows
        if row["slug"] not in keywords or row["slug"] in JOB_CANONICAL
    ]
    peers_by_line: dict[str, list[str]] = {}
    for row in create_rows:
        peers_by_line.setdefault(row["line"], []).append(row["slug"])
    names = {row["slug"]: display_name(row["slug"]) for row in create_rows}

    jobs: dict[str, dict] = {}
    for index, row in enumerate(create_rows):
        slug = row["slug"]
        line = row["line"]
        brand = row["brand"]
        name = names[slug]
        intent = intent_of(slug)
        equipment = equipment_of(slug, line, brand)
        fault = {
            "repair": f"{equipment} has failed, stuck, or started reporting a fault",
            "service": f"a planned service of {equipment} is due",
            "servicing": f"a planned servicing visit for {equipment} is due",
            "maintenance": f"planned maintenance of {equipment} is due",
            "installation": f"a new installation of {equipment} has been instructed",
            "replacement": f"{equipment} is to be replaced rather than patched again",
            "testing": f"a recorded test of {equipment} is due",
            "call-out": f"someone needs a call-out because {equipment} has failed today",
            "fault": f"the system is stuck and {equipment} needs a same-day look"
            if line != "barrier"
            else f"the lane is stuck and {equipment} needs a same-day look",
            "fault-finding": f"the cause of a fault on {equipment} is not yet known",
            "commissioning": f"{equipment} has been fitted and now needs commissioning",
            "inspection": f"an inspection of {equipment} is required for the site file",
            "upgrade": f"an upgrade of {equipment} has been asked for",
            "certificate": f"paperwork for work on {equipment} is being asked for",
        }[intent]
        standard = standard_of(slug, line)
        paper = paper_of(slug, intent)
        who = WHOS[index % len(WHOS)]
        policy = policy_of(slug, brand, line)
        service_slug, service_label, service_href = service_for(slug, line)
        brand_p = brand_paragraph(name, slug, brand, policy, equipment, line)
        spec = {
            "name": name,
            "equipment": equipment,
            "fault": fault,
            "standard": standard,
            "paper": paper,
            "who": who,
            "intent": intent,
            "policy": policy,
            "line": line,
            "brand_paragraph": brand_p,
            "steps": steps_for(name, equipment, fault, standard, paper),
        }
        paras = paragraphs_for(spec, index)
        text = " ".join(paras)
        words = re.findall(r"[A-Za-z0-9+’']+", text)
        if len(words) < 800:
            raise SystemExit(f"{slug} only {len(words)} words")
        title = f"{name}{BRAND_SUFFIX}"
        if "…" in title or title.endswith("-"):
            raise SystemExit("truncated title " + title)
        meta = fit_meta(name, who)
        if not 140 <= len(meta) <= 160:
            raise SystemExit(f"meta length {len(meta)} for {slug}: {meta}")
        if CTA not in meta:
            raise SystemExit("cta missing " + slug)
        images = images_for(slug, line, index)
        card = f"{name} for {who}. Price on application after survey."
        if len(card) > 160:
            card = f"{name}. Price on application after survey."
        jobs[slug] = {
            "slug": slug,
            "line": line,
            "kind": row["kind"],
            "brand": brand,
            "policy": policy,
            "name": name,
            "title": title,
            "meta": meta,
            "keywords": f"{name}, {service_label}, Stockport, UK-wide",
            "h1": name,
            "kicker": service_label,
            "lede": (
                f"{name} for {who}, arranged UK-wide from Stockport. "
                f"Price on application after we have seen the site."
            ),
            "card": card,
            "paragraphs": paras,
            "points": points_for(spec),
            "faqs": faqs_for(spec),
            "images": images,
            "links": related(slug, peers_by_line[line], names, service_href, service_label),
            "service_label": service_label,
            "service_href": service_href,
            "parent_label": "Job types",
            "parent_href": "/pages/jobs",
            "form_options": [name, service_label],
            "area_served": "United Kingdom",
            "show_nap": True,
            "word_count": len(words),
        }
        blob = json.dumps(jobs[slug])
        banned_patterns = (
            r"£",
            r"\bBAFE\b",
            r"\bNSI\b",
            r"\bFIRAS\b",
            r"\bLPCB\b",
            r"\bIFC\b",
            r"Gas Safe",
            r"authorised dealer",
            r"authorized dealer",
            r"approved subcontractor",
            r"\bb\d{5}\b",
        )
        for banned in banned_patterns:
            if re.search(banned, blob, flags=re.IGNORECASE):
                raise SystemExit(f"banned {banned} in {slug}")

    # Intro uniqueness.
    firsts = [job["paragraphs"][0][:180] for job in jobs.values()]
    if len(firsts) != len(set(firsts)):
        raise SystemExit("duplicate openings")

    OUT_JSON.write_text(json.dumps(jobs, ensure_ascii=False, indent=2) + "\n")
    for row in rows:
        slug = row["slug"]
        target = JOBS_DIR / f"{slug}.php"
        if (
            slug in keywords
            and slug not in JOB_CANONICAL
            and target.is_file()
            and "nationwideP0Render" in target.read_text()
        ):
            target.unlink()
    written = 0
    for slug in jobs:
        target = JOBS_DIR / f"{slug}.php"
        target.write_text(
            "<?php\n"
            "require_once dirname(__DIR__, 2) . '/config.php';\n"
            "require_once SITE_ROOT . '/includes/nationwide-p0-jobs.php';\n"
            f"nationwideP0Render({slug!r});\n"
        )
        written += 1
    redirect = [
        row["slug"] for row in rows
        if row["slug"] in keywords and row["slug"] not in JOB_CANONICAL
    ]
    print(f"hubs={written} keyword_canonical={len(redirect)} words_min={min(j['word_count'] for j in jobs.values())} words_max={max(j['word_count'] for j in jobs.values())}")


if __name__ == "__main__":
    build()
