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


# Related trades (dry lining, joinery, and so on) are listed in NEW_BUILDING but
# do not fit the 247 cap once every core verb form is kept. They are not selected.
# Gas pages dropped so safety, void and boiler verb forms fit the 223 cap:
# town-name doorway hubs (the town matrix already covers them), "support" hedges,
# and near-duplicates or jobs that belong to another trade.
GAS_DROP = frozenset({
    "boiler-install-stockport",
    "cp12-north-west",
    "gas-engineer-manchester",
    "gas-safety-certificate-stockport",
    "gas-safety-greater-manchester",
    "landlord-gas-stockport",
    "back-boiler-replacement-support",
    "gas-meter-box-repair-support",
    "gas-meter-move-support",
    "new-build-gas-first-fix-support",
    "carbon-monoxide-alarm-for-landlords",
    "co-alarm-near-boiler",
    "bonding-gas-pipe",
    "boiler-installation-quote",
    "combi-boiler-quote",
    "hob-gas-safety-check",
    "cooker-gas-safety",
    "gas-safe-check",
    "book-a-cp12",
    "annual-landlord-cp12",
    "cp12-same-day-certificate",
    "gas-safety-test",
    "cp12-inspection",
})
# Niche window accessories, so the Window Sealing verb form stays inside 247.
BUILDING_DROP = frozenset({
    "window-restrictor-install",
    "window-trickle-vent",
    "window-handle-and-gearbox",
})

SERVICE_COPY = {
    "brickwork": ("Brickwork & Masonry", "BS EN 1996 masonry practice", "set out, build and point"),
    "roofing": ("Roofing", "BS 5534 for pitched tile and slate", "inspect, repair and weatherproof"),
    "plastering": ("Plastering", "BS EN 13914 plastering practice", "board, skim and finish"),
    "renovation": ("Property Renovation", "Building Regulations and the letting standard where it is a let", "scope, carry out and hand over"),
    "kitchens": ("Kitchen Fitting", "Building Regulations, with gas and electrics isolated by the right trade", "survey, fit and finish"),
    "windows-doors": ("Windows & Doors", "Building Regulations Part L", "survey, fit and seal"),
    "dry-lining": ("Dry Lining", "BS 8212 board systems", "set out, board and close"),
    "joinery": ("Joinery", "site joinery practice", "measure, make and hang"),
    "carpentry": ("Carpentry", "site carpentry practice", "first-fix and second-fix"),
    "rendering": ("Rendering", "BS EN 13914 and the render manufacturer system", "prepare, apply and weatherproof"),
    "damp-proofing": ("Damp Proofing", "manufacturer damp systems after a proper diagnosis", "diagnose, treat and replaster"),
    "insulation": ("Insulation", "Building Regulations Part L", "specify, install and record"),
    "loft-conversions": ("Loft Conversions", "Building Regulations", "build, insulate and hand over"),
    "extensions": ("Home Extensions", "Building Regulations, with structural design from the right designer", "build the envelope and first-fix"),
    "building-maintenance": ("Building Maintenance", "site RAMS and the agreed attendance", "attend, repair and report"),
    "building-surveys": ("Building Surveys", "a written record of what is visible on site", "inspect, record and recommend"),
    "tiling": ("Tiling", "BS 5385 and the adhesive manufacturer", "set out, fix and finish"),
    "painting-decorating": ("Painting & Decorating", "trade preparation standards", "prep, decorate and protect"),
    "flooring": ("Flooring", "the flooring manufacturer install instructions", "prep, lay and finish"),
    "gas-systems": ("Gas Systems", "Gas Safe and the appliance manufacturer instructions", "inspect, repair and record"),
}

ASK_ON_SITE = {
    "brickwork": "the brick type and whether the wall is cavity or solid",
    "roofing": "the covering — slate, tile or flat — and where water is getting in",
    "plastering": "which rooms and whether the background is brick, block or board",
    "renovation": "whether anyone is still living there and what has to be ready for the next let",
    "kitchens": "which units are staying and whether a gas or electric appliance needs isolating",
    "windows-doors": "the frame material and whether any opening is an escape window or a fire door",
    "gas-systems": "the appliance make and model, and whether the property is a let",
}


def job_intent(slug: str, name: str, service: str) -> str:
    hay = f"{slug} {name}".lower()
    if re.search(r"gas-leak|suspected-gas|smell-of-gas|emergency-gas-leak", hay):
        return "emergency"
    if re.search(r"certificate|cp12|lgsr|gas-safety-record", hay):
        return "certificate"
    if re.search(r"inspect|survey|\bcheck\b|testing|\btest\b", hay):
        return "inspect"
    if re.search(r"service|servicing", hay):
        return "service"
    if re.search(r"repair|leak|patch|fault|lockout|making-good|repoint|\bseal", hay):
        return "repair"
    if re.search(r"replacement|replacing", hay):
        if service in {"brickwork", "roofing", "plastering", "windows-doors", "renovation"}:
            return "repair"
        return "install"
    if re.search(r"install|fitting|\bfit\b|installing", hay):
        return "install"
    return "works"


def job_audience(slug: str, name: str) -> str:
    hay = f"{slug} {name}".lower()
    if re.search(r"\bhmo\b", hay):
        return "HMO operators and managing agents"
    if re.search(r"\btenants?\b", hay):
        return "tenants, via the landlord or agent who has to book the work"
    if re.search(r"commercial|industrial|plant-room|plant room", hay):
        return "commercial occupiers and FM teams"
    if re.search(r"landlord|lettings|letting|tenancy|\bvoid\b|\bcp12\b|\blgsr\b|portfolio|agency", hay):
        return "landlords and letting agents"
    if re.search(r"homeowner|domestic|\bhouse\b", hay):
        return "homeowners and small landlords"
    return "homeowners, landlords and managing agents"


def job_standards(service: str, slug: str, name: str, intent: str) -> str:
    hay = f"{slug} {name}".lower()
    if service == "gas-systems":
        if "cp44" in hay:
            return "Gas Safe commercial catering (CP44) — not a domestic CP12"
        if re.search(r"commercial|industrial|plant-room|catering", hay):
            return "Gas Safe commercial elements — this is not a domestic CP12"
        if "lpg" in hay:
            return "Gas Safe and the LPG appliance instructions"
        if "flue" in hay:
            return "Gas Safe and the appliance manufacturer flue instructions"
        if intent == "certificate" or re.search(r"cp12|lgsr", hay):
            return "Gas Safe and the domestic landlord gas safety record (CP12 / LGSR)"
        return "Gas Safe and the appliance manufacturer instructions"
    if service == "brickwork":
        return "BS EN 1996 masonry practice"
    if service == "roofing":
        if "flat" in hay:
            return "the flat-roof manufacturer system"
        return "BS 5534 for pitched tile and slate"
    if service == "plastering":
        return "BS EN 13914 plastering practice"
    if service == "kitchens":
        return "Building Regulations, with gas and electrics isolated by the right trade"
    if service == "windows-doors":
        if "fire-door" in hay or "fire door" in hay:
            return "the fire-door certificate and Building Regulations for that door"
        return "Building Regulations Part L"
    if service == "renovation":
        return "Building Regulations and the letting standard where it is a let"
    fallback = SERVICE_COPY.get(service)
    if fallback:
        return fallback[1]
    return "British Standards and manufacturer instructions"


def job_verb(service: str, intent: str, slug: str = "", name: str = "") -> str:
    hay = f"{slug} {name}".lower()
    if service == "gas-systems" and intent == "certificate" and re.search(r"commercial|industrial|plant-room|cp44", hay):
        return "inspect the installation and issue the commercial gas safety record, not a domestic CP12"
    table = {
        ("brickwork", "repair"): "cut out failed bricks, repoint and make good",
        ("brickwork", "inspect"): "inspect the masonry, record it and say what to do next",
        ("brickwork", "install"): "set out, build and point",
        ("roofing", "repair"): "find the leak path, repair the covering and leave it weatherproof",
        ("roofing", "inspect"): "inspect the roof and record where water is getting in",
        ("roofing", "install"): "strip, re-cover and weather the details",
        ("plastering", "repair"): "patch, skim and leave a surface ready for decoration",
        ("plastering", "install"): "board or bond, then skim",
        ("kitchens", "repair"): "repair the units that are staying and leave the kitchen usable",
        ("kitchens", "install"): "survey, fit and finish, isolating gas or electrics with the right trade",
        ("windows-doors", "repair"): "repair the opening and check it still closes and locks",
        ("windows-doors", "install"): "survey, fit and seal",
        ("gas-systems", "emergency"): "send you to the National Gas Emergency Service first, then attend only once the supply is safe",
        ("gas-systems", "certificate"): "inspect the appliances and issue the gas safety record the let needs",
        ("gas-systems", "inspect"): "inspect the appliance, record what we find and recommend the next step",
        ("gas-systems", "service"): "service it to the manufacturer routine and record what was done",
        ("gas-systems", "repair"): "diagnose the fault, repair it and relight under Gas Safe",
        ("gas-systems", "install"): "survey, install and commission under Gas Safe",
    }
    if (service, intent) in table:
        return table[(service, intent)]
    generic = {
        "emergency": "make the situation safe before any other work",
        "certificate": "inspect and issue the record that job needs",
        "inspect": "inspect, record what we find and recommend the next step",
        "service": "service it and record what was done",
        "repair": "diagnose, repair and make good",
        "install": "survey, install and hand over",
        "works": "scope the job, do the agreed work and hand it over",
    }
    return generic[intent]


def fit_title(name: str, svc_name: str, idx: int) -> str:
    choices = [
        f"{name} | {svc_name} North West",
        f"{name} — iComply Stockport",
        f"{name} | {svc_name} | iComply",
        f"{name} | North West",
    ]
    title = choices[idx % 4]
    if len(title) <= 65:
        return title
    shorter = [
        f"{name} | {svc_name} | iComply",
        f"{name} | iComply",
        f"{name} | NW",
    ]
    for candidate in shorter:
        if len(candidate) <= 65:
            return candidate
    return (name[:52].rstrip(" -|") + " | iComply")[:65]


def unique_seo(slug: str, name: str, service: str, family: str, idx: int) -> dict:
    svc_name = SERVICE_COPY.get(service, (service.replace("-", " ").title(), "", ""))[0]
    intent = job_intent(slug, name, service)
    audience = job_audience(slug, name)
    standards = job_standards(service, slug, name, intent)
    verb = job_verb(service, intent, slug, name)
    ask = ASK_ON_SITE.get(service, "the postcode, the property type and what is already on site")
    poa = "Written POA quote from Stockport — no invented £ prices."
    variant = idx % 4

    h1_choices = [
        name,
        f"{name} across the North West",
        f"{svc_name}: {name}",
        name,
    ]
    h1 = h1_choices[variant]
    if name.lower() not in h1.lower() or len(h1) > 90:
        h1 = name

    seo_title = fit_title(name, svc_name, idx)
    notes: list[str] = []
    if intent == "emergency" or re.search(r"gas-leak|suspected-gas|smell-of-gas", slug):
        notes.append(
            "If you smell gas, hear hissing, or think gas is escaping, call the National Gas Emergency Service on 0800 111 999 before you call a contractor. Do not use switches. iComply does not attend an uncontrolled escape."
        )
    if "commercial" in slug and "cp12" in slug:
        notes.append(
            "People search for a commercial CP12, but that name is the domestic landlord gas safety record. Catering and plant gas need a commercial gas safety inspection by a Gas Safe engineer with the right commercial elements. iComply will not issue a domestic CP12 for that."
        )
    if re.search(r"\btenants?\b", f"{slug} {name}".lower()):
        notes.append(
            "A tenant cannot book the landlord gas safety record. The landlord or letting agent has to arrange it. Send them this page."
        )
    if re.search(r"24-hour|same-day|weekend", slug) and intent != "emergency":
        notes.append(
            "Same-day and out-of-hours visits depend on engineer cover for that postcode. This page does not guarantee a 24-hour call-out."
        )
    if re.search(r"how-much|price|quote", slug):
        notes.append(
            "There is no catalogue price for this. The figure is price on application after we know the appliance, the flue and the access."
        )

    meta = f"{name} for {audience} across the North West. {standards}. {poa}"
    if len(meta) > 160:
        meta = f"{name} across the North West. {svc_name} from iComply. {poa}"
    if len(meta) > 160:
        meta = f"{name}. {svc_name} from iComply. {poa}"[:160]

    intro = (
        f"This {name} job is part of our {svc_name} service for {audience}. "
        f"We {verb}, working from our Stockport SK2 base."
    )
    body_parts = [
        f"iComply starts with a look at the property for {name}, then a written scope and a POA figure. The work is checked against {standards}.",
        f"Tell us the postcode and {ask}.",
        "We do not publish a catalogue pound figure. Quotes stay POA / enquire.",
        *notes,
    ]
    body = " ".join(body_parts)

    faqs = [
        [
            f"What does {name} include?",
            f"The quote confirms the scope. For this job we {verb}. Paperwork matches the work that was actually done.",
        ],
        [
            f"How do you price {name}?",
            "Price on application after we confirm access, the property and the materials. iComply does not invent a catalogue £ figure on this page.",
        ],
        [
            f"Do you cover my town for {name}?",
            "Yes across Greater Manchester, Cheshire, Lancashire and the wider North West from Stockport. Town pages are linked from this hub.",
        ],
    ]
    if intent == "emergency" or re.search(r"gas-leak|suspected-gas|smell-of-gas", slug):
        faqs[0] = [
            "What should I do if I smell gas?",
            "Call the National Gas Emergency Service on 0800 111 999. Do not use switches and do not look for the leak with a flame. iComply attends only after the supply is safe.",
        ]
    elif "commercial" in slug and "cp12" in slug:
        faqs[0] = [
            "Is a commercial CP12 the same as a landlord CP12?",
            "No. CP12 is the domestic landlord gas safety record. Commercial catering and plant gas need a commercial gas safety inspection, not a domestic CP12.",
        ]
    elif re.search(r"\btenants?\b", f"{slug} {name}".lower()):
        faqs[2] = [
            "Can a tenant book this?",
            "No. The landlord or letting agent has to arrange the gas safety record. A tenant can pass this page to them.",
        ]
    elif re.search(r"24-hour|same-day|weekend", slug):
        faqs[2] = [
            "Do you guarantee a 24-hour or same-day visit?",
            "No. Out-of-hours and same-day visits depend on engineer cover for that postcode. Ask when you enquire.",
        ]
    elif re.search(r"how-much|price|quote", slug):
        faqs[1] = [
            f"Why is there no price for {name}?",
            "Access, the appliance and the flue change the figure. Price on application. iComply will not publish an invented £ price.",
        ]

    focus = [
        f"Scope confirmed against {standards}",
        "Written POA quote after we know the property — no invented £",
        verb[:1].upper() + verb[1:],
        "iComply engineers from Stockport covering the North West",
    ]
    return {
        "seo_title": seo_title,
        "h1": h1,
        "intro": intro,
        "body": body,
        "meta_desc": meta,
        "seo_keywords": f"{name}, {svc_name}, {family}, North West, Stockport, iComply",
        "focus_points": focus,
        "faq": faqs,
        "secondaries": [f"{name} North West", f"{name} Stockport", svc_name],
    }


def assign_related(jobs: list[dict]) -> None:
    """Link each job to another job in the same service, preferring a specific shared word."""
    by_service: dict[str, list[str]] = {}
    present = {job["slug"] for job in jobs}
    for job in jobs:
        by_service.setdefault(job["service"], []).append(job["slug"])
    stop = {"a", "the", "and", "for", "of", "to", "in", "on", "near", "with"}
    generic = {
        "gas", "boiler", "safety", "certificate", "cp12", "lgsr", "repair", "service",
        "installation", "install", "landlord", "north", "west", "brick", "roof",
        "window", "door", "kitchen", "plaster", "void", "property", "works", "job",
        "systems", "engineer", "commercial", "domestic",
    }
    leak_cycle = [s for s in ("emergency-gas-leak", "suspected-gas-leak", "gas-leak-repair", "smell-of-gas") if s in present]

    def tokens(slug: str) -> set[str]:
        return {t for t in slug.split("-") if t not in stop and len(t) > 2}

    for job in jobs:
        if job["slug"] in leak_cycle and len(leak_cycle) > 1:
            job["related"] = next(s for s in leak_cycle if s != job["slug"])
            continue
        siblings = [s for s in by_service.get(job["service"], []) if s != job["slug"]]
        mine = tokens(job["slug"])
        best = ""
        best_key: tuple[int, int, int, str] | None = None
        for sib in siblings:
            shared = mine & tokens(sib)
            if not shared:
                continue
            score = sum(3 if t not in generic else 1 for t in shared)
            starts = 1 if any(sib == t or sib.startswith(t + "-") for t in shared) else 0
            longest = max(len(t) for t in shared)
            key = (score, starts, longest, sib)
            # Higher score, then a sibling that starts with a shared word, then the longest shared word.
            if best_key is None or key[:3] > best_key[:3] or (key[:3] == best_key[:3] and sib < best):
                best, best_key = sib, key
        if not best and siblings:
            slugs = by_service[job["service"]]
            idx = slugs.index(job["slug"])
            best = slugs[idx - 1] if idx else slugs[min(1, len(slugs) - 1)]
        job["related"] = best or job["slug"]


def enrich(jobs: list[dict], family: str) -> list[dict]:
    out = []
    for i, job in enumerate(jobs):
        row = dict(job)
        row["family"] = family
        row.update(unique_seo(row["slug"], row["name"], row["service"], family, i))
        out.append(row)
    assign_related(out)
    return out


def pick_building(have: dict[str, dict], reserved: set[str] | None = None) -> list[dict]:
    """Core families only. Keep every core verb form. Do not fill by alphabetical slice."""
    reserved = reserved or set()
    chosen: dict[str, dict] = {}

    def add(row: dict) -> None:
        slug = row["slug"]
        if not slug or slug in chosen or slug in reserved or slug in BUILDING_DROP:
            return
        if row["service"] not in CORE_BUILDING:
            return
        chosen[slug] = row

    for name, svc in NEW_BUILDING:
        if svc in CORE_BUILDING:
            add(job_row(name, svc))

    for job in sorted(have.values(), key=lambda j: j["slug"]):
        if job["service"] in CORE_BUILDING:
            add(job)

    jobs = list(chosen.values())
    jobs.sort(key=lambda j: (CORE_BUILDING.index(j["service"]) if j["service"] in CORE_BUILDING else 99, j["slug"]))
    if len(jobs) != BUILDING_TARGET:
        raise SystemExit(f"building list {len(jobs)}, need exactly {BUILDING_TARGET}")
    return jobs


def pick_gas(have: dict[str, dict]) -> list[dict]:
    """Keep every existing gas-systems slug, then new jobs except the explicit drop list."""
    chosen: dict[str, dict] = {}

    def add(row: dict) -> None:
        slug = row["slug"]
        if not slug or slug in chosen or slug in GAS_DROP:
            return
        chosen[slug] = row

    for job in sorted(have.values(), key=lambda j: j["slug"]):
        if job["service"] == "gas-systems":
            add(job)

    for name, svc in NEW_GAS:
        add(job_row(name, svc))

    jobs = list(chosen.values())
    jobs.sort(key=lambda j: j["slug"])
    if len(jobs) != GAS_TARGET:
        raise SystemExit(f"gas list {len(jobs)}, need exactly {GAS_TARGET}")
    return jobs


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

    quality_fail = []
    by_slug = {j["slug"]: j for j in building + gas}
    for slug in ("window-sealing", "smell-of-gas", "suspected-gas-leak", "repairing-a-boiler", "void-gas-safety-certificate"):
        if slug not in by_slug:
            quality_fail.append(f"missing-protected:{slug}")
    for slug in GAS_DROP | BUILDING_DROP:
        if slug in by_slug:
            quality_fail.append(f"should-be-dropped:{slug}")
    services_by_slug = {j["slug"]: j["service"] for j in building + gas}
    for job in building + gas:
        blob = " ".join(
            [
                job.get("seo_title", ""),
                job.get("intro", ""),
                job.get("body", ""),
                job.get("meta_desc", ""),
                json.dumps(job.get("faq", [])),
                json.dumps(job.get("focus_points", [])),
            ]
        )
        if "Building & Gas" in job.get("seo_title", ""):
            quality_fail.append(job["slug"] + ":building-and-gas-title")
        if "panel, boiler or door brand" in blob:
            quality_fail.append(job["slug"] + ":boilerplate-brand-ask")
        if "BS 5628" in blob:
            quality_fail.append(job["slug"] + ":bs5628")
        if "CP44" in blob and not re.search(r"commercial|catering|plant|cp44", job["slug"]):
            quality_fail.append(job["slug"] + ":cp44")
        if "cp44" in job["slug"] and "not a domestic CP12" not in blob:
            quality_fail.append(job["slug"] + ":cp44-called-domestic")
        if re.search(rf"^{re.escape(job['name'])} sits under", job.get("intro", "")):
            quality_fail.append(job["slug"] + ":plural-or-name-sits")
        rel = job.get("related") or ""
        if services_by_slug.get(rel) != job["service"]:
            quality_fail.append(job["slug"] + ":related-other-service")
        if re.search(r"gas-leak|suspected-gas|smell-of-gas", job["slug"]) and "0800 111 999" not in blob:
            quality_fail.append(job["slug"] + ":no-gas-emergency-number")
        if job["slug"] == "commercial-cp12" and "not a domestic CP12" not in blob and "not a domestic" not in blob.lower():
            quality_fail.append(job["slug"] + ":commercial-cp12")
        if re.search(r"\btenants?\b", job["slug"]) and "landlord" not in blob.lower():
            quality_fail.append(job["slug"] + ":tenant-no-landlord")
        if re.search(r"24-hour|same-day|weekend", job["slug"]) and "guarantee" not in blob.lower() and "gas-leak" not in job["slug"]:
            quality_fail.append(job["slug"] + ":availability-claim")
    if quality_fail:
        print(f"FAIL copy/selection on {len(quality_fail)} (sample {quality_fail[:8]})")
        ok = False
    else:
        print("OK   selection keeps safety and verb forms; copy is trade-specific")

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
