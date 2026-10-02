#!/usr/bin/env python3
"""Build Security (164) + Water (130) + Plumbing (130) = 424 job catalogue."""
from __future__ import annotations

import json
import re
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path("/workspace/website")
OUT = ROOT / "data/job-types-sec-water-plumb.json"
MASTER = ROOT / "data/job-types-master.json"
KEYWORDS = ROOT / "data/keywords.json"
EXTRAS = ROOT / "data/job-types-extras.json"

SECURITY_TARGETS = {
    "cctv": 40,
    "access-control": 36,
    "door-entry": 30,
    "intercoms": 30,
    "intruder-alarm": 28,
}  # 164
WATER_TARGET = 130
PLUMBING_TARGET = 130

WATER_NAMES = [
    "Sentinel Tap Log Setup",
    "Thermostatic Mixer Valve Check Support",
    "Water Hygiene Schematic Support",
    "Void Legionella Flush",
    "Cold Water Storage Tank Inspection",
    "Cold Water Tank Clean",
    "Loft Cold Water Tank Survey",
    "Communal Cold Water Tank Clean",
    "Tank Lid and Screen Check",
    "Tank Insulation Hygiene Check",
    "Tank Insect Screen Fit",
    "Tank Overflow Hygiene Check",
    "Sectional Tank Inspection",
    "Gravity Cold Water Tank Visit",
    "Break Tank Hygiene Inspection",
    "Feed and Expansion Tank Check",
    "Hot Water Sentinel Temperature Check",
    "Cold Water Sentinel Temperature Check",
    "Monthly Temperature Log Setup",
    "Calorifier Flow Temperature Check",
    "Calorifier Return Temperature Check",
    "Shower Temperature Hygiene Check",
    "Blended Outlet Temperature Check",
    "Thermostatic Mixer Valve Service",
    "TMV Failsafe Check",
    "Hot Water Storage Temperature Review",
    "Little Used Outlet Flush",
    "Void Property Flush Programme",
    "Weekly Flush Log Setup",
    "Shower Head Flush and Clean",
    "Dead Leg Flush Plan",
    "Stagnant Pipe Flush Visit",
    "After Holiday Let Flush",
    "Student Let Return Flush",
    "Seasonal Occupancy Flush Plan",
    "Shower Head Descale Hygiene",
    "Flexible Hose Hygiene Replace",
    "Spray Tap Hygiene Clean",
    "Outdoor Tap Isolation Hygiene",
    "Point of Use Heater Clean",
    "Legionella Sample Suite",
    "Pre Disinfection Water Sample",
    "Post Clean Water Sample",
    "Outlet Sampling Advice",
    "Water Hygiene Sample Interpretation",
    "Water System Schematic Update",
    "Water Outlet Asset Register",
    "Written Scheme of Control Support",
    "Water Hygiene Logbook Setup",
    "Responsible Person Water Briefing",
    "Tenant Flush Instruction Pack",
    "Managing Agent Water Hygiene Pack",
    "Office Water Hygiene Visit",
    "Retail Water Hygiene Visit",
    "Warehouse Water Hygiene Visit",
    "School Water Hygiene Visit",
    "Nursery Water Hygiene Visit",
    "Dental Practice Water Hygiene",
    "GP Surgery Water Hygiene",
    "Hotel Water Hygiene Visit",
    "Holiday Cottage Legionella Review",
    "Supported Housing Water Hygiene",
    "Extra Care Water Hygiene",
    "Block of Flats Water Hygiene",
    "High Rise Water Hygiene Visit",
    "Student Halls Water Hygiene",
    "New Tenancy Legionella Review",
    "Change of Occupancy Water Check",
    "Portfolio Water Hygiene Programme",
    "HMO Licence Water Hygiene Evidence",
    "Landlord Tank Photograph Pack",
    "Agent Water Hygiene Reminder Pack",
    "Calorifier Inspection Support",
    "Showerhead Clean Programme",
    "Expansion Vessel Hygiene Advice",
    "Water Softener Hygiene Liaison",
    "Instantaneous Heater Outlet Clean",
    "Legionella Risk Assessment Review",
    "Legionella RA After Alteration",
    "Legionella RA After Tank Change",
    "Legionella RA After Void Works",
    "Two Year Legionella Review",
    "Significant Findings Water Close Out",
    "Insurer Water Hygiene Evidence Pack",
    "Chlorination Support Advice",
    "Tank Disinfection Liaison",
    "Post Disinfection Flush",
    "Biocide Dosing Advice",
    "Communal Riser Water Hygiene",
    "Plant Room Water Hygiene Walk",
    "Booster Set Hygiene Advice",
    "Pressure Vessel Drain Advice",
    "Dead Leg Identification Survey",
    "Unused En Suite Flush Plan",
    "Loft Tank Ballvalve Hygiene",
    "Cold Water Storage Photograph Record",
    "Showers After Long Void Clean",
    "Care Home Sentinel Log Training",
    "HMO Shower Head Replace Programme",
    "Landlord Little Used Room Flush",
    "Retail Staff Shower Hygiene",
    "Warehouse Welfare Water Hygiene",
    "Site Cabin Water Hygiene Advice",
    "Temporary Building Water Hygiene",
    "New Build Water Hygiene Handover",
    "After Refurb Water Hygiene Visit",
    "After Leak Repair Flush",
    "After Pipe Alteration Review",
    "Combination Boiler Low Risk Review",
    "Stored Water High Risk Review",
    "Vulnerable Occupant Water Review",
    "Spa Bath Hygiene Advice",
    "Feature Fountain Hygiene Advice",
    "Irrigation Isolation Hygiene",
    "Outside Tap Dead Leg Advice",
    "Hose Union Bib Tap Review",
    "Drinking Water Point Hygiene",
    "Tea Point Outlet Flush",
    "Disabled WC Outlet Hygiene",
    "Communal Laundry Water Hygiene",
    "Student Kitchen Tap Flush Plan",
    "Void Flat Tank and Outlet Check",
    "Managing Agent L8 Briefing",
    "Dutyholder Water Hygiene Pack",
    "Temperature Probe Kit Setup",
    "Logbook Photo Evidence Visit",
    "Water Hygiene After Power Cut",
    "Frost Damage Tank Hygiene Check",
    "Overflow Warning Pipe Check",
    "Tank Vent Screen Replace",
    "Lid Seal Replace on Tank",
    "Cross Connection Hygiene Advice",
    "Hot Water Circulation Review",
    "Secondary Return Temperature Check",
    "Calorifier Drain Point Check",
    "Shower Hose Replace Landlord Pack",
    "HMO Bathroom Hygiene Visit",
    "Landlord Portfolio Tank Survey",
    "Block Cold Water Tank Access Survey",
]

PLUMBING_NAMES = [
    "Washing Machine Plumbing",
    "Dishwasher Connection",
    "Stopcock Replacement",
    "Overflow Repair",
    "Cylinder Drain Down Support",
    "Bathroom Waste Replacement",
    "Kitchen Waste Trap",
    "Outside Tap With Isolation",
    "Shower Mixer Replacement",
    "Frozen Pipe Thaw Support",
    "Landlord Plumbing Call Out",
    "Condensate Pipe Re-run",
    "WC Fill Valve Replace",
    "Immersion Heater Replace",
    "Cold Water Storage Lid",
    "Shower Waste Pump",
    "Kitchen Mixer Replace",
    "WC Pan Connector",
    "Burst Pipe Repair",
    "No Water Call Out",
    "Low Water Pressure Visit",
    "Internal Stop Tap Replace",
    "Rising Main Repair",
    "Leaking Tap Repair",
    "Dripping Mixer Tap",
    "Basin Tap Replacement",
    "Bath Tap Replacement",
    "Outdoor Tap Isolation",
    "Washing Machine Leak Repair",
    "Dishwasher Leak Repair",
    "Toilet Repair",
    "Toilet Cistern Repair",
    "WC Flush Mechanism",
    "Blocked Toilet Clearance",
    "Blocked Sink Unblock",
    "Blocked Bath Waste",
    "Blocked Shower Waste",
    "Soil Stack Repair",
    "Waste Pipe Replacement",
    "Bottle Trap Replace",
    "Electric Shower Isolation Support",
    "Shower Tray Waste",
    "Shower Valve Service",
    "Bath Waste Replace",
    "Basin Waste Replace",
    "Overflow Drip Trace",
    "Loft Tank Ballvalve",
    "Ballvalve Replacement",
    "Header Tank Repair",
    "Gravity Hot Water Fault",
    "Combi No Hot Water Plumbing",
    "Condensate Blockage Clear",
    "Radiator Valve Replace",
    "TRV Replacement Plumbing",
    "Leaking Radiator Valve",
    "Pipework Trace and Repair",
    "Copper Pipe Repair",
    "Plastic Pipe Repair",
    "Underfloor Leak Trace",
    "Ceiling Leak Trace",
    "Void Property Plumbing Check",
    "HMO Bathroom Plumbing",
    "Tenant Damage Plumbing Repair",
    "End of Tenancy Plumbing Snag",
    "Landlord Leak Call Out",
    "Emergency Leak Isolation",
    "After Hours Plumber Visit",
    "Flexible Hose Replace",
    "Appliance Isolation Valve",
    "Garden Tap Repair",
    "Frost Damage Pipe Repair",
    "Lead Pipe Advice",
    "Seized Stop Tap Free",
    "Mains Water Leak Liaison",
    "Internal Mains Repair",
    "Kitchen Sink Install Plumbing",
    "Utility Sink Plumbing",
    "Disabled WC Plumbing Support",
    "Urinal Outlet Repair",
    "Commercial Washroom Plumbing",
    "Office Kitchen Plumbing",
    "Care Home Outlet Repair",
    "School Washroom Plumbing",
    "Retail Staff Toilet Repair",
    "Landlord Portfolio Plumbing Visit",
    "Making Good After Leak",
    "Floorboard Lift for Leak",
    "Boxing In Pipework",
    "Pipe Insulation After Repair",
    "Pressure Reducing Valve",
    "Check Valve Replace",
    "Scale Build Up Tap Service",
    "Kitchen Mixer Install",
    "Monobloc Tap Install",
    "Pillar Tap Replace",
    "Thermostatic Shower Install",
    "Power Shower Plumbing",
    "Saniflo Fault Support",
    "Macerator Repair Support",
    "Towel Rail Plumbing",
    "Bathroom Radiator Plumbing",
    "Second Bathroom First Fix",
    "Loft Conversion Plumbing First Fix",
    "Extension Plumbing First Fix",
    "Kitchen Relocation Plumbing",
    "Appliance Reconnect After Move",
    "Tenant Left Taps Dripping",
    "No Hot Water Landlord Visit",
    "Communal Bin Store Tap",
    "Washer Replace Visit",
    "Isolation Valve Seized Free",
    "Hot Water Cylinder Plumbing Support",
    "Unvented Cylinder Plumbing Support",
    "Immersion Isolate and Replace",
    "Overflow Warning Pipe Repair",
    "Bath Mixer Replace",
    "Shower Diverter Repair",
    "Plastic Pushfit Repair",
    "Compression Joint Remake",
    "Leaking Stopcock Gland",
    "Outside Tap Vacuum Breaker",
    "Hose Union Bib Tap Install",
    "Washing Machine Standpipe",
    "Dishwasher Waste Connection",
    "Kitchen Waste Disposal Support",
    "Utility Room First Fix Plumbing",
    "En Suite Waste Alteration",
    "Loft Tank Overflow Repair",
    "Header Tank Ballvalve Service",
    "Landlord Void Leak Sweep",
    "HMO Shared Bathroom Plumbing",
    "Student Let Plumbing Call Out",
    "Holiday Let Plumbing Repair",
    "Care Home Basin Replace Support",
    "Office Tea Point Plumbing",
    "Warehouse Welfare Plumbing",
    "Retail Customer Toilet Repair",
    "Blocked Soil Vent Pipe",
    "Gulley Trap Clearance",
    "Yard Gully Plumbing Repair",
    "Rainwater Pipe Internal Leak",
    "Condensate Trap Clean",
    "Boiler Condensate Re-route",
    "No Cold Water To Kitchen",
    "No Cold Water To Bathroom",
    "Intermittent Water Pressure",
    "Airlocked Hot Water Circuit",
    "Draining Down for Works",
    "Refill and Vent After Works",
    "Tenant Reported Leak Visit",
    "Managing Agent Plumbing Call Out",
    "Emergency Isolation After Burst",
    "Ceiling Stain Leak Trace",
    "Neighbour Leak Investigation",
    "Meter Side Leak Liaison",
    "Internal Stopcock Box Repair",
    "Wall Plate Elbow Replace",
    "Flexible Connector Burst Repair",
    "Toilet Seat and Pan Fixing",
    "Concealed Cistern Repair",
    "Furniture Bathroom Plumbing",
    "Pedestal Basin Replumb",
    "Vanity Unit Plumbing",
    "Shower Screen Tray Seal Plumbing",
    "Bath Panel Access Plumbing",
    "Under Sink Filter Install Support",
    "Drinking Tap Install",
    "Boiling Tap Isolate Support",
    "Quooker Style Tap Isolate",
    "Landlord Tap Upgrade Pack",
    "HMO Kitchen Tap Replace",
    "Void Stopcock Service",
    "New Tenancy Plumbing Check",
    "Change of Tenancy Leak Test",
    "Portfolio Reactive Plumbing",
    "Same Day Leak Call Out",
    "Out of Hours Isolation Visit",
]


def slugify(name: str) -> str:
    s = re.sub(r"[^a-z0-9]+", "-", name.lower())
    return s.strip("-")


def load_json(path: Path, default):
    if not path.is_file():
        return default
    return json.loads(path.read_text())


def all_known_jobs() -> dict[str, dict]:
    out: dict[str, dict] = {}
    master = load_json(MASTER, {})
    for row in master.get("jobs") or []:
        slug = slugify(str(row.get("slug") or ""))
        if slug:
            out[slug] = {
                "slug": slug,
                "name": str(row.get("name") or slug),
                "service": str(row.get("service") or ""),
                "related": str(row.get("related") or slug),
            }
    extras = load_json(EXTRAS, {})
    for row in extras.get("jobs") or []:
        slug = slugify(str(row.get("slug") or row.get("name") or ""))
        if slug and slug not in out:
            out[slug] = {
                "slug": slug,
                "name": str(row.get("name") or slug),
                "service": str(row.get("service") or ""),
                "related": str(row.get("related") or slug),
            }
    keywords = load_json(KEYWORDS, {})
    for slug, meta in keywords.items():
        if not isinstance(meta, dict):
            continue
        sl = slugify(str(slug))
        if sl and sl not in out:
            out[sl] = {
                "slug": sl,
                "name": str(meta.get("name") or sl),
                "service": str(meta.get("service") or ""),
                "related": str(meta.get("related") or sl),
            }
        elif sl in out and not out[sl].get("name"):
            out[sl]["name"] = str(meta.get("name") or out[sl]["name"])
    return out


def score_security(row: dict, in_keywords: set[str]) -> int:
    slug = row["slug"]
    name = row["name"].lower()
    hay = f"{slug} {name}"
    score = 0
    if slug in in_keywords:
        score += 50
    for term, pts in (
        ("install", 12),
        ("repair", 10),
        ("maintenance", 10),
        ("system", 8),
        ("gate", 14),
        ("barrier", 12),
        ("anpr", 10),
        ("intercom", 8),
        ("cctv", 8),
        ("intruder", 8),
        ("door-entry", 8),
        ("door entry", 8),
        ("access", 6),
        ("paxton", 8),
        ("video", 6),
        ("gsm", 6),
        ("landlord", 8),
        ("commercial", 5),
        ("near-me", -8),
        ("engineers-near-me", -10),
    ):
        if term in hay:
            score += pts
    return score


def pick_security(known: dict[str, dict], keywords: dict) -> list[dict]:
    in_kw = set(slugify(s) for s in keywords.keys())
    by_svc: dict[str, list[dict]] = {s: [] for s in SECURITY_TARGETS}
    for row in known.values():
        svc = row["service"]
        if svc in by_svc:
            by_svc[svc].append(row)
    picked: list[dict] = []
    used: set[str] = set()
    for svc, need in SECURITY_TARGETS.items():
        ranked = sorted(
            by_svc[svc],
            key=lambda r: (-score_security(r, in_kw), r["slug"]),
        )
        take = []
        for row in ranked:
            if row["slug"] in used:
                continue
            take.append(row)
            used.add(row["slug"])
            if len(take) >= need:
                break
        if len(take) < need:
            raise SystemExit(f"Only {len(take)} security jobs for {svc}, need {need}")
        picked.extend(take)
    return picked


def add_named_jobs(
    names: list[str],
    service: str,
    known: dict[str, dict],
    used: set[str],
    target: int,
    existing: list[dict],
) -> list[dict]:
    out = list(existing)
    for row in existing:
        used.add(row["slug"])
    for name in names:
        if len(out) >= target:
            break
        slug = slugify(name)
        if not slug or slug in used:
            continue
        if slug in known and known[slug]["service"] not in {service, ""}:
            # Already a different trade — skip to avoid hijacking.
            continue
        if slug in known:
            row = dict(known[slug])
            row["service"] = service
            row["name"] = known[slug].get("name") or name
        else:
            row = {"slug": slug, "name": name, "service": service, "related": slug}
        out.append(row)
        used.add(slug)
    if len(out) < target:
        raise SystemExit(f"{service} only reached {len(out)}, need {target}")
    return out[:target]


def pick_water(known: dict[str, dict], used: set[str]) -> list[dict]:
    existing = []
    for row in known.values():
        hay = f"{row['slug']} {row['name'].lower()} {row['service']}"
        if row["service"] == "legionella-risk-assessment" or "legionella" in hay or "water-hygiene" in hay or "water hygiene" in hay:
            if row["slug"] not in used:
                existing.append(dict(row))
    existing.sort(key=lambda r: r["slug"])
    return add_named_jobs(WATER_NAMES, "legionella-risk-assessment", known, used, WATER_TARGET, existing)


def pick_plumbing(known: dict[str, dict], used: set[str]) -> list[dict]:
    existing = [dict(r) for r in known.values() if r["service"] == "plumbing" and r["slug"] not in used]
    existing.sort(key=lambda r: r["slug"])
    return add_named_jobs(PLUMBING_NAMES, "plumbing", known, used, PLUMBING_TARGET, existing)


# Service voice is specific. Family-wide paragraphs made 424 near-duplicates.
SERVICE_BRIEF = {
    "cctv": {
        "label": "CCTV",
        "work": "camera positions, recording and who can view the pictures",
        "standard": "BS EN 62676",
        "ask": "the doors or car park, and any NVR or cloud recorder already on site",
    },
    "access-control": {
        "label": "Access control",
        "work": "which doors lock, the reader or token, and who is allowed through",
        "standard": "BS EN 60839",
        "ask": "the doors, the token type and any controller already fitted",
    },
    "door-entry": {
        "label": "Door entry",
        "work": "the entrance panel, the handsets and how the door is released",
        "standard": "BS EN 62820",
        "ask": "audio or video, how many flats, and the lock release",
    },
    "intercoms": {
        "label": "Intercom",
        "work": "the call point, the answer point and the door or gate release",
        "standard": "BS EN 62820",
        "ask": "audio or video, the cable or IP run, and the lock or gate",
    },
    "intruder-alarm": {
        "label": "Intruder alarm",
        "work": "detection, the panel and how the system is set and unset",
        "standard": "PD 6662 / BS EN 50131",
        "ask": "the panel make, how it signals, and which rooms are covered",
    },
    "legionella-risk-assessment": {
        "label": "Water hygiene",
        "work": "outlets, stored water and the temperatures or flush the scheme needs",
        "standard": "HSE ACOP L8 and HSG274",
        "ask": "stored or mains water, the outlets in use, and who the dutyholder is",
    },
    "plumbing": {
        "label": "Plumbing",
        "work": "isolation, the fitting that has failed, and a repair that holds",
        "standard": "Water Regulations and WRAS fittings",
        "ask": "the postcode, what is leaking or blocked, and whether you can isolate",
    },
}

FAMILY_DEFAULT_VERB = {
    "security": ("system check", "check what is already installed", "a note of what the system still does"),
    "water": ("water hygiene visit", "review the outlets and any stored water", "a written note against the scheme"),
    "plumbing": ("plumbing visit", "find the fault and make it safe", "the fitting isolated and the repair agreed"),
}

# Longer needles first so "maintenance-contract" beats "maintenance".
VERB_RULES = [
    ("maintenance-contract", "maintenance contract", "agree the visit schedule and test the system", "a schedule and a written fault log"),
    ("maintenance", "maintenance", "test what is already there and log the faults", "a test record and a list of faults"),
    ("servicing", "servicing", "service the equipment and note what was adjusted", "a service note and any parts that need replacing"),
    ("repair", "repair", "find the fault and repair it", "a repaired system and a note of the fault"),
    ("installation", "installation", "install and test it", "the equipment fitted, tested and handed over"),
    ("install", "installation", "install and test it", "the equipment fitted, tested and handed over"),
    ("upgrade", "upgrade", "upgrade what is already there", "a list of what stays and what is replaced"),
    ("replacement", "replacement", "replace the failed part", "the old part removed and the new one tested"),
    ("replace", "replacement", "replace the failed part", "the old part removed and the new one tested"),
    ("design", "design", "design it before any install", "a layout you can price from"),
    ("certification", "certification", "check what a certificate would cover", "a note of what is covered and what is not"),
    ("compliance", "compliance", "check it against the standard that applies", "a note of the gaps"),
    ("programming", "programming", "programme the users or tokens", "a record of who can get in"),
    ("flush", "flush", "flush the outlets that the scheme names", "a note of which outlets were run and for how long"),
    ("temperature", "temperature check", "take the temperatures the scheme asks for", "the readings, including any outlets out of range"),
    ("inspection", "inspection", "inspect it and record the condition", "a note of what was seen and the next step"),
    ("survey", "survey", "survey access and condition", "access, condition and the next step"),
    ("clean", "clean", "clean the part the scheme names", "a note of what was cleaned and any follow-up"),
    ("descal", "descale", "descale the outlet", "a note of which outlets were descaled"),
    ("sample", "sampling", "take the samples the scheme names", "a note of where the samples were taken"),
    ("isolation", "isolation", "isolate it so the rest of the system can stay on", "a note of what was isolated and how to restore it"),
    ("unblock", "clearance", "clear the blockage", "a note of where the blockage was"),
    ("clearance", "clearance", "clear the blockage", "a note of where the blockage was"),
    ("blocked", "clearance", "clear the blockage", "a note of where the blockage was"),
    ("leak", "leak trace", "trace the leak before opening up", "a note of where the water is coming from"),
    ("thaw", "thaw", "thaw the frozen pipe and protect it", "a note of which pipes were frozen"),
    ("drain", "drain down", "drain down and refill", "what was drained and how it was refilled"),
    ("training", "training", "show the team how to keep the log", "who was shown the log and what they record"),
    ("setup", "setup", "set the log or kit up", "the log or kit in place and who owns it"),
    ("liaison", "liaison", "liaise with the other party", "who else is involved and what we need from them"),
    ("call-out", "call-out", "attend and make it safe", "the fault made safe and the follow-up agreed"),
    ("call out", "call-out", "attend and make it safe", "the fault made safe and the follow-up agreed"),
    ("advice", "advice", "advise on the practical next step", "a written note of the risk and the next step"),
    ("review", "review", "review what changed", "a note of what changed and what to do next"),
    ("support", "support", "support the system that is already there", "a note of what we will take on and what we will not"),
]

# Longer place names first so "care-home" beats a later generic token.
SETTING_RULES = [
    ("care-home", "a care home"),
    ("block-of-flats", "a block of flats"),
    ("student-hall", "student halls"),
    ("student-let", "a student let"),
    ("student-kitchen", "a student kitchen"),
    ("holiday-cottage", "a holiday cottage"),
    ("holiday-let", "a holiday let"),
    ("supported-housing", "supported housing"),
    ("extra-care", "extra care housing"),
    ("gp-surgery", "a GP surgery"),
    ("dental", "a dental practice"),
    ("high-rise", "a high-rise"),
    ("change-of-occupancy", "a change of occupancy"),
    ("change-of-tenancy", "a change of tenancy"),
    ("end-of-tenancy", "an end of tenancy"),
    ("new-tenancy", "a new tenancy"),
    ("new-build", "a new build"),
    ("managing-agent", "a managing agent instruction"),
    ("site-cabin", "a site cabin"),
    ("temporary-building", "a temporary building"),
    ("disabled-wc", "an accessible WC"),
    ("tea-point", "a tea point"),
    ("plant-room", "a plant room"),
    ("car-park", "a car park"),
    ("en-suite", "an en suite"),
    ("landlord", "a landlord property"),
    ("commercial", "a commercial site"),
    ("apartment", "an apartment block"),
    ("warehouse", "a warehouse"),
    ("communal", "a communal system"),
    ("portfolio", "a property portfolio"),
    ("kitchen", "a kitchen"),
    ("bathroom", "a bathroom"),
    ("retail", "a retail unit"),
    ("office", "an office"),
    ("school", "a school"),
    ("nursery", "a nursery"),
    ("hotel", "a hotel"),
    ("void", "a void property"),
    ("tenant", "a tenanted home"),
    ("loft", "a loft"),
    ("outdoor", "an outdoor tap"),
    ("outside", "an outside tap"),
    ("garden", "a garden tap"),
    ("gate", "a gate"),
    ("hmo", "an HMO"),
]

HINGE_STOP = {
    "and", "the", "for", "with", "after", "from", "visit", "support", "check", "system",
    "pack", "north", "west", "advice", "review", "plan", "programme", "program",
    "installation", "install", "repair", "maintenance", "servicing", "upgrade",
    "replacement", "replace", "design", "certification", "compliance", "setup",
    "clean", "flush", "inspection", "survey", "sample", "sampling", "training",
    "liaison", "call", "out", "service", "contract", "works", "blocked", "clearance",
    "plumbing", "temperature", "cctv", "access", "control", "door", "entry",
    "intruder", "alarm", "water", "hygiene", "intercom", "legionella",
}


def _hay(row: dict) -> str:
    return f"{row['slug']} {row['name'].lower()}"


def _verb(row: dict, family: str) -> tuple[str, str, str]:
    hay = _hay(row)
    for needle, noun, action, deliverable in VERB_RULES:
        if needle in hay:
            return noun, action, deliverable
    return FAMILY_DEFAULT_VERB[family]


def _setting(row: dict) -> str:
    slug = row["slug"]
    for needle, phrase in SETTING_RULES:
        if needle in slug:
            return phrase
    return ""


def _article(phrase: str) -> str:
    return "an" if phrase[:1].lower() in "aeiou" else "a"


def _hinge(name: str, setting: str) -> str:
    """Subject of the job, with the trade, the verb and the setting taken out."""
    skip = set(HINGE_STOP)
    skip.update(re.findall(r"[a-z0-9]+", setting.lower()))
    raw = re.findall(r"[A-Za-z0-9]+", name)
    original = {w.lower(): w for w in raw}
    words = [w for w in raw if w.lower() not in skip]
    if not words:
        return ""
    # A hinge that repeats the whole name is deleted when the name is removed,
    # so keep one or two subject words instead.
    while len(words) > 1 and " ".join(words).lower() == name.lower():
        words = words[:1]
    if " ".join(words).lower() == name.lower():
        return ""
    if len(words) > 2:
        words = words[:2]
    kept = []
    for word in words:
        src = original.get(word.lower(), word)
        kept.append(src if src.isupper() and len(src) > 1 else word.lower())
    return " ".join(kept)


def _fit_title(name: str, label: str) -> str:
    titled = f"{name} | {label} | iComply"
    if len(titled) <= 65:
        return titled
    short = f"{name} | iComply"
    if len(short) <= 65:
        return short
    return name


def _fit_meta(name: str, verb_noun: str, setting: str, standard: str) -> str:
    where = setting if setting else "the North West"
    meta = f"{name}: {verb_noun} for {where}. {standard}. Written POA from Stockport — no catalogue £."
    if len(meta) <= 160:
        return meta
    meta = f"{name}. {standard}. Written POA after scope, from Stockport."
    if len(meta) <= 160:
        return meta
    trimmed = f"{name}. POA after scope. {standard}."
    if len(trimmed) <= 160:
        return trimmed
    return trimmed[:157].rsplit(" ", 1)[0] + "."


def synthesize(row: dict, family: str, idx: int) -> dict:
    del idx  # copy follows the job, not a rotating family template
    brief = SERVICE_BRIEF[row["service"]]
    name = row["name"]
    slug = row["slug"]
    verb_noun, verb_action, deliverable = _verb(row, family)
    setting = _setting(row)
    hinge = _hinge(name, setting)
    where = f" for {setting}" if setting else ""
    subject = hinge or setting or brief["label"].lower()
    scope_line = (
        f"The part that changes this job is {hinge}, confirmed on site before a written POA."
        if hinge
        else "We confirm that scope on site, then issue a written POA."
    )
    intro = (
        f"{name} is booked from our Stockport team{where}. "
        f"This is {brief['label']} work. "
        f"On the visit we {verb_action}. "
        f"{scope_line}"
    )
    body = (
        f"On {_article(name)} {name} visit we {verb_action}. "
        f"You get {deliverable}. "
        f"We ask about {brief['ask']}{where}. "
        f"Scope is checked against {brief['standard']}. "
        f"The quote is price on application after that scope — this page does not invent a catalogue £ figure."
    )
    faqs = [
        [
            f"What happens on {_article(name)} {name} visit?",
            f"We {verb_action}. The part that changes the scope is {subject}. "
            f"You leave with {deliverable}. The figure is POA after we know {brief['ask']}.",
        ],
        [
            f"Where do you cover for {name}?",
            "Greater Manchester, Cheshire, Lancashire and the wider North West, from Stockport"
            + (f", including {setting}" if setting else "")
            + f". Tell us the postcode and mention {subject}. Area links on this page go to the towns we export.",
        ],
        [
            f"How do you price {name}?",
            f"Price on application. We need {brief['ask']} before a written POA. "
            f"Nothing on this {brief['label']} page is a catalogue £ figure.",
        ],
    ]
    focus = [
        f"{brief['standard']} applied to {subject}",
        f"You get {deliverable}",
        "Written POA after scope — no invented £",
        f"Enquire on this page for {brief['label'].lower()}{where or ' across the North West'}",
    ]
    secondaries = [
        f"{subject} {brief['label']}",
        f"{name} Stockport",
        f"{verb_noun} {brief['label']} North West",
    ]
    return {
        "slug": slug,
        "name": name,
        "service": row["service"],
        "family": family,
        "related": slug,
        "seo_title": _fit_title(name, brief["label"]),
        "h1": name,
        "meta_desc": _fit_meta(name, verb_noun, setting, brief["standard"]),
        "intro": intro,
        "body": body,
        "faq": faqs,
        "focus_points": focus,
        "secondaries": secondaries,
        "seo_keywords": f"{name}, {brief['label']}, {verb_noun}, North West, Stockport, POA",
    }


def _readable_slug(slug: str) -> str:
    return slug.replace("-", " ")


def uniquify(jobs: list[dict]) -> None:
    """Break the rare full-string collisions without stuffing a raw slug into every meta."""

    def claim(seen: dict[str, str], value: str, slug: str) -> bool:
        if value not in seen:
            seen[value] = slug
            return True
        return False

    seen_title: dict[str, str] = {}
    seen_h1: dict[str, str] = {}
    seen_meta: dict[str, str] = {}
    seen_intro: dict[str, str] = {}
    seen_body: dict[str, str] = {}
    seen_faq: dict[str, str] = {}
    for job in jobs:
        slug = job["slug"]
        label = _readable_slug(slug)
        if not claim(seen_title, job["seo_title"], slug):
            titled = label[:1].upper() + label[1:]
            job["seo_title"] = f"{titled} | iComply"
            claim(seen_title, job["seo_title"], slug)
        if not claim(seen_h1, job["h1"], slug):
            job["h1"] = label[:1].upper() + label[1:]
            if not claim(seen_h1, job["h1"], slug):
                job["h1"] = f"{job['name']} ({label})"
                claim(seen_h1, job["h1"], slug)
        if not claim(seen_meta, job["meta_desc"], slug):
            job["meta_desc"] = f"{job['name']} ({label}). Written POA after scope from Stockport."
            claim(seen_meta, job["meta_desc"], slug)
        if not claim(seen_intro, job["intro"], slug):
            job["intro"] = job["intro"] + f" This page is the {label} visit."
            claim(seen_intro, job["intro"], slug)
        if not claim(seen_body, job["body"], slug):
            job["body"] = job["body"] + f" Ask for the {label} visit when you enquire."
            claim(seen_body, job["body"], slug)
        faq_key = json.dumps(job["faq"], ensure_ascii=False)
        if not claim(seen_faq, faq_key, slug):
            job["faq"] = [
                [f"What happens on the {label} visit?", job["faq"][0][1]],
                [f"Where do you cover for the {label} visit?", job["faq"][1][1]],
                [f"How do you price the {label} visit?", job["faq"][2][1]],
            ]
            claim(seen_faq, json.dumps(job["faq"], ensure_ascii=False), slug)


def wire_related(jobs: list[dict]) -> None:
    """Point each page at the next job in the same service, not one shared slug."""
    by_service: dict[str, list[str]] = {}
    for job in jobs:
        by_service.setdefault(job["service"], []).append(job["slug"])
    index = {job["slug"]: job for job in jobs}
    for slugs in by_service.values():
        ordered = sorted(slugs)
        count = len(ordered)
        for i, slug in enumerate(ordered):
            index[slug]["related"] = ordered[(i + 1) % count] if count > 1 else slug


def main() -> None:
    known = all_known_jobs()
    keywords = load_json(KEYWORDS, {})
    used: set[str] = set()
    security = pick_security(known, keywords)
    for row in security:
        used.add(row["slug"])
    water = pick_water(known, used)
    plumbing = pick_plumbing(known, used)

    jobs: list[dict] = []
    for i, row in enumerate(security):
        jobs.append(synthesize(row, "security", i))
    for i, row in enumerate(water):
        jobs.append(synthesize(row, "water", i))
    for i, row in enumerate(plumbing):
        jobs.append(synthesize(row, "plumbing", i))
    uniquify(jobs)
    wire_related(jobs)

    fam_counts = {"security": 0, "water": 0, "plumbing": 0}
    for job in jobs:
        fam_counts[job["family"]] += 1
    assert fam_counts["security"] == 164, fam_counts
    assert fam_counts["water"] == 130, fam_counts
    assert fam_counts["plumbing"] == 130, fam_counts
    assert len({j["slug"] for j in jobs}) == 424
    assert len({j["seo_title"] for j in jobs}) == 424
    assert len({j["h1"] for j in jobs}) == 424
    assert len({j["meta_desc"] for j in jobs}) == 424
    assert len({j["intro"] for j in jobs}) == 424
    assert len({j["body"] for j in jobs}) == 424
    related_counts: dict[str, int] = {}
    for job in jobs:
        related_counts[job["related"]] = related_counts.get(job["related"], 0) + 1
        assert job["related"] != job["slug"]
        blob = job["meta_desc"] + job["intro"] + job["body"]
        assert not re.search(r"£\s*\d", blob)
        assert "Fixed-price" not in blob and "Fixed quotes" not in blob
    assert max(related_counts.values()) == 1, max(related_counts.values())

    def _norm(text: str, name: str) -> str:
        return re.sub(r"\s+", " ", re.sub(re.escape(name), "", text, flags=re.I)).strip()

    intro_norms: dict[str, int] = {}
    for job in jobs:
        key = _norm(job["intro"], job["name"])
        intro_norms[key] = intro_norms.get(key, 0) + 1
    worst = max(intro_norms.values())
    assert worst <= 8, worst
    assert len(intro_norms) >= 80, len(intro_norms)

    payload = {
        "count": 424,
        "families": fam_counts,
        "generated": datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%S+00:00"),
        "jobs": jobs,
    }
    OUT.write_text(json.dumps(payload, indent=4, ensure_ascii=False) + "\n")
    print(f"Wrote {len(jobs)} jobs → {OUT}")
    print(f"security={fam_counts['security']} water={fam_counts['water']} plumbing={fam_counts['plumbing']}")


if __name__ == "__main__":
    main()
