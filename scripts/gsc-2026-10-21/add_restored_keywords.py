#!/usr/bin/env python3
"""GSC 21 Oct: restore ranking keyword slugs that were missing from keywords.json
(their hub and town URLs 301'd to /pages/areas). Adds each slug with its own copy
and puts the 65 ranking keyword x town paths in gsc-keep-towns.json so they serve 200.
Prices: EICR 'from £160'; everything else POA. Gas: 'carried out by Gas Safe registered engineers'."""
import json, re, sys, pathlib
ROOT = pathlib.Path(__file__).resolve().parents[2]
KW = ROOT / "website/data/keywords.json"
KEEP = ROOT / "website/data/gsc-keep-towns.json"
# slug: (name, service, related, what-it-covers sentence)
S = {
 "5-year-eicr-certificate": ("5-Year EICR Certificate", "electrical", "eicr", "A five-year Electrical Installation Condition Report for rented homes and HMOs, with the circuits tested, observations coded C1, C2, C3 or FI and the certificate issued for your tenant and council file."),
 "eicr-cost-near-me": ("EICR Cost Near Me", "electrical", "eicr", "A local EICR priced from £160 for a typical property, with the final figure confirmed once we know the number of circuits and boards on site."),
 "acrylic-render-cost": ("Acrylic Render Cost", "rendering", "external-rendering", "Thin-coat acrylic render over a prepared base coat and mesh, with the price set by wall area, scaffold access and the repairs needed before the finish goes on."),
 "external-rendering-cost": ("External Rendering Cost", "rendering", "external-rendering", "External render on houses and small blocks, from hacking off failed render to beads, base coat, mesh and the final silicone, acrylic or sand and cement finish."),
 "rendering-prices": ("Rendering Prices", "rendering", "external-rendering", "Clear rendering quotes broken down by wall area, access, preparation and finish, so you can compare silicone, acrylic, monocouche and sand and cement systems."),
 "render-and-paint-package-cost": ("Render and Paint Package Cost", "rendering", "external-rendering", "A combined package to repair and re-render tired elevations and finish them with a breathable masonry paint in one visit under one scaffold."),
 "air-source-heat-pumps-site-attendance": ("Air Source Heat Pumps Site Attendance", "heating", "heating-services", "Site attendance for air source heat pump jobs, covering surveys, heat-loss checks, radiator sizing and the follow-up visits needed after the unit is fitted."),
 "radiator-balancing-service": ("Radiator Balancing Service", "heating", "heating-services", "Balancing the lockshield valves on every radiator so the whole system heats evenly, the boiler cycles less and cold rooms at the end of the run warm up."),
 "basement-tanking-cost": ("Basement Tanking Cost", "damp-proofing", "basement-tanking-support", "Cementitious tanking or cavity drain membranes for cellars and basements, priced after a survey of the walls, floor, water ingress and how the space will be used."),
 "damp-proofing-prices": ("Damp Proofing Prices", "damp-proofing", "basement-tanking-support", "Damp proofing quotes that separate rising damp, penetrating damp and condensation, so you only pay for the treatment the survey shows is needed."),
 "damp-proofing-service-cost": ("Damp Proofing Service Cost", "damp-proofing", "basement-tanking-support", "A damp proofing service from first survey to injected DPC, replastering and a written record of the works for your files."),
 "damp-survey-cost": ("Damp Survey Cost", "damp-proofing", "basement-tanking-support", "A damp survey with moisture readings, a look at ground levels, gutters and ventilation, and a written report that names the cause before any work is quoted."),
 "penetrating-damp-repair-cost": ("Penetrating Damp Repair Cost", "damp-proofing", "basement-tanking-support", "Finding and fixing the route water takes through walls, from pointing and render cracks to failed gutters, sills and flashings, then making good inside."),
 "building-surveys-near-me": ("Building Surveys Near Me", "building-surveys", None, "Local building surveys for buyers, owners and landlords, covering structure, roof, damp, services and the defects worth budgeting for."),
 "dilapidations-support-survey-quote": ("Dilapidations Support Survey Quote", "building-surveys", None, "Dilapidations surveys at the end of a lease, with a schedule of the repairs, reinstatement and decoration needed and the works to close it out."),
 "boiler-service-and-repair-package": ("Boiler Service and Repair Package", "gas-systems", None, "An annual boiler service with a repair visit included in one package, carried out by Gas Safe registered engineers with flue and safety checks recorded."),
 "boiler-service-gas-safe": ("Boiler Service by Gas Safe Registered Engineers", "gas-systems", None, "A full boiler service carried out by Gas Safe registered engineers, with combustion analysis, flue checks, seals and the service record left with you."),
 "commercial-kitchen-fire-safety-package": ("Commercial Kitchen Fire Safety Package", "kitchen-fire-suppression", None, "Fire safety for commercial kitchens in one package, covering wet chemical suppression, extinguishers, the gas interlock and the paperwork your insurer asks for."),
 "commercial-plumbing-support": ("Commercial Plumbing Support", "plumbing", "plumbing-services", "Planned and reactive plumbing for shops, offices and blocks, from leaks and blocked wastes to washroom repairs, water heaters and isolation valves."),
 "dishwasher-plumbing-install": ("Dishwasher Plumbing Install", "plumbing", "kitchen-plumbing", "Plumbing in a dishwasher with a new valve, a correctly looped waste into the sink trap or standpipe and a leak check under load."),
 "soil-stack-repair": ("Soil Stack Repair", "plumbing", "plumbing-services", "Repairing or replacing cracked, leaking or blocked soil stacks and their joints, internal or external, with access and making good included in the scope."),
 "commercial-washroom-fit-out-cost": ("Commercial Washroom Fit-Out Cost", "commercial-fit-out", None, "Washroom fit-outs for offices, shops and venues, including cubicles, sanitaryware, accessible facilities, wall finishes and the plumbing behind them."),
 "custom-storage-joinery-quote": ("Custom Storage Joinery Quote", "joinery", None, "Made-to-measure alcove units, wardrobes, under-stair storage and shelving, built and fitted by joiners to suit your room and finish."),
 "joinery-stockport": ("Joinery Stockport", "joinery", None, "Joinery from our Stockport base, from doors, skirting and architrave to stairs, built-in storage and first and second fix on renovations."),
 "decking-installation-cost": ("Decking Installation Cost", "landscaping", None, "Timber or composite decking on a level, ventilated frame with joist spacing, fixings and edge trims chosen for the boards you pick."),
 "dry-and-wet-risers-prices": ("Dry and Wet Risers Prices", "dry-risers", None, "Dry and wet riser testing, servicing and repairs to BS 9990, with the annual pressure test and six-monthly visual checks recorded."),
 "emergency-lighting-survey-cost": ("Emergency Lighting Survey Cost", "emergency-lighting", None, "A survey of escape routes, fittings and durations against BS 5266, with a written list of the gaps and the work needed to put them right."),
 "evacuation-strategy-document": ("Evacuation Strategy Document", "fire-risk-assessments", None, "A written evacuation strategy that sets out stay-put, simultaneous or phased evacuation, refuges and the plans for people who need help to leave."),
 "fire-strategy-fit-out-coordination": ("Fire Strategy Fit-Out Coordination", "fire-compartmentation", None, "Keeping a fit-out in line with the building's fire strategy, from compartment lines and fire stopping to doors, alarms and the handover pack."),
 "epc-insulation-works-cost": ("EPC Insulation Works Cost", "insulation", None, "Insulation works that lift an EPC rating, such as loft top-ups, cavity fill, internal wall insulation and draught-proofing, scoped against your current certificate."),
 "extension-building-package-quote": ("Extension Building Package Quote", "extensions", None, "A single quote for an extension covering groundworks, shell, roof, windows, first and second fix and finishes, with stages agreed up front."),
 "extension-fixed-price-package": ("Extension Fixed-Price Package", "extensions", None, "A fixed-scope extension package with the drawings, specification and stages agreed before work starts, so changes are priced before they happen."),
 "loft-conversion-project-management": ("Loft Conversion Project Management", "loft-conversions", None, "Managing a loft conversion from drawings and building control to steels, dormer, stairs, insulation and the completion certificate."),
 "external-cctv-installers": ("External CCTV Installers", "cctv", None, "External CCTV with weatherproof cameras placed to cover drives, doors and gardens, cabled neatly and set up for viewing on your phone."),
 "hmo-plastering-package": ("HMO Plastering Package", "plastering", None, "Plastering for HMOs between tenancies, from patch repairs and skimming to boarding, so rooms are ready to decorate and re-let."),
 "patch-plastering-cost": ("Patch Plastering Cost", "plastering", None, "Patch plastering to fill holes, cracks and blown areas after electrical, plumbing or damp work, blended in ready for paint."),
 "reactive-maintenance-call-out-cost": ("Reactive Maintenance Call-Out Cost", "building-maintenance", None, "Reactive maintenance call-outs for leaks, broken doors and locks, damaged fittings and small repairs that cannot wait for a planned visit."),
 "roof-tile-replacement-cost": ("Roof Tile Replacement Cost", "roofing", None, "Replacing slipped, cracked or missing tiles and slates, with the battens, felt and ridge checked while the roof is open."),
}

def entry(slug, name, service, related, what, svc_name):
    low = name.lower()
    price = "EICRs are from £160 for a typical property; larger installations are POA until we know the circuit count." if "eicr" in slug else "Pricing is POA: we quote in writing once we have seen the job."
    gas = slug.startswith("boiler")
    return {
        "name": name,
        "service": service,
        "related": related or slug,
        "intro": f"{what} Icomply Property Services cover Greater Manchester and the North West from Stockport. {price}",
        "body": (f"{what} We start by asking for your postcode, the property type and what you have noticed, then book a visit to see the job. "
                 f"{price} You get a written scope that says what is included, what is not and how long it should take. "
                 + ("All gas work is carried out by Gas Safe registered engineers. " if gas else "")
                 + "Call 07517 806082 or email icomplypropertyservices@gmail.com to arrange a visit."),
        "meta_desc": "",
        "seo_keywords": f"{low}, {low} manchester, {low} stockport, {low} north west, {svc_name.lower()}",
        "focus_points": [
            f"A visit and written quote for {low}",
            "Local team from Stockport covering the North West",
            "Clear scope with what is and is not included",
            "Same/Next-Day where the diary allows",
        ],
        "faq": [
            [f"What does {low} include?", what],
            [f"How much is {low}?", price],
            [f"Do you cover my town for {low}?", "We cover Greater Manchester, Lancashire, Cheshire, Merseyside and the wider North West from Stockport."],
            [f"How do I book {low}?", "Call 07517 806082 or email icomplypropertyservices@gmail.com with your postcode and a short description of the job."],
        ],
    }

def meta(name):
    tail = ". Free quote, call 07517 806082"
    opts = [
        f"{name} in Greater Manchester and the North West. Local team from Stockport, written scope and clear dates",
        f"{name} in Greater Manchester and the North West. Local team from Stockport and a written scope",
        f"{name} across Greater Manchester and the North West from our Stockport team",
        f"{name} across Greater Manchester and the North West from our local Stockport team",
        f"{name} across the North West from our Stockport team, with a written scope",
        f"{name} across the North West from our Stockport team",
        f"{name} in the North West from Stockport, written scope",
        f"{name} in the North West from Stockport",
        f"{name} from our Stockport team",
    ]
    for o in opts:
        if 140 <= len(o + tail) <= 155:
            return o + tail
    raise SystemExit(f"no meta fits 140-155 for {name}")

kw = json.loads(KW.read_text())
svc = {}
try:
    svc = dict(json.loads((ROOT / "website/data/services.json").read_text()))
except Exception:
    pass
added = []
for slug, (name, service, related, what) in S.items():
    if slug in kw:
        continue
    if related and related not in kw:
        related = None
    e = entry(slug, name, service, related, what, svc.get(service, service.replace("-", " ")))
    e["meta_desc"] = meta(name)
    kw[slug] = e
    added.append(slug)
KW.write_text(json.dumps(kw, indent=2, ensure_ascii=False) + "\n")
keep = json.loads(KEEP.read_text())
paths = set(keep["paths"]); order = list(keep["paths"])
new = 0
for f in sys.argv[1:]:
    for u in pathlib.Path(f).read_text().split():
        p = re.sub(r"^https?://[^/]+", "", u)
        if p not in paths:
            paths.add(p); order.append(p); new += 1
keep["paths"] = order
KEEP.write_text(json.dumps(keep, indent=1, ensure_ascii=False) + "\n")
print(f"added {len(added)} keyword slugs, {new} keep paths")
