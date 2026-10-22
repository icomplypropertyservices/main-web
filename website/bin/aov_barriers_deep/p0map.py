"""Map each P0 slug to (line, core, intent, sector, thing label, variant note)."""
from __future__ import annotations

ACRONYMS = {
    "aov": "AOV", "aovs": "AOVs", "nshevs": "NSHEVS", "shevs": "SHEVS", "hmo": "HMO", "hgv": "HGV",
    "anpr": "ANPR", "en": "EN", "came": "CAME", "se": "SE", "colt": "Colt", "4m": "4m", "5m": "5m", "6m": "6m",
}

INTENT_SUFFIXES = [
    ("emergency-repair", "emergency-repair"),
    ("installation-near-me", ("installation", "near-me")),
    ("installer-near-me", ("installer", "near-me")),
    ("installers-near-me", ("installers", "near-me")),
    ("engineer-near-me", ("engineer", "near-me")),
    ("engineers-near-me", ("engineers", "near-me")),
    ("maintenance-near-me", ("maintenance", "near-me")),
    ("repair-near-me", ("repair", "near-me")),
    ("service-near-me", ("service", "near-me")),
    ("servicing-near-me", ("servicing", "near-me")),
    ("replacement-near-me", ("replacement", "near-me")),
    ("installation", "installation"), ("installer", "installer"), ("installers", "installers"),
    ("near-me", "near-me"), ("repair", "repair"), ("maintenance", "maintenance"),
    ("servicing", "servicing"), ("service", "service"), ("commissioning", "commissioning"),
    ("engineers", "engineers"), ("engineer", "engineer"), ("replacement", "replacement"),
    ("shortening", "shortening"), ("survey", "survey"),
]

AOV_SECTORS = ["apartment-block", "car-park", "care-home", "commercial", "factory", "high-rise", "hmo"]

# product slug part -> (core key, thing label)
AOV_PRODUCTS = [
    ("aov-control-panel", "aov-control-panel", "AOV control panel"),
    ("aov-actuator", "aov-actuator", "AOV actuator"),
    ("aov-system", "aov-system", "AOV system"),
    ("aov-panel", "aov-control-panel", "AOV panel"),
    ("louvre-aov", "louvre-aov", "louvre AOV"),
    ("window-aov", "window-aov", "window AOV"),
    ("mechanical-smoke-ventilation", "mechanical-smoke-ventilation", "mechanical smoke ventilation"),
    ("natural-smoke-ventilation", "natural-smoke-ventilation", "natural smoke ventilation"),
    ("smoke-ventilation", "smoke-ventilation", "smoke ventilation"),
    ("smoke-control", "smoke-control", "smoke control"),
    ("smoke-curtain", "smoke-curtain", "smoke curtain"),
    ("smoke-shaft", "smoke-shaft", "smoke shaft"),
    ("corridor-smoke-vent", "corridor-smoke-vent", "corridor smoke vent"),
    ("automatic-smoke-vent", "smoke-vent", "automatic smoke vent"),
    ("automatic-opening-vent", "aov", "automatic opening vent"),
    ("smoke-vent", "smoke-vent", "smoke vent"),
    ("nshevs", "nshevs", "NSHEVS"),
    ("shevs", "shevs", "SHEVS"),
    ("fire-rated-aov", "fire-rated-aov", "fire-rated AOV"),
    ("en-12101", "en-12101", "EN 12101 smoke control"),
    ("colt-aov", "colt-aov", "Colt AOV"),
    ("se-controls-aov", "se-controls-aov", "SE Controls AOV"),
    ("aov", "aov", "AOV"),
]

# barrier: exact slug -> (core, intent, thing)
BARRIER_MAP = {
    "manual-barrier": ("manual", "guide", "manual barrier"),
    "manual-barrier-installation": ("manual", "installation", "manual barrier"),
    "manual-barrier-near-me": ("manual", "near-me", "manual barrier"),
    "manual-rising-arm-barrier": ("manual", "guide", "manual rising arm barrier"),
    "manual-car-park-barrier": ("manual", "guide", "manual car park barrier"),
    "heavy-duty-manual-barrier": ("heavy-duty", "guide", "heavy-duty manual barrier"),
    "reinforced-barrier-arm": ("heavy-duty", "replacement", "reinforced barrier arm"),
    "manual-barrier-repair": ("manual", "repair", "manual barrier"),
    "manual-barrier-service": ("manual", "service", "manual barrier"),
    "manual-height-barrier": ("manual-height", "guide", "manual height barrier"),
    "manual-swing-height-barrier": ("manual-height", "installation", "manual swing height barrier"),
    "manual-width-restriction-barrier": ("manual-width", "guide", "manual width restriction barrier"),
    "manual-swing-arm-barrier": ("manual-swing-arm", "guide", "manual swing arm barrier"),
    "manual-barrier-arm-length": ("arm-length", "guide", "manual barrier arm length"),
    "barrier-specification": ("survey-spec", "guide", "barrier specification"),
    "height-restriction-barrier": ("height-restriction", "guide", "height restriction barrier"),
    "height-restriction-barrier-installation": ("height-restriction", "installation", "height restriction barrier"),
    "height-restriction-barrier-repair": ("height-restriction", "repair", "height restriction barrier"),
    "height-restrictor": ("height-restriction", "guide", "height restrictor"),
    "height-restrictor-installation": ("height-restriction", "installation", "height restrictor"),
    "height-barrier": ("height-restriction", "guide", "height barrier"),
    "height-barrier-near-me": ("height-restriction", "near-me", "height barrier"),
    "vehicle-height-restriction-barrier": ("height-restriction", "guide", "vehicle height restriction barrier"),
    "car-park-height-barrier": ("height-restriction", "guide", "car park height barrier"),
    "car-park-height-barrier-installation": ("height-restriction", "installation", "car park height barrier"),
    "swing-height-barrier": ("manual-height", "guide", "swing height barrier"),
    "barrier-height-clearance": ("height-clearance", "guide", "barrier height clearance"),
    "boom-height-clearance": ("height-clearance", "guide", "boom height clearance"),
    "barrier-vertical-clearance": ("height-clearance", "guide", "barrier vertical clearance"),
    "low-headroom-barrier": ("height-clearance", "guide", "low headroom barrier"),
    "articulated-arm-barrier": ("articulated-arm", "guide", "articulated arm barrier"),
    "articulated-arm-barrier-installation": ("articulated-arm", "installation", "articulated arm barrier"),
    "undercroft-car-park-barrier": ("undercroft", "guide", "undercroft car park barrier"),
    "hgv-height-restriction-barrier": ("hgv-height", "guide", "HGV height restriction barrier"),
    "height-barrier-and-rising-arm-barrier": ("height-and-rising-arm", "guide", "height barrier and rising arm barrier"),
    "width-restriction-barrier": ("width-restriction", "guide", "width restriction barrier"),
    "width-restriction-barrier-installation": ("width-restriction", "installation", "width restriction barrier"),
    "width-restrictor": ("width-restriction", "guide", "width restrictor"),
    "barrier-opening-width": ("opening-width", "guide", "barrier opening width"),
    "barrier-lane-width": ("opening-width", "guide", "barrier lane width"),
    "single-lane-barrier": ("opening-width", "guide", "single lane barrier"),
    "dual-lane-barrier": ("opening-width", "guide", "dual lane barrier"),
    "wide-opening-barrier": ("opening-width", "guide", "wide opening barrier"),
    "swing-arm-barrier": ("manual-swing-arm", "installation", "swing arm barrier"),
    "long-arm-barrier": ("arm-length", "installation", "long arm barrier"),
    "barrier-arm-length": ("arm-length", "guide", "barrier arm length"),
    "barrier-arm-replacement": ("arm-length", "replacement", "barrier arm"),
    "barrier-arm-replacement-near-me": ("arm-length", ("replacement", "near-me"), "barrier arm"),
    "barrier-arm-shortening": ("arm-length", "shortening", "barrier arm"),
    "4m-barrier-arm": ("arm-length", "guide", "4m barrier arm"),
    "5m-barrier-arm": ("arm-length", "guide", "5m barrier arm"),
    "6m-barrier-arm": ("arm-length", "guide", "6m barrier arm"),
    "barrier-clearance-survey": ("survey-spec", "survey", "barrier clearance survey"),
    "barrier-installation": ("general", "installation", "vehicle barrier"),
    "barrier-installer": ("general", "installer", "vehicle barrier"),
    "barrier-near-me": ("general", "near-me", "vehicle barrier"),
    "barrier-repair": ("general", "repair", "vehicle barrier"),
    "barrier-maintenance": ("general", "maintenance", "vehicle barrier"),
    "barrier-engineer": ("general", "engineer", "vehicle barrier"),
    "emergency-barrier-repair": ("general", "emergency-repair", "vehicle barrier"),
    "barrier-service": ("general", "service", "vehicle barrier"),
    "rising-arm-barrier": ("rising-arm", "guide", "rising arm barrier"),
    "rising-arm-barrier-installation": ("rising-arm", "installation", "rising arm barrier"),
    "rising-arm-barrier-repair": ("rising-arm", "repair", "rising arm barrier"),
    "car-park-barrier": ("car-park", "guide", "car park barrier"),
    "car-park-barrier-installation": ("car-park", "installation", "car park barrier"),
    "car-park-barrier-repair": ("car-park", "repair", "car park barrier"),
    "barrier-for-car-parks": ("car-park", "installer", "car park barrier"),
    "automatic-barrier": ("automatic", "guide", "automatic barrier"),
    "automatic-barrier-repair": ("automatic", "repair", "automatic barrier"),
    "boom-barrier": ("boom", "guide", "boom barrier"),
    "parking-barrier": ("boom", "installation", "parking barrier"),
    "anpr-barrier-system": ("anpr", "guide", "ANPR barrier system"),
    "came-barrier-installation": ("came", "installation", "CAME barrier"),
}

AOV_SPECIAL = {
    "aov-for-flats": ("aov", "guide", "flats", "AOV"),
    "smoke-ventilation": ("smoke-ventilation", "guide", None, "smoke ventilation"),
    "mechanical-smoke-ventilation": ("mechanical-smoke-ventilation", "guide", None, "mechanical smoke ventilation"),
    "natural-smoke-ventilation": ("natural-smoke-ventilation", "guide", None, "natural smoke ventilation"),
    "aov-control-panel": ("aov-control-panel", "guide", None, "AOV control panel"),
    "emergency-aov-installer": ("aov", "emergency-installer", None, "AOV"),
    "aov-emergency-repair": ("aov", "emergency-repair", None, "AOV"),
    "en-12101-installer": ("en-12101", "installer", None, "EN 12101 smoke control"),
}

# Manufacturer × job heads: town pages are Greater Manchester core (GM-60) only.
GM_ONLY = {"colt-aov-installation", "se-controls-aov-installation", "came-barrier-installation"}

AOV_HINTS = ("aov", "smoke", "nshevs", "shevs", "en-12101", "automatic-opening-vent")


def display_name(slug: str) -> str:
    parts = slug.split("-")
    out = []
    i = 0
    while i < len(parts):
        p = parts[i]
        if p == "en" and i + 1 < len(parts) and parts[i + 1].isdigit():
            out.append("EN " + parts[i + 1])
            i += 2
            continue
        if p == "se" and i + 1 < len(parts) and parts[i + 1] == "controls":
            out.append("SE Controls")
            i += 2
            continue
        if p == "near" and i + 1 < len(parts) and parts[i + 1] == "me":
            out.append("Near Me")
            i += 2
            continue
        out.append(ACRONYMS.get(p, p.capitalize() if p not in ("and", "for") else p))
        i += 1
    name = " ".join(out)
    for a, b in (("Fire Rated", "Fire-Rated"), ("High Rise", "High-Rise"), ("Heavy Duty", "Heavy-Duty")):
        name = name.replace(a, b)
    return name


def split_intent(rest: str):
    for suf, intent in INTENT_SUFFIXES:
        if rest == suf:
            return "", intent
        if rest.endswith("-" + suf):
            return rest[: -(len(suf) + 1)], intent
    return rest, "guide"


def classify(slug: str) -> dict:
    if slug in BARRIER_MAP:
        core, intent, thing = BARRIER_MAP[slug]
        return {"line": "barrier", "core": core, "intent": intent, "sector": None, "thing": thing}
    if slug in AOV_SPECIAL:
        core, intent, sector, thing = AOV_SPECIAL[slug]
        return {"line": "aov", "core": core, "intent": intent, "sector": sector, "thing": thing}
    sector = None
    rest = slug
    for s in AOV_SECTORS:
        if rest.startswith(s + "-"):
            sector, rest = s, rest[len(s) + 1:]
            break
    product, intent = split_intent(rest)
    for key, core, thing in AOV_PRODUCTS:
        if product == key:
            return {"line": "aov", "core": core, "intent": intent, "sector": sector, "thing": thing}
    raise ValueError(f"unmapped P0 slug: {slug}")
