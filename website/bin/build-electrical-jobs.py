#!/usr/bin/env python3
"""Build website/data/electrical-jobs.json — exactly 340 Electrical master slugs."""
from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TARGET = 340
SERVICE = "electrical"


def slugify(phrase: str) -> str:
    s = phrase.lower().strip()
    s = re.sub(r"[^a-z0-9]+", "-", s)
    return s.strip("-")


def display_name(slug: str) -> str:
    name = slug.replace("-", " ")
    name = name.title()
    for frm, to in (
        ("Eicr", "EICR"),
        ("Pat", "PAT"),
        ("Ev", "EV"),
        ("Bs", "BS"),
        ("Hmo", "HMO"),
        ("Niceic", "NICEIC"),
        ("Rcd", "RCD"),
        ("Rcbo", "RCBO"),
        ("Afdd", "AFDD"),
        ("Spd", "SPD"),
        ("Swa", "SWA"),
        ("Usb", "USB"),
        ("Cat6", "Cat6"),
        ("Ip", "IP"),
        ("Led", "LED"),
        ("Pir", "PIR"),
        ("Ups", "UPS"),
        ("Ats", "ATS"),
        ("Eic", "EIC"),
        ("Kw", "kW"),
        ("Fi", "FI"),
        ("Ze", "Ze"),
        ("Zs", "Zs"),
        ("Mees", "MEES"),
    ):
        name = re.sub(rf"\b{frm}\b", to, name)
    return name


def load_existing_slugs() -> set[str]:
    slugs: set[str] = set()
    master = json.loads((ROOT / "data" / "job-types-master.json").read_text())
    for row in master.get("jobs", []):
        slugs.add(slugify(row.get("slug") or ""))
    kw = json.loads((ROOT / "data" / "keywords.json").read_text())
    slugs.update(slugify(s) for s in kw.keys())
    slugs.discard("")
    return slugs


def load_existing_electrical() -> list[dict]:
    master = json.loads((ROOT / "data" / "job-types-master.json").read_text())
    out = []
    seen = set()
    for row in master.get("jobs", []):
        if row.get("service") != SERVICE:
            continue
        slug = slugify(row.get("slug") or "")
        if not slug or slug in seen:
            continue
        seen.add(slug)
        out.append(
            {
                "slug": slug,
                "name": row.get("name") or display_name(slug),
                "service": SERVICE,
                "related": slugify(row.get("related") or "eicr") or "eicr",
            }
        )
    return out


# Curated Electrical jobs covering EICR, rewire, consumer unit, PAT, emergency
# lighting (electrical), EV charger, sockets, lighting circuits, three-phase,
# landlord electrical, and related verbs. Slugs that already exist are skipped.
NEW_NAMES = [
    # EICR / inspection / certificates
    "HMO EICR",
    "Void EICR",
    "Change of Tenancy EICR",
    "Five Year EICR",
    "Unsatisfactory EICR Remedial",
    "EICR C1 Remedial",
    "EICR C2 Remedial",
    "EICR FI Code Investigation",
    "Industrial EICR",
    "Office EICR",
    "Retail EICR",
    "Warehouse EICR",
    "School EICR",
    "Care Home EICR",
    "Student Let EICR",
    "New Tenancy Electrical Certificate",
    "Electrical Condition Report",
    "PIR Electrical",
    "Electrical Testing Certificate",
    "Visual Electrical Inspection",
    "Electrical Dead Testing",
    "Electrical Live Testing",
    "Insulation Resistance Test",
    "Earth Fault Loop Impedance Test",
    "RCD Testing",
    "RCD Trip Time Test",
    "Ze Zs Testing",
    "Continuity Testing",
    "Polarity Testing",
    "Board Schedule of Circuits",
    "Overdue EICR",
    "EICR Remedial Works",
    "Coded Electrical Remedials",
    "Electrical Certificate Renewal",
    "Electrical Inspection Certificate",
    "Minor Works Certificate",
    "Electrical Installation Certificate",
    "EIC Certificate",
    "Part P Notification",
    "Building Control Electrical Notification",
    "Electrical Sign Off",
    "Landlord Electrical Compliance",
    "HMO Electrical Safety",
    "Electrical Risk Assessment",
    "Pre Purchase Electrical Survey",
    "Electrical Snagging",
    "New Build Electrical Snagging",
    "Insurance Electrical Report",
    "Communal Area EICR",
    "Block EICR",
    "Landlord Portfolio EICR",
    "Same Day EICR",
    "Weekend EICR",
    "EICR After Remedials",
    "Satisfactory EICR Recheck",
    "Limitation Electrical Inspection",
    # Rewire
    "Full Rewire",
    "Complete House Rewire",
    "Flat Rewire",
    "Apartment Rewire",
    "Commercial Rewire",
    "Office Rewire",
    "Shop Rewire",
    "Industrial Rewire",
    "HMO Rewire",
    "Period Property Rewire",
    "Garage Rewire",
    "Extension Rewire",
    "Emergency Rewire",
    "Fire Damage Rewire",
    "Flood Damage Electrical Rewire",
    "Rewire with Making Good",
    "Second Fix Electrical",
    "Rewire Consumer Unit Package",
    "Rewire Quote",
    "How Much to Rewire",
    "Partial House Rewire",
    "Upstairs Rewire",
    "Downstairs Rewire",
    "Landlord Void Rewire",
    "Student House Rewire",
    "Terrace Rewire",
    "Victorian House Rewire",
    "Rewire Certification",
    # Consumer unit / fuse board
    "Dual RCD Consumer Unit",
    "SPD Consumer Unit",
    "Metal Consumer Unit",
    "Insulated Consumer Unit",
    "Garage Consumer Unit",
    "Three Phase Consumer Unit",
    "Consumer Unit Relocation",
    "Split Load Consumer Unit",
    "Fuseboard Upgrade",
    "Old Fuse Box Replacement",
    "Rewireable Fuse Replacement",
    "MCB Replacement",
    "RCBO Replacement",
    "RCD Replacement",
    "Surge Protection Device Install",
    "Consumer Unit Inspection",
    "AFDD Consumer Unit",
    "Arc Fault Detection Device",
    "18th Edition Upgrade",
    "Amendment 2 Consumer Unit",
    "EV Ready Consumer Unit",
    "Main Switch Replacement",
    "Consumer Unit Labelling",
    "Garage Fuseboard",
    "Outbuilding Consumer Unit",
    "High Integrity Consumer Unit",
    "Metal Clad Consumer Unit",
    "Wylex Consumer Unit Upgrade",
    "Hager Consumer Unit Upgrade",
    # Sockets / points
    "Outdoor Socket",
    "USB Socket Installation",
    "Kitchen Socket Installation",
    "Fused Spur Installation",
    "Cooker Switch Installation",
    "Shower Pull Cord",
    "Extractor Fan Isolation",
    "Additional Lighting Point",
    "Dimmer Switch Installation",
    "Two Way Lighting",
    "Garden Socket",
    "Metal Clad Socket",
    "Industrial Socket Installation",
    "Commando Socket",
    "32 Amp Supply",
    "63 Amp Supply",
    "Double Socket Installation",
    "Socket Relocation",
    "Socket Replacement",
    "Fused Connection Unit",
    "Cooker Circuit Installation",
    "Hob Connection Electrical",
    "Oven Connection Electrical",
    "Induction Hob Connection",
    "Dishwasher Fused Spur",
    "Washer Isolator",
    "Kitchen Extraction Isolator",
    "Loft Power Supply",
    "Shed Power Supply",
    "Garage Electrical Installation",
    "Outdoor Supply",
    "Outbuilding Electrical Supply",
    "Summer House Electrics",
    "Workshop Electrical Installation",
    # Lighting circuits
    "Outdoor Lighting Circuit",
    "Garden Lighting Installation",
    "Security Lighting Electrical",
    "LED Downlight Installation",
    "Loft Lighting Circuit",
    "Bathroom Lighting Circuit",
    "Kitchen Lighting Circuit",
    "Three Phase Lighting",
    "Lighting Maintenance Electrical",
    "Lighting Design Electrical",
    "PIR Lighting Install",
    "Occupancy Sensor Lighting",
    "Floodlight Installation",
    "Car Park Lighting Electrical",
    "Lighting Control System",
    "Emergency Exit Lighting Electrical",
    "Two Way Switching Installation",
    "Intermediate Switching",
    "Outside Light Installation",
    "LED Conversion Electrical",
    "Spot Light Installation",
    "Pendant Lighting Installation",
    # Three-phase
    "Three Phase Supply",
    "Three Phase Board",
    "Three Phase Distribution",
    "Three Phase Rewire",
    "Three Phase Testing",
    "Three Phase Upgrade",
    "Three Phase EV Charger",
    "Three Phase Motor Circuit",
    "Single to Three Phase",
    "Three Phase Meter Tails",
    "Three Phase Isolator",
    "Three Phase Distribution Board",
    "Commercial Three Phase Install",
    "Industrial Three Phase Testing",
    # EV chargers
    "EV Charger",
    "EV Charge Point",
    "EV Charger Upgrade",
    "EV Charger Relocation",
    "EV Charger Isolator",
    "EV Charger Load Balancing",
    "Workplace EV Charging",
    "Tethered EV Charger",
    "Untethered EV Charger",
    "7kW EV Charger",
    "22kW EV Charger",
    "Domestic EV Charge Point",
    "Commercial EV Charge Point",
    "EV Charger Consumer Unit Upgrade",
    "Smart EV Charger Installation",
    "Rolec EV Charger Installation",
    "Ohme EV Charger Installation",
    "Wallbox EV Charger Installation",
    # PAT (electrical)
    "Office PAT",
    "Commercial PAT Testing",
    "Industrial PAT",
    "Landlord PAT Testing",
    "Portable Appliance Test",
    "PAT Testing Certificate",
    "PAT Testing Near Me",
    "IT Equipment PAT",
    "Hire Equipment PAT",
    "Annual PAT Testing",
    "Retail PAT Testing Electrical",
    "Warehouse PAT Testing Electrical",
    "School PAT Testing Electrical",
    "Microwave PAT Testing",
    "PAT Testing Register",
    "Failed PAT Repair",
    # Emergency lighting (electrical works, not the fire service hub)
    "Emergency Lighting Circuit Install",
    "Emergency Light Conversion Electrical",
    "Maintained Emergency Lighting Electrical",
    "Bulkhead Emergency Light Electrical",
    "Emergency Lighting Supply Circuit",
    "Self Test Emergency Light Wiring",
    "Central Battery Emergency Lighting Electrical",
    "Emergency Lighting Isolator",
    # Landlord electrical
    "Landlord Electrical Works",
    "Void Property Electrics",
    "Tenant Electrical Repair",
    "Landlord Electrical Package",
    "Landlord Electrical Inspection Visit",
    "HMO Electrical Upgrade",
    "Let Ready Electrical Package",
    "Change of Tenancy Electrical Check",
    "Landlord Electrical Remedial",
    "Multi Let Electrical Certificate",
    "Agent Electrical Compliance Pack",
    # Faults / emergency
    "Electrical Fault",
    "No Power Electrician",
    "Tripped RCD",
    "Buzzing Socket",
    "Burning Smell Electrical",
    "Loose Socket Repair",
    "Light Not Working",
    "Power Cut Investigation",
    "Damaged Cable Repair",
    "Water Damaged Electrics",
    "Tripped Breaker Repair",
    "Lost Neutral Investigation",
    "Overheating Socket Repair",
    "Sparking Socket Repair",
    # Cables / containment / first-adjacent
    "SWA Cable Installation",
    "Armoured Cable Run",
    "Sub Main Installation",
    "Meter Tails Upgrade",
    "Isolator Switch Installation",
    "Rotary Isolator",
    "Emergency Stop Circuit",
    "Trace Heating Electrical",
    "Frost Stat Wiring",
    "Henley Block Install",
    "Cable Containment Electrical",
    "Trunking Installation Electrical",
    "Conduit Installation Electrical",
    # Solar / storage (electrical connection)
    "Solar PV Electrical Connection",
    "Battery Storage Install",
    "Hybrid Inverter Electrical",
    "Generation Meter Electrical",
    "Export Limit Device",
    "Solar Isolator Installation",
    # Bathroom / wet
    "Wet Room Electrical",
    "IP Rated Bathroom Electrics",
    "Zone 1 Bathroom Lighting",
    "Bathroom Extractor Electrical",
    "Shower Circuit",
    "Electric Shower Repair",
    "Electric Shower Upgrade",
    "Towel Rail Electrical",
    "Immersion Heater Installation",
    "Extractor Fan Installation Electrical",
    # Heating / extras
    "Underfloor Heating Electrical",
    "Electric Heating Installation",
    "Storage Heater Installation",
    "Doorbell Transformer",
    "TV Aerial Point",
    "Ethernet Cabling Electrical",
    "Cat6 Cabling",
    "Satellite Point",
    "Media Plate Installation",
    "Smoke Alarm Wiring Electrical",
    # Commercial / FM
    "Electrical PPM",
    "Electrical Service Contract",
    "Multi Site Electrical Maintenance",
    "Facilities Electrical Support",
    "Data Cabinet Power",
    "Comms Room Electrical",
    "Server Room Electrical",
    "UPS Installation Electrical",
    "Generator Changeover",
    "ATS Electrical",
    "Electrical Survey",
    "Electrical Condition Survey",
    # Trade identity / local intent
    "18th Edition Electrician",
    "Registered Electrician",
    "Approved Electrician",
    "Qualified Electrician",
    "Local Electrician",
    "Commercial Electrical Contractor",
    "Domestic Electrical Contractor",
    "Part P Electrician",
    "NICEIC Electrician",
    "Emergency Electrical Call Out",
]


def related_for(name: str, slug: str) -> str:
    hay = f"{name} {slug}".lower()
    rules = (
        ("eicr", "eicr"),
        ("periodic", "eicr"),
        ("inspection", "electrical-inspection"),
        ("certificate", "electrical-safety-certificate"),
        ("rewire", "rewire"),
        ("consumer unit", "consumer-unit"),
        ("fuse", "fuse-board"),
        ("rcbo", "rcbo-consumer-unit"),
        ("rcd", "consumer-unit"),
        ("pat", "pat-testing"),
        ("portable appliance", "portable-appliance-testing"),
        ("ev ", "ev-charger-installation"),
        ("charge", "ev-charger-installation"),
        ("three phase", "three-phase-installation"),
        ("socket", "socket-installation"),
        ("lighting", "lighting-circuit-installation"),
        ("landlord", "landlord-eicr"),
        ("emergency light", "emergency-lighting-electrical-works"),
        ("solar", "solar-inverter-installation"),
        ("battery", "battery-storage-electrical"),
        ("shower", "electric-shower-installation"),
        ("fault", "electrical-fault-finding"),
        ("emergency", "emergency-electrician"),
    )
    for needle, rel in rules:
        if needle in hay:
            return rel
    return "electrical-services"


def systematic_candidates() -> list[str]:
    properties = [
        "HMO",
        "Office",
        "Warehouse",
        "Retail Unit",
        "Care Home",
        "School",
        "Flat",
        "Terrace House",
        "New Build",
        "Landlord Void",
        "Student Let",
        "Workshop",
        "Farm Building",
        "Church Hall",
        "Sports Club",
        "GP Surgery",
        "Nursery",
        "Hotel",
        "Pub",
        "Restaurant",
    ]
    works = [
        "EICR",
        "Rewire",
        "Consumer Unit Upgrade",
        "PAT Testing",
        "EV Charger Installation",
        "Socket Installation",
        "Lighting Circuit Installation",
        "Three Phase Installation",
        "Electrical Remedial Works",
        "Distribution Board Upgrade",
        "Emergency Lighting Electrical Works",
        "Landlord Electrical Certificate",
        "Fixed Wire Testing",
        "Electrical Maintenance Visit",
        "Minor Works Certificate",
    ]
    out = []
    for work in works:
        for place in properties:
            out.append(f"{place} {work}")
    return out


def main() -> None:
    existing_all = load_existing_slugs()
    jobs = load_existing_electrical()
    have = {j["slug"] for j in jobs}

    added = []
    for name in NEW_NAMES + systematic_candidates():
        slug = slugify(name)
        if not slug or slug in have:
            continue
        # Allow reclaiming a slug only if it is not already used by another service.
        if slug in existing_all and slug not in have:
            continue
        jobs.append(
            {
                "slug": slug,
                "name": name if name[0].isupper() or name[0].isdigit() else display_name(slug),
                "service": SERVICE,
                "related": related_for(name, slug),
            }
        )
        have.add(slug)
        added.append(slug)
        if len(jobs) >= TARGET:
            break

    if len(jobs) < TARGET:
        raise SystemExit(f"Only built {len(jobs)} electrical jobs; need {TARGET}")

    jobs = jobs[:TARGET]
    # Keep related pointers valid within the electrical set when possible.
    slugs = {j["slug"] for j in jobs}
    for row in jobs:
        if row["related"] not in slugs:
            row["related"] = "eicr" if "eicr" in slugs else jobs[0]["slug"]

    payload = {
        "count": TARGET,
        "generated": __import__("datetime").datetime.now(__import__("datetime").timezone.utc).replace(microsecond=0).isoformat().replace("+00:00", "Z"),
        "jobs": jobs,
    }
    out = ROOT / "data" / "electrical-jobs.json"
    out.write_text(json.dumps(payload, indent=4, ensure_ascii=False) + "\n")
    print(f"Wrote {len(jobs)} jobs → {out}")
    print(f"existing_electrical_kept={TARGET - len(added)} added={len(added)}")


if __name__ == "__main__":
    main()
