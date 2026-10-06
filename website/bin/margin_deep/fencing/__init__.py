"""Fencing margin DEEP pack (lock FENCING-DEEP-LOCK-2026-10-05.md, P0 108).

Manual fencing and gates only: powered gate automation belongs to the electric gates pack.
"""
from __future__ import annotations

import hashlib

from .contexts import CONTEXTS
from .cores import CORES as _C1
from .cores2 import CORES as _C2
from .cores3 import CORES as _C3
from .cores4 import CORES as _C4
from .cores5 import CORES as _C5
from .cores6 import CORES as _C6
from .cores7 import CORES as _C7
from .intents import ANGLES, INTENTS
from .lenses1 import LENSES as _L1
from .lenses2 import LENSES as _L2
from .lenses3 import LENSES as _L3

CORES = {**_C1, **_C2, **_C3, **_C4, **_C5, **_C6, **_C7}
LENSES = {**_L1, **_L2, **_L3}

IMG = "/assets/images/lanes/fencing-deep/"

SHARED = [
    "iComply Property Services works from a workshop at 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Fencing enquiries can be sent by phone or WhatsApp on 07517 806082, or by email to info@icomplypropertyservices.co.uk, with the site address, a few photos and a rough idea of the length and height.",
    "Every fencing job is quoted price on application. The quote follows a survey or a review of clear photos and measurements and is based on a written scope, so you can see exactly what is included before agreeing to anything.",
    "iComply does not promise attendance or completion times in advance. Visits and installation dates are agreed directly with you, and you are kept informed if weather or ground conditions change the plan on the day.",
    "Waste from fencing work, including old panels, posts, concrete and offcuts, is removed from site as part of the agreed scope. Where materials can be recycled, they are separated and taken to the appropriate facility.",
    "Work is carried out with risk assessments appropriate to the job, care around buried services and protection for lawns, paving and planting next to the line. Operatives follow site rules and inductions on commercial, school and industrial premises.",
    "Where a specification, insurer or landlord sets requirements for a fence, the scope refers to them and the products are chosen to meet them. Certification of a product belongs to its manufacturer; iComply supplies and installs to the maker's instructions.",
]

TOWN_WHO = "homeowners, landlords, schools and businesses"

TOWN_ANGLES = [
    "Boundaries around {place} range from terraced back yards to business parks and depots, so the survey records which fence type suits each run before {name} is priced.",
    "Ground in and around {place} varies from clay to made ground and hard standing, which is why post foundations for {name} are chosen on site rather than assumed.",
    "Enquiries for {name} in {place} usually start with photos and rough measurements by WhatsApp or email, followed by a survey visit booked by arrangement.",
    "Sites in {place} often share boundaries with neighbours, footways or other businesses, so the line, height and finished face are agreed before {name} work starts.",
    "Exposure differs across {place}, and wind on open or elevated boundaries affects the panel, post and bracing choices made for {name}.",
    "Schools, landlords and businesses in {place} often need a written scope for approval or funding, and the {name} quote is set out to support that.",
]

BANNED = r"\b(automatic gates?|electric gates?|gate motors?|boilers?|fire alarms?|AOVs?|smoke vents?|dampers?|sprinklers?|roofing|gutters?|air conditioning|electrical installation|EICR|PAT testing)\b"

CORE_PREFIX = [
    ("868-twin-wire-mesh-fencing", "mesh-868"),
    ("358-mesh-fencing", "mesh-358"),
    ("steel-palisade-fencing", "steel-palisade"),
    ("security-fencing", "security"),
    ("palisade-fencing", "palisade"),
    ("weldmesh-fencing", "weldmesh"),
    ("mesh-fencing", "mesh"),
    ("closeboard-fencing", "closeboard"),
    ("panel-fencing", "panel-fencing"),
    ("fence-panels", "fence-panels"),
    ("industrial-fencing", "industrial"),
    ("commercial-fencing", "commercial"),
    ("perimeter-fencing", "perimeter"),
    ("temporary-fencing", "temporary"),
    ("anti-climb-fencing", "anti-climb"),
    ("chain-link-fencing", "chain-link"),
    ("steel-railings", "railings"),
    ("school-fencing", "school"),
    ("fence-gates", "fence-gates"),
    ("pedestrian-gate", "pedestrian-gate"),
    ("palisade-gate", "palisade-gate"),
    ("fencing", "fencing"),
]

ONE_OFFS = {
    "fence-installation": {"core": "fence", "intent": "installation"},
    "fence-repair": {"core": "fence", "intent": "repair"},
    "fence-replacement": {"core": "fence", "intent": "replacement"},
    "fence-erection": {"core": "fence", "intent": "installation", "context": "residential"},
    "fencing-contractors": {"core": "fencing", "intent": "contractors", "context": "commercial"},
    "fencing-company": {"core": "fencing", "intent": "contractors", "context": "residential"},
    "fencing-installers": {"core": "fence", "intent": "contractors"},
    "fencing-cost": {"core": "fencing", "intent": "cost"},
    "fencing-quote": {"core": "fence", "intent": "cost", "context": "residential"},
    "fence-posts-replacement": {"core": "posts", "intent": "replacement"},
    "concrete-fence-posts-installation": {"core": "posts", "intent": "installation"},
    "fence-post-repair": {"core": "posts", "intent": "repair"},
    "fallen-fence-repair": {"core": "fence", "intent": "emergency"},
    "temporary-fencing-hire": {"core": "temporary", "intent": "hire"},
    "site-hoarding-installation": {"core": "hoarding", "intent": "installation"},
    "security-fencing-for-schools": {"core": "security", "intent": "guide", "context": "school"},
    "palisade-fencing-for-warehouses": {"core": "palisade", "intent": "guide", "context": "warehouse"},
    "fencing-for-industrial-estates": {"core": "industrial", "intent": "guide", "context": "industrial-estate"},
    "anti-climb-fence-toppings-installation": {"core": "toppings", "intent": "installation"},
    "lps-1175-fencing": {"core": "lps", "intent": "guide"},
}

NAMES = {
    "lps-1175-fencing": "LPS 1175 Fencing",
    "868-twin-wire-mesh-fencing": "868 Twin-Wire Mesh Fencing",
    "868-twin-wire-mesh-fencing-near-me": "868 Twin-Wire Mesh Fencing Near Me",
    "868-twin-wire-mesh-fencing-installation": "868 Twin-Wire Mesh Fencing Installation",
    "868-twin-wire-mesh-fencing-repair": "868 Twin-Wire Mesh Fencing Repair",
}

SMALL = {"for", "and", "of", "the", "near"}


def display_name(slug: str) -> str:
    out = []
    parts = slug.split("-")
    i = 0
    while i < len(parts):
        w = parts[i]
        if w == "anti" and i + 1 < len(parts) and parts[i + 1] == "climb":
            out.append("Anti-Climb")
            i += 2
            continue
        if w == "near" and i + 1 < len(parts) and parts[i + 1] == "me":
            out.append("Near Me")
            i += 2
            continue
        out.append(w if w in SMALL and out else w.capitalize())
        i += 1
    return " ".join(out)


def spoken(name: str) -> str:
    return name[: -len(" Near Me")] + " near you" if name.endswith(" Near Me") else name


def classify(slug: str) -> dict:
    if slug in ONE_OFFS:
        return dict(ONE_OFFS[slug])
    for prefix, core in CORE_PREFIX:
        if slug == prefix:
            return {"core": core, "intent": "guide"}
        for suffix, intent in (("-near-me", "near-me"), ("-installation", "installation"), ("-repair", "repair")):
            if slug == prefix + suffix:
                return {"core": core, "intent": intent}
    raise SystemExit(f"unclassified fencing slug {slug}")


POOLS = {
    "fencing": ["fence-installation-crew", "palisade-fence-utility-site", "timber-closeboard-fence-new", "steel-railings-frontage", "mesh-security-fence-retail-site"],
    "fence": ["timber-closeboard-fence-new", "fence-in-need-of-repair", "fence-posts-being-set", "panel-fence-garden-boundary", "fence-installation-crew"],
    "security": ["mesh-security-fence-airport", "perimeter-fence-razor-wire", "security-fence-gate-site", "palisade-fence-telecoms-site", "boundary-fence-crash-gate"],
    "palisade": ["palisade-fence-pales-close-up", "palisade-fence-utility-site", "palisade-fence-telecoms-site", "perimeter-fence-razor-wire"],
    "steel-palisade": ["palisade-fence-utility-site", "palisade-fence-pales-close-up", "palisade-fence-telecoms-site", "security-fence-gate-site"],
    "mesh": ["mesh-security-fence-retail-site", "mesh-security-fence-airport", "boundary-fence-crash-gate", "perimeter-fence-gates"],
    "weldmesh": ["mesh-security-fence-retail-site", "perimeter-fence-gates", "boundary-fence-crash-gate", "mesh-security-fence-airport"],
    "mesh-358": ["mesh-security-fence-airport", "perimeter-fence-razor-wire", "boundary-fence-crash-gate", "mesh-security-fence-retail-site"],
    "mesh-868": ["school-gate-security-fence", "mesh-security-fence-retail-site", "perimeter-fence-gates", "school-railings-entrance"],
    "closeboard": ["timber-closeboard-fence-new", "fence-in-need-of-repair", "concrete-fence-post", "timber-post-and-rail-gate"],
    "panel-fencing": ["panel-fence-garden-boundary", "timber-closeboard-fence-new", "concrete-fence-post", "fence-in-need-of-repair"],
    "fence-panels": ["panel-fence-garden-boundary", "fence-in-need-of-repair", "concrete-fence-post", "timber-gate-and-fence"],
    "industrial": ["palisade-fence-utility-site", "security-fence-gate-site", "perimeter-fence-gates", "palisade-fence-telecoms-site"],
    "commercial": ["mesh-security-fence-retail-site", "steel-railings-frontage", "perimeter-fence-gates", "boundary-fence-crash-gate"],
    "perimeter": ["perimeter-fence-gates", "perimeter-fence-razor-wire", "mesh-security-fence-airport", "palisade-fence-utility-site"],
    "temporary": ["temporary-fencing-street", "temporary-fencing-panels-stacked", "construction-site-hoarding"],
    "hoarding": ["construction-site-hoarding", "temporary-fencing-street", "temporary-fencing-panels-stacked"],
    "anti-climb": ["mesh-security-fence-airport", "perimeter-fence-razor-wire", "boundary-fence-crash-gate", "security-fence-gate-site"],
    "toppings": ["perimeter-fence-razor-wire", "mesh-security-fence-airport", "palisade-fence-telecoms-site", "security-fence-gate-site"],
    "chain-link": ["chain-link-fence", "security-fence-gate-farm", "fence-posts-being-set", "perimeter-fence-gates"],
    "railings": ["steel-railings-frontage", "school-railings", "school-railings-entrance", "school-gate-security-fence"],
    "school": ["school-railings", "school-railings-entrance", "school-gate-security-fence", "mesh-security-fence-retail-site"],
    "fence-gates": ["timber-gate-and-fence", "security-fence-gate-site", "security-fence-gate-farm", "timber-post-and-rail-gate"],
    "pedestrian-gate": ["school-gate-security-fence", "timber-gate-and-fence", "steel-railings-frontage", "school-railings-entrance"],
    "palisade-gate": ["security-fence-gate-site", "palisade-fence-utility-site", "perimeter-fence-gates", "palisade-fence-pales-close-up"],
    "posts": ["fence-posts-being-set", "concrete-fence-post", "fence-installation-crew", "fence-in-need-of-repair"],
    "lps": ["mesh-security-fence-airport", "perimeter-fence-razor-wire", "boundary-fence-crash-gate", "security-fence-gate-site"],
}

INTENT_LEAD = {
    "installation": "fence-installation-crew",
    "repair": "fence-in-need-of-repair",
    "emergency": "fence-in-need-of-repair",
}


def _h(*parts: str) -> int:
    return int(hashlib.sha1("|".join(parts).encode()).hexdigest()[:8], 16)


def images_for(slug: str, info: dict) -> list[str]:
    pool = list(POOLS[info["core"]])
    k = _h(slug, "img") % len(pool)
    pool = pool[k:] + pool[:k]
    lead = INTENT_LEAD.get(info["intent"])
    if lead and info["core"] not in ("temporary", "hoarding"):
        pool = [lead] + [p for p in pool if p != lead]
    out: list[str] = []
    for p in pool:
        if p not in out:
            out.append(p)
        if len(out) == 3:
            break
    return [IMG + p + ".jpg" for p in out]


PACK = {
    "pack": "fencing",
    "label": "Fencing",
    "lock": "FENCING-DEEP-LOCK-2026-10-05.md",
    "p0_file": "FENCING-DEEP-P0-2026-10-05.txt",
    "p0_count": 108,
    "service": "landscaping",
    "trade_label": "Fencing",
    "parents": [
        ["/pages/services/landscaping", "Fencing, landscaping and external works"],
        ["/pages/keywords/fencing", "Fencing guide"],
    ],
    "town_who": TOWN_WHO,
    "town_angles": TOWN_ANGLES,
    "banned": BANNED,
    "cores": CORES,
    "intents": INTENTS,
    "contexts": CONTEXTS,
    "shared": SHARED,
    "lenses": LENSES,
    "classify": classify,
    "names": NAMES,
    "display_name": display_name,
    "spoken": spoken,
    "images_for": images_for,
    "angles": ANGLES,
    "default_context": "mixed",
    "redirects": {},
}
