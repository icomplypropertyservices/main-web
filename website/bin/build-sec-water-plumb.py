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


FAMILY_META = {
    "security": {
        "label": "Security",
        "short": "CCTV, access, door entry and alarms",
        "standards": "BS EN 62676 · EN 60839 · PD 6662 / BS EN 50131",
        "audience": "landlords, managing agents and FM teams",
        "cta": "Enquire for a written POA quote — we do not invent a catalogue £ figure.",
    },
    "water": {
        "label": "Water hygiene",
        "short": "Legionella, tanks and water hygiene",
        "standards": "HSE ACOP L8 · HSG274 · COSHH / HSWA dutyholder duties",
        "audience": "landlords, dutyholders and managing agents",
        "cta": "Price on application after we know the system — POA, never a made-up fee.",
    },
    "plumbing": {
        "label": "Plumbing",
        "short": "General plumbing and landlord repairs",
        "standards": "Water Regs · WRAS fittings · isolation before works",
        "audience": "landlords, agents and occupiers",
        "cta": "Enquire with postcode and the fault — written POA after scope.",
    },
}


def synthesize(row: dict, family: str, idx: int) -> dict:
    meta = FAMILY_META[family]
    name = row["name"]
    slug = row["slug"]
    service = row["service"]
    variant = idx % 4
    titles = [
        f"{name} | {meta['label']} North West",
        f"{name} — POA | iComply Stockport",
        f"{name} | Greater Manchester & North West",
        f"{name} | {meta['short']} | iComply",
    ]
    h1s = [
        name,
        f"{name} across the North West",
        f"{name} for {meta['audience']}",
        f"{meta['label']}: {name}",
    ]
    seo_title = titles[variant]
    if len(seo_title) > 68:
        seo_title = f"{name} | {meta['label']} | iComply"
    h1 = h1s[variant]
    if name.lower() not in h1.lower():
        h1 = name
    meta_desc = (
        f"{name} for {meta['audience']} across Greater Manchester and the North West. "
        f"{meta['standards']}. {meta['cta']}"
    )
    if len(meta_desc) > 165:
        meta_desc = f"{name} across the North West. {meta['short']}. POA after scope from Stockport ({slug})."
    intro = (
        f"{name} is part of our {meta['label'].lower()} work from Stockport SK2. "
        f"We scope the job you actually have for {meta['audience']} — not a generic package — "
        f"then issue a written POA figure."
    )
    body = (
        f"{meta['short']}. For {name} that means a site look or a clear description of the property, "
        f"then a written scope. Typical North West stock includes terraces, purpose-built flats, HMOs, "
        f"offices and light industrial. {meta['standards']}. {meta['cta']} "
        f"WhatsApp and phone are on this page. Related {meta['label'].lower()} guides are linked below."
    )
    faqs = [
        [
            f"What does {name} include?",
            f"Scope is confirmed in your quote. We typically survey, agree the work and complete {name} "
            f"under {meta['short']}, then issue the notes that job actually needs.",
        ],
        [
            f"Do you cover my town for {name}?",
            "Yes across Greater Manchester, Cheshire, Lancashire and the wider North West from Stockport. "
            "Use the area links on this page or the areas hub.",
        ],
        [
            f"How do you price {name}?",
            "Price on application after we confirm access, materials and standards. "
            "We do not invent a catalogue £ figure on this page.",
        ],
        [
            f"Who is {name} for?",
            f"{meta['audience'].capitalize()} booking through iComply. Tell us the postcode, property type "
            f"and any brand already on site.",
        ],
    ]
    # Drop one FAQ by variant so neighbouring pages are not identical lists.
    faqs.pop(variant)
    focus = [
        f"Scope confirmed against {meta['standards']}",
        "Written POA quote after we know the property — no invented £",
        f"{meta['short']} from Stockport engineers",
        "Enquire by form, WhatsApp or phone on this page",
    ]
    secondaries = [f"{name} North West", f"{meta['label']} {name}", f"{name} Stockport"]
    return {
        "slug": slug,
        "name": name,
        "service": service,
        "family": family,
        "related": row.get("related") or slug,
        "seo_title": seo_title,
        "h1": h1,
        "meta_desc": meta_desc,
        "intro": intro,
        "body": body,
        "faq": faqs,
        "focus_points": focus,
        "secondaries": secondaries,
        "seo_keywords": f"{name}, {meta['label']}, North West, Stockport, POA",
    }


def uniquify(jobs: list[dict]) -> None:
    seen_title: dict[str, int] = {}
    seen_h1: dict[str, int] = {}
    seen_meta: dict[str, int] = {}
    for job in jobs:
        title = job["seo_title"]
        if title in seen_title:
            job["seo_title"] = f"{job['name']} · {job['slug']} | iComply"
        seen_title[job["seo_title"]] = 1
        h1 = job["h1"]
        if h1 in seen_h1:
            job["h1"] = f"{job['name']} ({job['slug']})"
        seen_h1[job["h1"]] = 1
        meta = job["meta_desc"]
        if meta in seen_meta:
            job["meta_desc"] = f"{job['name']} ({job['slug']}) across the North West. POA after scope from Stockport."
        seen_meta[job["meta_desc"]] = 1


def wire_related(jobs: list[dict]) -> None:
    by_fam: dict[str, list[str]] = {}
    for job in jobs:
        by_fam.setdefault(job["family"], []).append(job["slug"])
    for job in jobs:
        slugs = by_fam[job["family"]]
        others = [s for s in slugs if s != job["slug"]]
        job["related"] = others[0] if others else job["slug"]


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
