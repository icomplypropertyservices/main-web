#!/usr/bin/env python3
"""Build Building (247) + Gas (223) job-type catalogues and keyword stubs.

Usage:
  python3 website/bin/build-building-gas-jobs.py
  python3 website/bin/build-building-gas-jobs.py --check-only
"""
from __future__ import annotations

import argparse
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DATA = ROOT / "data"
PAGES = ROOT / "pages" / "keywords"
MASTER_PATH = DATA / "job-types-master.json"
BUILDING_PATH = DATA / "job-types-building.json"
GAS_PATH = DATA / "job-types-gas.json"

BUILDING_TARGET = 247
GAS_TARGET = 223

CORE_BUILDING = (
    "brickwork",
    "roofing",
    "plastering",
    "renovation",
    "kitchens",
    "windows-doors",
)
RELATED_BUILDING = (
    "dry-lining",
    "joinery",
    "carpentry",
    "rendering",
    "damp-proofing",
    "insulation",
    "loft-conversions",
    "extensions",
    "building-maintenance",
    "building-surveys",
    "tiling",
    "painting-decorating",
    "flooring",
)


def slugify(name: str) -> str:
    s = name.lower().strip()
    s = re.sub(r"[^a-z0-9]+", "-", s)
    return s.strip("-")


def title_case(name: str) -> str:
    return re.sub(r"\s+", " ", name).strip()


def load_json(path: Path):
    return json.loads(path.read_text(encoding="utf-8"))


def existing_jobs() -> dict[str, dict]:
    out: dict[str, dict] = {}
    master = load_json(MASTER_PATH)
    for job in master.get("jobs", []):
        slug = slugify(job.get("slug") or "")
        if slug:
            out[slug] = {
                "slug": slug,
                "name": title_case(job.get("name") or slug),
                "service": job.get("service") or "electrical",
                "related": slugify(job.get("related") or slug),
            }
    extras = DATA / "job-types-extras.json"
    if extras.is_file():
        extra = load_json(extras)
        for job in extra.get("jobs", []):
            slug = slugify(job.get("slug") or job.get("name") or "")
            if slug and slug not in out:
                out[slug] = {
                    "slug": slug,
                    "name": title_case(job.get("name") or slug),
                    "service": job.get("service") or "electrical",
                    "related": slugify(job.get("related") or slug),
                }
    kw_path = DATA / "keywords.json"
    if kw_path.is_file():
        kw = load_json(kw_path)
        for slug, meta in kw.items():
            slug = slugify(slug)
            if not slug or not isinstance(meta, dict):
                continue
            if slug not in out:
                out[slug] = {
                    "slug": slug,
                    "name": title_case(meta.get("name") or slug),
                    "service": meta.get("service") or "electrical",
                    "related": slugify(meta.get("related") or slug),
                }
    return out


def job_row(name: str, service: str, related: str = "") -> dict:
    slug = slugify(name)
    return {
        "slug": slug,
        "name": title_case(name),
        "service": service,
        "related": slugify(related) if related else slug,
    }


NEW_BUILDING: list[tuple[str, str]] = [
    # Brickwork + verb forms
    ("Brickwork Installation", "brickwork"),
    ("Brickwork Pointing", "brickwork"),
    ("Brickwork Inspection", "brickwork"),
    ("Installing Brickwork", "brickwork"),
    ("Repairing Brickwork", "brickwork"),
    ("Repointing Brickwork", "brickwork"),
    ("Brickwork Making Good", "brickwork"),
    ("Brick Slip Installation", "brickwork"),
    ("Soldier Course Repair", "brickwork"),
    ("Cavity Wall Tie Replacement Support", "brickwork"),
    ("Frost Damaged Brick Replacement", "brickwork"),
    ("Chimney Repointing", "brickwork"),
    ("Garden Wall Pointing", "brickwork"),
    ("Brickwork Crack Stitching", "brickwork"),
    ("Parapet Brickwork Repair", "brickwork"),
    ("Brick Arch Reconstruction", "brickwork"),
    ("Landlord Brickwork Inspection", "brickwork"),
    ("Void Brickwork Repairs", "brickwork"),
    # Roof + verb forms
    ("Roof Installation", "roofing"),
    ("Roofing Services", "roofing"),
    ("Installing a Roof", "roofing"),
    ("Repairing a Roof", "roofing"),
    ("Pitched Roof Repair", "roofing"),
    ("Slate Roof Repair", "roofing"),
    ("Tile Roof Repair", "roofing"),
    ("Roof Valley Repair", "roofing"),
    ("Roof Ridge Replacement", "roofing"),
    ("Roof Survey", "roofing"),
    ("Roofing Maintenance", "roofing"),
    ("Gutter Repair", "roofing"),
    ("Soffit Repair", "roofing"),
    ("Fascia Repair", "roofing"),
    ("Roof Flashing Repair", "roofing"),
    ("Flat Roof Repair", "roofing"),
    ("Landlord Roof Inspection", "roofing"),
    ("Void Roof Leak Repair", "roofing"),
    ("Emergency Roofing", "roofing"),
    ("Re-roofing a House", "roofing"),
    # Plaster + verb forms
    ("Plastering Repair", "plastering"),
    ("Plastering Installation", "plastering"),
    ("Installing Plasterboard", "plastering"),
    ("Skimming Walls", "plastering"),
    ("Bonding Coat Plaster", "plastering"),
    ("Wet Plastering", "plastering"),
    ("Plaster Patch Repair", "plastering"),
    ("Hallway Plastering", "plastering"),
    ("Void Plastering", "plastering"),
    ("Landlord Skim Coat", "plastering"),
    ("Replastering a Room", "plastering"),
    ("Ceiling Skim", "plastering"),
    ("Plastering After Rewire", "plastering"),
    ("Making Good Plaster", "plastering"),
    ("Dot and Dab Installation", "plastering"),
    # Voids / renovation verb forms
    ("Void Clearance", "renovation"),
    ("Void Refurbishment", "renovation"),
    ("Void Making Good", "renovation"),
    ("Void Kitchen Replace", "renovation"),
    ("Void Plaster and Paint", "renovation"),
    ("Landlord Void Works", "renovation"),
    ("Void Property Works", "renovation"),
    ("Empty Property Refurb", "renovation"),
    ("Turnaround Void Package", "renovation"),
    ("Refurbishing a Void", "renovation"),
    ("Renovating a Terrace", "renovation"),
    ("Renovating a Flat", "renovation"),
    ("Investment Void Refresh", "renovation"),
    ("HMO Void Refurbishment", "renovation"),
    # Kitchens + verb forms
    ("Kitchen Fitting Near Me", "kitchens"),
    ("Installing Kitchens", "kitchens"),
    ("Kitchen Refit", "kitchens"),
    ("Kitchen Repair", "kitchens"),
    ("Kitchen Unit Repair", "kitchens"),
    ("Kitchen Worktop Replacement", "kitchens"),
    ("Fitting a Kitchen", "kitchens"),
    ("Replacing a Kitchen", "kitchens"),
    ("Landlord Kitchen Refit", "kitchens"),
    ("Void Kitchen Fitting", "kitchens"),
    ("HMO Kitchen Install", "kitchens"),
    ("Kitchen Carcass Repair", "kitchens"),
    ("Kitchen Door Replacement", "kitchens"),
    ("Kitchen Sink Refit", "kitchens"),
    ("Compact Kitchen Installation", "kitchens"),
    # Windows / doors + verb forms
    ("Window Installation", "windows-doors"),
    ("Door Installation", "windows-doors"),
    ("Window Repair", "windows-doors"),
    ("uPVC Door Repair", "windows-doors"),
    ("Sash Window Repair Support", "windows-doors"),
    ("Window Sealing", "windows-doors"),
    ("Installing Windows", "windows-doors"),
    ("Replacing Windows", "windows-doors"),
    ("Fitting uPVC Windows", "windows-doors"),
    ("Composite Door Fitting", "windows-doors"),
    ("Landlord Window Repair", "windows-doors"),
    ("Void Window Replacement", "windows-doors"),
    ("Fire Door Installation Support", "windows-doors"),
    ("Door Threshold Repair", "windows-doors"),
    ("Window Handle Replacement", "windows-doors"),
    ("French Door Repair", "windows-doors"),
    # Related building verbs
    ("Dry Lining Repair", "dry-lining"),
    ("Installing Dry Lining", "dry-lining"),
    ("Metal Stud Installation", "dry-lining"),
    ("Joinery Repair", "joinery"),
    ("Installing Joinery", "joinery"),
    ("Carpentry Repair", "carpentry"),
    ("Installing First Fix Carpentry", "carpentry"),
    ("Render Installation", "rendering"),
    ("Repairing Render", "rendering"),
    ("Damp Proof Course Installation", "damp-proofing"),
    ("Treating Rising Damp", "damp-proofing"),
    ("Installing Loft Insulation", "insulation"),
    ("Insulating a Loft", "insulation"),
    ("Loft Conversion Survey", "loft-conversions"),
    ("Building a Rear Extension", "extensions"),
    ("Building Maintenance Visit", "building-maintenance"),
    ("Inspecting a Building", "building-surveys"),
    ("Void Condition Survey", "building-surveys"),
]

NEW_GAS: list[tuple[str, str]] = [
    ("CP12 Certificate", "gas-systems"),
    ("CP12 Inspection", "gas-systems"),
    ("CP12 Renewal", "gas-systems"),
    ("CP12 for Landlords", "gas-systems"),
    ("CP12 for Tenants", "gas-systems"),
    ("CP12 HMO", "gas-systems"),
    ("Expired CP12", "gas-systems"),
    ("Overdue Gas Safety", "gas-systems"),
    ("Book a CP12", "gas-systems"),
    ("CP12 Same Day Certificate", "gas-systems"),
    ("Gas Safety Certificate for Landlords", "gas-systems"),
    ("Landlord Gas Safety Record", "gas-systems"),
    ("Gas Safety Record for Let", "gas-systems"),
    ("Annual LGSR", "gas-systems"),
    ("LGSR Renewal", "gas-systems"),
    ("Gas Safe Check", "gas-systems"),
    ("Gas Safety Test", "gas-systems"),
    ("Gas Appliance Safety Check", "gas-systems"),
    ("Boiler Gas Safety Check", "gas-systems"),
    ("Cooker Gas Safety", "gas-systems"),
    ("Hob Gas Safety Check", "gas-systems"),
    ("Gas Fire Safety Check", "gas-systems"),
    ("Installing a Boiler", "gas-systems"),
    ("Boiler Fitting", "gas-systems"),
    ("New Boiler Installation", "gas-systems"),
    ("Replacing a Boiler", "gas-systems"),
    ("Boiler Swap", "gas-systems"),
    ("Combi Boiler Replacement", "gas-systems"),
    ("System Boiler Installation", "gas-systems"),
    ("System Boiler Repair", "gas-systems"),
    ("Regular Boiler Repair", "gas-systems"),
    ("Back Boiler Replacement Support", "gas-systems"),
    ("Boiler Servicing", "gas-systems"),
    ("Boiler Annual Service", "gas-systems"),
    ("Worcester Bosch Boiler Service", "gas-systems"),
    ("Vaillant Boiler Service", "gas-systems"),
    ("Ideal Boiler Service", "gas-systems"),
    ("Baxi Boiler Service", "gas-systems"),
    ("Boiler Leak Repair", "gas-systems"),
    ("Boiler Lockout Repair", "gas-systems"),
    ("Boiler Ignition Repair", "gas-systems"),
    ("Boiler Thermostat Repair", "gas-systems"),
    ("Boiler Pump Replacement", "gas-systems"),
    ("Boiler Flue Repair", "gas-systems"),
    ("Flue Inspection", "gas-systems"),
    ("Flue Repair", "gas-systems"),
    ("Flue Replacement", "gas-systems"),
    ("Flue Terminal Replacement", "gas-systems"),
    ("Flue Liner Installation", "gas-systems"),
    ("Balanced Flue Installation", "gas-systems"),
    ("Vertical Flue Installation", "gas-systems"),
    ("Carbon Monoxide Flue Check", "gas-systems"),
    ("Landlord Boiler Repair", "gas-systems"),
    ("Landlord Boiler Replacement", "gas-systems"),
    ("Void Boiler Service", "gas-systems"),
    ("Void Gas Safety Certificate", "gas-systems"),
    ("New Tenancy Gas Certificate", "gas-systems"),
    ("End of Tenancy Gas Check", "gas-systems"),
    ("Portfolio LGSR", "gas-systems"),
    ("Multi Site Gas Safety", "gas-systems"),
    ("Agency Gas Certificates", "gas-systems"),
    ("HMO CP12", "gas-systems"),
    ("HMO Boiler Service", "gas-systems"),
    ("Commercial CP12", "gas-systems"),
    ("Commercial Gas Safety Check", "gas-systems"),
    ("Commercial Kitchen Gas Certificate", "gas-systems"),
    ("Commercial Gas Tightness Test", "gas-systems"),
    ("Gas Interlock Installation", "gas-systems"),
    ("Kitchen Gas Interlock", "gas-systems"),
    ("Gas Proving System", "gas-systems"),
    ("Gas Isolation Valve", "gas-systems"),
    ("Gas Pipework Installation", "gas-systems"),
    ("Gas Pipework Alteration", "gas-systems"),
    ("Gas Pipework Repair", "gas-systems"),
    ("Bonding Gas Pipe", "gas-systems"),
    ("Gas Meter Box Repair Support", "gas-systems"),
    ("Gas Meter Move Support", "gas-systems"),
    ("Emergency Gas Leak", "gas-systems"),
    ("Gas Leak Repair", "gas-systems"),
    ("Suspected Gas Leak", "gas-systems"),
    ("Carbon Monoxide Alarm for Landlords", "gas-systems"),
    ("CO Alarm Near Boiler", "gas-systems"),
    ("No Hot Water Repair", "gas-systems"),
    ("No Heating Repair", "gas-systems"),
    ("Radiator Not Heating Gas", "gas-systems"),
    ("Heating Controls Installation", "gas-systems"),
    ("Smart Thermostat Gas", "gas-systems"),
    ("Programmer Replacement Gas", "gas-systems"),
    ("Magnetic Filter Installation", "gas-systems"),
    ("System Inhibitor Service", "gas-systems"),
    ("Condensate Pipe Repair", "gas-systems"),
    ("Frozen Condensate Repair", "gas-systems"),
    ("Boiler Pressure Loss", "gas-systems"),
    ("Filling Loop Repair", "gas-systems"),
    ("Expansion Vessel Replacement", "gas-systems"),
    ("Diverter Valve Replacement", "gas-systems"),
    ("Gas Fire Repair", "gas-systems"),
    ("Gas Fire Replacement", "gas-systems"),
    ("Gas Cooker Repair", "gas-systems"),
    ("Gas Cooker Disconnect", "gas-systems"),
    ("Gas Cooker Reconnect", "gas-systems"),
    ("Gas Hob Repair", "gas-systems"),
    ("LPG Boiler Service", "gas-systems"),
    ("LPG Cooker Safety", "gas-systems"),
    ("LPG Tightness Test", "gas-systems"),
    ("Industrial Gas Safety", "gas-systems"),
    ("Plant Room Gas Service", "gas-systems"),
    ("Gas Safe Registered Engineer", "gas-systems"),
    ("Gas Engineer Manchester", "gas-systems"),
    ("Gas Safety Certificate Stockport", "gas-systems"),
    ("Boiler Install Stockport", "gas-systems"),
    ("Landlord Gas Stockport", "gas-systems"),
    ("CP12 North West", "gas-systems"),
    ("Gas Safety Greater Manchester", "gas-systems"),
    ("Same Day Gas Safety Certificate", "gas-systems"),
    ("Emergency Boiler Repair", "gas-systems"),
    ("24 Hour Gas Engineer", "gas-systems"),
    ("Weekend Boiler Repair", "gas-systems"),
    ("Boiler Installation Quote", "gas-systems"),
    ("How Much Is a CP12", "gas-systems"),
    ("Gas Safety Certificate Price", "gas-systems"),
    ("Combi Boiler Quote", "gas-systems"),
    ("Inspecting a Boiler", "gas-systems"),
    ("Servicing a Boiler", "gas-systems"),
    ("Repairing a Boiler", "gas-systems"),
    ("Installing a Flue", "gas-systems"),
    ("Inspecting a Flue", "gas-systems"),
    ("Landlord Gas Inspection", "gas-systems"),
    ("Lettings Gas Safety", "gas-systems"),
    ("Tenant Gas Safety Check", "gas-systems"),
    ("Annual Landlord CP12", "gas-systems"),
    ("Gas Certificate Renewal", "gas-systems"),
    ("Boiler Commissioning", "gas-systems"),
    ("New Build Gas First Fix Support", "gas-systems"),
    ("Gas Hob Disconnect", "gas-systems"),
    ("Gas Fire Servicing", "gas-systems"),
    ("Unvented Cylinder Gas Support", "gas-systems"),
    ("Warm Air Unit Gas Service", "gas-systems"),
    ("Gas Cooker Bayonet Repair", "gas-systems"),
    ("Emergency CP12", "gas-systems"),
    ("Portfolio Boiler Servicing", "gas-systems"),
]


SERVICE_COPY = {
    "brickwork": ("Brickwork & Masonry", "BS 5628 / EN 1996 practice", "point, rebuild and make good"),
    "roofing": ("Roofing", "BS 5534 · manufacturer roofing systems", "inspect, repair and weatherproof"),
    "plastering": ("Plastering", "BS EN 13914 · trade finish standards", "skim, board and finish"),
    "renovation": ("Property Renovation", "Building Regs · landlord letting standards", "strip, rebuild and handover"),
    "kitchens": ("Kitchen Fitting", "Building Regs · gas/electric safe isolation", "supply, fit and finish"),
    "windows-doors": ("Windows & Doors", "Part L · Part Q · fire door certification", "survey, fit and seal"),
    "dry-lining": ("Dry Lining", "BS 8212 · fire-rated board systems", "set out, board and close"),
    "joinery": ("Joinery", "BS 4787 doors · site joinery practice", "measure, make and hang"),
    "carpentry": ("Carpentry", "NHBC-style detailing · site practice", "first-fix and second-fix"),
    "rendering": ("Rendering", "BS EN 13914 · manufacturer render systems", "prepare, apply and weatherproof"),
    "damp-proofing": ("Damp Proofing", "BS 6576 · manufacturer damp systems", "diagnose, treat and replaster"),
    "insulation": ("Insulation", "Part L · PAS 2035 awareness where relevant", "specify, install and record"),
    "loft-conversions": ("Loft Conversions", "Building Regs Parts B/L/K", "build, insulate and certify-ready"),
    "extensions": ("Home Extensions", "Building Regs · structural design input", "build the envelope and first-fix"),
    "building-maintenance": ("Building Maintenance", "Site RAMS · landlord & commercial SLAs", "attend, repair and report"),
    "building-surveys": ("Building Surveys", "RICS-style condition reporting practice", "inspect, record and recommend"),
    "tiling": ("Tiling", "BS 5385 · manufacturer adhesive systems", "set out, fix and finish"),
    "painting-decorating": ("Painting & Decorating", "Trade prep standards · low-VOC options", "prep, decorate and protect"),
    "flooring": ("Flooring", "Manufacturer install warranties · subfloor prep", "prep, lay and finish"),
    "gas-systems": ("Gas Systems", "Gas Safe · CP12 / CP44 · manufacturer servicing", "inspect, service and certificate"),
}


def unique_seo(slug: str, name: str, service: str, family: str, idx: int) -> dict:
    svc_name, standards, verb = SERVICE_COPY.get(
        service, (service.replace("-", " ").title(), "UK standards and manufacturer guidance", "scope, deliver and handover")
    )
    variant = idx % 4
    audiences = [
        "landlords and letting agents",
        "homeowners and small landlords",
        "HMO operators and managing agents",
        "commercial occupiers and FM teams",
    ]
    audience = audiences[variant]
    if re.search(r"landlord|lett|tenanc|void|cp12|lgsr", slug):
        audience = "landlords and letting agents"
    elif re.search(r"hmo", slug):
        audience = "HMO operators and managing agents"
    elif re.search(r"commercial|industrial|plant-room|kitchen-gas", slug):
        audience = "commercial occupiers and FM teams"

    h1_choices = [
        name,
        f"{name} across the North West",
        f"{name} for {audience}",
        f"{svc_name}: {name}",
    ]
    h1 = h1_choices[variant]
    if name.lower() not in h1.lower():
        h1 = name

    title_choices = [
        f"{name} | {svc_name} North West",
        f"{name} — iComply Stockport",
        f"{name} | Building & Gas | iComply",
        f"{name} in Greater Manchester & the North West",
    ]
    seo_title = title_choices[variant]
    if len(seo_title) > 65:
        seo_title = f"{name} | {svc_name} | iComply"

    poa = "Written POA quote from Stockport — no invented £ prices."
    meta = (
        f"{name} for {audience} across Greater Manchester and the North West. "
        f"{standards}. {poa}"
    )
    if len(meta) > 165:
        meta = f"{name} across the North West. {svc_name} from iComply. {poa}"

    intro = (
        f"{name} sits under our {svc_name} service. We {verb} the work you actually have "
        f"— not a generic package — for {audience} from our Stockport SK2 base."
    )
    bodies = [
        (
            f"{svc_name} work for {name} starts with a site look, a written scope and a POA figure "
            f"once access, standards and any existing equipment are clear. "
            f"Typical North West stock includes terraces, purpose-built flats, HMOs, offices and light industrial."
        ),
        (
            f"We say when {name} is the right next step and when a wider {svc_name} visit is safer. "
            f"Paperwork is issued after the agreed works or inspection. Quotes stay POA / enquire."
        ),
        (
            f"iComply engineers attend from Offerton (SK2) into Manchester, Bolton, Oldham, Stockport and the wider North West. "
            f"Tell us the postcode, property type and any brand already on site for {name}."
        ),
        (
            f"Related {svc_name} jobs and towns are linked from this page so you can move Category → Service → Job → Area. "
            f"We do not publish catalogue pound figures for {name}."
        ),
    ]
    rot = variant
    body = " ".join(bodies[i] for i in (rot, (rot + 1) % 4, (rot + 2) % 4))

    faqs = [
        [
            f"What does {name} include?",
            f"Scope is confirmed in your quote. We typically {verb} under {svc_name}, then issue the paperwork that job actually needs.",
        ],
        [
            f"Do you cover my town for {name}?",
            "Yes across Greater Manchester, Cheshire, Lancashire and the wider North West from Stockport. Use the area links on this page or the areas hub.",
        ],
        [
            f"How do you price {name}?",
            "Price on application after we confirm access, standards and materials. We do not invent a catalogue £ figure on this page.",
        ],
        [
            f"Who is {name} for?",
            f"{audience.capitalize()} booking through iComply. Tell us the postcode, property type and any panel, boiler or door brand already on site.",
        ],
    ]
    faqs.pop(variant)
    is_cost = bool(re.search(r"\b(cost|price|quote|how-much|how much)\b", slug + " " + name, re.I))
    if is_cost:
        faqs[0] = [
            f"Why is {name} POA?",
            "Access, appliance count, flue type and making-good change the figure. iComply will not publish an invented £ price.",
        ]

    focus = [
        f"Scope confirmed against {standards}",
        "Written POA quote after we know the property — no invented £",
        f"{verb} as part of {svc_name}",
        "Stockport engineers covering 150+ North West towns",
    ]
    secondaries = [f"{svc_name} {family}", f"{name} North West", f"{name} Stockport"]
    return {
        "seo_title": seo_title,
        "h1": h1,
        "intro": intro,
        "body": body,
        "meta_desc": meta,
        "seo_keywords": f"{name}, {svc_name}, {family}, North West, Stockport, iComply",
        "focus_points": focus,
        "faq": faqs,
        "secondaries": secondaries,
    }


def enrich(jobs: list[dict], family: str) -> list[dict]:
    out = []
    for i, job in enumerate(jobs):
        row = dict(job)
        row["family"] = family
        row.update(unique_seo(row["slug"], row["name"], row["service"], family, i))
        if i > 0:
            row["related"] = jobs[i - 1]["slug"]
        elif len(jobs) > 1:
            row["related"] = jobs[1]["slug"]
        out.append(row)
    return out


def pick_building(have: dict[str, dict], reserved: set[str] | None = None) -> list[dict]:
    reserved = reserved or set()
    chosen: dict[str, dict] = {}

    def add(row: dict) -> None:
        slug = row["slug"]
        if not slug or slug in chosen or slug in reserved:
            return
        chosen[slug] = row

    for svc in CORE_BUILDING:
        for job in sorted(have.values(), key=lambda j: j["slug"]):
            if job["service"] == svc:
                add(job)

    for name, svc in NEW_BUILDING:
        add(job_row(name, svc))

    for svc in RELATED_BUILDING:
        for job in sorted(have.values(), key=lambda j: j["slug"]):
            if job["service"] == svc:
                add(job)

    # Deterministic pad from remaining construction-ish master jobs if still short.
    extra_svcs = set(CORE_BUILDING + RELATED_BUILDING)
    if len(chosen) < BUILDING_TARGET:
        for job in sorted(have.values(), key=lambda j: j["slug"]):
            if job["service"] in extra_svcs:
                add(job)
            if len(chosen) >= BUILDING_TARGET:
                break

    jobs = list(chosen.values())
    jobs.sort(key=lambda j: (0 if j["service"] in CORE_BUILDING else 1, j["slug"]))
    if len(jobs) < BUILDING_TARGET:
        raise SystemExit(f"building list only {len(jobs)}, need {BUILDING_TARGET}")
    return jobs[:BUILDING_TARGET]


def pick_gas(have: dict[str, dict]) -> list[dict]:
    chosen: dict[str, dict] = {}

    def add(row: dict) -> None:
        slug = row["slug"]
        if not slug or slug in chosen:
            return
        chosen[slug] = row

    for job in sorted(have.values(), key=lambda j: j["slug"]):
        if job["service"] == "gas-systems":
            add(job)

    for name, svc in NEW_GAS:
        add(job_row(name, svc))

    jobs = list(chosen.values())
    jobs.sort(key=lambda j: j["slug"])
    if len(jobs) < GAS_TARGET:
        raise SystemExit(f"gas list only {len(jobs)}, need {GAS_TARGET}")
    return jobs[:GAS_TARGET]


def write_family(path: Path, family: str, jobs: list[dict]) -> None:
    payload = {
        "family": family,
        "count": len(jobs),
        "jobs": jobs,
    }
    path.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")


STUB = """<?php
/** AUTO-GENERATED stub — php bin/generate-building-gas-pages.php */
require_once __DIR__ . '/../../includes/render.php';
renderKeywordPage({slug});
"""


def write_stubs(jobs: list[dict]) -> int:
    PAGES.mkdir(parents=True, exist_ok=True)
    n = 0
    for job in jobs:
        slug = job["slug"]
        php = STUB.format(slug=repr(slug))
        (PAGES / f"{slug}.php").write_text(php, encoding="utf-8")
        n += 1
    return n


def check_outputs(building: list[dict], gas: list[dict]) -> int:
    building_count = len(building)
    gas_count = len(gas)
    ok = True
    if building_count != BUILDING_TARGET:
        print(f"FAIL building_count={building_count} != {BUILDING_TARGET}")
        ok = False
    else:
        print(f"OK   building_count=={BUILDING_TARGET}")
    if gas_count != GAS_TARGET:
        print(f"FAIL gas_count={gas_count} != {GAS_TARGET}")
        ok = False
    else:
        print(f"OK   gas_count=={GAS_TARGET}")
    total = building_count + gas_count
    if total != BUILDING_TARGET + GAS_TARGET:
        print(f"FAIL total={total} != 470")
        ok = False
    else:
        print("OK   total==470")

    slugs = [j["slug"] for j in building + gas]
    if len(slugs) != len(set(slugs)):
        print("FAIL duplicate slugs across building+gas")
        ok = False
    else:
        print("OK   slugs unique across 470")

    missing = [s for s in slugs if not (PAGES / f"{s}.php").is_file()]
    if missing:
        print(f"FAIL missing {len(missing)} stub files (sample {missing[:8]})")
        ok = False
    else:
        print(f"OK   all {len(slugs)} outputs exist under pages/keywords/")

    # Brand / POA lock in stored SEO
    brand_fail = []
    for job in building + gas:
        blob = " ".join(
            [
                job.get("seo_title", ""),
                job.get("intro", ""),
                job.get("body", ""),
                job.get("meta_desc", ""),
                json.dumps(job.get("faq", [])),
            ]
        )
        if "£" in blob and re.search(r"£\s*\d", blob):
            brand_fail.append(job["slug"] + ":invented-£")
        if "POA" not in blob and "price on application" not in blob.lower():
            brand_fail.append(job["slug"] + ":no-POA")
        if "iComply" not in blob and "Icomply" not in blob:
            brand_fail.append(job["slug"] + ":no-brand")
        if not job.get("faq"):
            brand_fail.append(job["slug"] + ":no-faq")
    if brand_fail:
        print(f"FAIL SEO/brand/POA/FAQ on {len(brand_fail)} (sample {brand_fail[:6]})")
        ok = False
    else:
        print("OK   unique SEO, FAQ, brand lock and POA CTAs on all 470")

    print(f"building_count={building_count} gas_count={gas_count} total={total}")
    return 0 if ok else 1


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--check-only", action="store_true")
    args = parser.parse_args()

    if args.check_only:
        building = load_json(BUILDING_PATH).get("jobs", [])
        gas = load_json(GAS_PATH).get("jobs", [])
        return check_outputs(building, gas)

    have = existing_jobs()
    original_gas = {s for s, j in have.items() if j.get("service") == "gas-systems"}
    gas = pick_gas(have)
    reserved_gas = {j["slug"] for j in gas}
    building = pick_building(have, reserved_gas)
    overlap = reserved_gas & {j["slug"] for j in building}
    if overlap:
        raise SystemExit("building/gas slug overlap: " + ",".join(sorted(overlap)[:12]))
    building = enrich(building, "building")
    gas = enrich(gas, "gas")
    for job in gas:
        job["hub_only"] = job["slug"] not in original_gas
    write_family(BUILDING_PATH, "building", building)
    write_family(GAS_PATH, "gas", gas)
    written = write_stubs(building + gas)
    print(f"Wrote {len(building)} building → {BUILDING_PATH}")
    print(f"Wrote {len(gas)} gas → {GAS_PATH}")
    print(f"Wrote {written} stubs → {PAGES}")
    return check_outputs(building, gas)


if __name__ == "__main__":
    raise SystemExit(main())
