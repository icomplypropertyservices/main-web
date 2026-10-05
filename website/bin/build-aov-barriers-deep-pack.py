#!/usr/bin/env python3
"""Build data/aov-barriers-deep-p0.json — AOV + Barriers DEEP P0 hubs (179).

SEO lock: icomply-ops/seo/AOV-BARRIERS-DEEP-LOCK-2026-10-05.md (corrected 23:55:
barrier theme = manual barriers + WIDTH / HEIGHT restrictions; wind theme withdrawn).
P0 list:  icomply-ops/seo/AOV-BARRIERS-DEEP-P0.txt (exactly 179 slugs).

Hubs are /pages/keywords/{slug}. Town pages are P0 × UK TOP 5000 (manufacturer heads:
Greater Manchester core 60 only), rendered on demand by PHP + the Netlify edge function.
POA only. No attendance-time promises. Honest dimensions: site-measured or the
manufacturer's published range — never invented ratings.

Usage: python3 bin/build-aov-barriers-deep-pack.py [--seo-dir=/workspace/icomply-ops/seo]
"""
from __future__ import annotations

import hashlib
import json
import os
import random
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.dont_write_bytecode = True  # keep bin/ clean (no __pycache__)
sys.path.insert(0, HERE)

from aov_barriers_deep.aov_cores import AOV_CORES  # noqa: E402
from aov_barriers_deep.barrier_cores import BARRIER_CORES  # noqa: E402
from aov_barriers_deep.intents import INTENTS  # noqa: E402
from aov_barriers_deep.lenses import LENSES  # noqa: E402
from aov_barriers_deep.p0map import GM_ONLY, classify, display_name  # noqa: E402
from aov_barriers_deep.sectors import CONTEXT, SECTORS, SHARED  # noqa: E402

ROOT = os.path.dirname(HERE)
OUT = os.path.join(ROOT, "data", "aov-barriers-deep-p0.json")
LOCK = "icomply-ops/seo/AOV-BARRIERS-DEEP-LOCK-2026-10-05.md"
NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"

IMG = "/assets/images"
DEEP = IMG + "/lanes/aov-barriers-deep"
BL = IMG + "/lanes/barriers"
IMAGE_POOLS = {
    "aov-panel": [DEEP + "/aov-smoke-vent-control-panel.jpg", DEEP + "/aov-smoke-vent-panels-plant-room.jpg", IMG + "/products/aov-ctrl.jpg", DEEP + "/aov-smoke-vent-trigger-switch.jpg"],
    "aov-actuator": [IMG + "/products/aov-act-hvy.jpg", DEEP + "/aov-smoke-vent-control-panel.jpg", IMG + "/services/aov-air-handling-photo.jpg", DEEP + "/aov-smoke-vent-trigger-switch.jpg"],
    "aov-carpark": [DEEP + "/aov-basement-smoke-outlet.jpg", DEEP + "/aov-smoke-vent-panels-plant-room.jpg", IMG + "/services/aov-air-handling-photo.jpg", DEEP + "/aov-smoke-vent-trigger-switch.jpg"],
    "aov-roof": [IMG + "/services/aov-air-handling-photo.jpg", IMG + "/keywords/aov-installation.jpg", DEEP + "/aov-smoke-vent-control-panel.jpg", DEEP + "/aov-smoke-vent-trigger-switch.jpg", IMG + "/products/aov-sensor.jpg"],
    "aov": [DEEP + "/aov-smoke-vent-trigger-switch.jpg", IMG + "/services/aov-air-handling-photo.jpg", DEEP + "/aov-smoke-vent-control-panel.jpg", IMG + "/products/aov-sensor.jpg", DEEP + "/aov-smoke-vent-panels-plant-room.jpg", IMG + "/keywords/aov-installation.jpg"],
    "manual": [BL + "/01.jpg", BL + "/05.jpg", BL + "/08.jpg", BL + "/24.jpg", BL + "/04.jpg"],
    "height": [DEEP + "/height-barrier-retail-car-park.jpg", DEEP + "/height-restriction-barrier-lane.jpg", DEEP + "/height-barrier-public-car-park.jpg", DEEP + "/height-restriction-barrier-entrance.jpg", DEEP + "/height-barriers-twin-entrance.jpg", DEEP + "/height-barrier-access-road.jpg", DEEP + "/height-barrier-car-park-entrance.jpg"],
    "clearance": [BL + "/12.jpg", BL + "/14.jpg", BL + "/21.jpg", BL + "/15.jpg", BL + "/18.jpg"],
    "width": [BL + "/06.jpg", BL + "/16.jpg", BL + "/10.jpg", BL + "/17.jpg", BL + "/23.jpg", DEEP + "/height-barriers-twin-entrance.jpg"],
    "rising": [BL + "/09.jpg", BL + "/13.jpg", BL + "/21.jpg", BL + "/19.jpg", BL + "/11.jpg", BL + "/07.jpg", BL + "/16.jpg"],
}
CORE_POOL = {
    "aov-control-panel": "aov-panel", "aov-actuator": "aov-actuator", "louvre-aov": "aov-roof", "nshevs": "aov-roof",
    "smoke-vent": "aov-roof", "colt-aov": "aov-roof", "manual": "manual", "manual-width": "width",
    "manual-swing-arm": "manual", "heavy-duty": "manual", "manual-height": "height", "height-restriction": "height",
    "hgv-height": "height", "height-and-rising-arm": "height", "height-clearance": "clearance",
    "articulated-arm": "clearance", "undercroft": "clearance", "width-restriction": "width", "opening-width": "width",
    "arm-length": "rising", "survey-spec": "width", "rising-arm": "rising", "car-park": "rising", "automatic": "rising",
    "boom": "rising", "anpr": "rising", "general": "rising", "came": "rising",
}

# Short definition used in intros + metas (per core).
HOOK = {
    "aov": "vents that open on a signal to clear smoke from stairs and corridors",
    "aov-system": "the detector, panel, actuator and vent chain that has to work as one",
    "aov-control-panel": "the panel that reads detectors, drives actuators and holds the standby battery",
    "aov-actuator": "the motor that opens and closes the vent to its published force and stroke",
    "louvre-aov": "blade louvres that open on a signal at shaft heads, plant rooms and facades",
    "window-aov": "opening windows with actuators that must reach the required free area",
    "smoke-control": "the measures that keep escape routes clearer of smoke for longer",
    "smoke-curtain": "fabric barriers that drop on a signal to hold back smoke",
    "smoke-shaft": "vertical shafts that clear smoke from the fire floor only",
    "smoke-vent": "openings that let smoke out of stairs, corridors and roofs",
    "smoke-ventilation": "natural and mechanical systems that clear smoke from escape routes",
    "nshevs": "natural smoke and heat exhaust ventilation with tested inlets and outlets",
    "shevs": "natural and powered smoke and heat exhaust systems tested in fire mode",
    "mechanical-smoke-ventilation": "fans, dampers and vents that must run in the right sequence",
    "natural-smoke-ventilation": "high-level outlets and low-level inlets that rely on rising smoke",
    "corridor-smoke-vent": "corridor vents that must open on the fire floor only",
    "fire-rated-aov": "vents that open for smoke and seal as part of fire-resisting construction",
    "en-12101": "products fitted to their tested EN 12101 configuration",
    "colt-aov": "Colt vents and controls worked on to the maker's published data",
    "se-controls-aov": "SE Controls actuators and panels worked on to the maker's published data",
    "manual": "counterbalanced barriers opened by a keyholder and locked by padlock or key",
    "manual-height": "keyholder-opened height bars set to a measured clearance",
    "manual-width": "keyholder-opened width restrictions on private land",
    "manual-swing-arm": "barriers that swing sideways where overhead clearance is low",
    "heavy-duty": "stronger posts and reinforced arms with matched counterbalance",
    "arm-length": "arm length set from the measured clear opening and published ranges",
    "height-restriction": "bars that stop vehicles above a measured, signed height",
    "height-clearance": "the headroom a rising arm needs as well as the vehicle height limit",
    "articulated-arm": "folding arms for entrances with low headroom",
    "undercroft": "barriers for low-ceiling undercroft entrances",
    "height-and-rising-arm": "a height bar paired with a rising arm at one entrance",
    "hgv-height": "height bars that keep lorries out and let cars and vans in",
    "width-restriction": "restrictors that keep wider vehicles out of private sites",
    "opening-width": "clear width, lane layout and twin arms for wide openings",
    "survey-spec": "measured clearances turned into a written specification",
    "rising-arm": "automatic arms chosen by measured opening, headroom and duty",
    "car-park": "barriers matched to car park traffic, height limits and layout",
    "automatic": "powered barriers with safety from a written risk assessment",
    "boom": "boom arms sized from the measured opening and published ranges",
    "anpr": "number-plate access with cameras placed from measured approaches",
    "general": "manual, automatic, height and width barriers sized from measurements",
    "came": "CAME barriers worked on to the maker's published data",
}

AUDIENCE = {
    None: {"aov": "landlords, agents and building managers", "barrier": "car parks, estates and private sites"},
    "apartment-block": "managing agents of apartment blocks", "flats": "landlords and agents of blocks of flats",
    "car-park": "car park owners and operators", "care-home": "care home operators", "commercial": "commercial landlords and FMs",
    "factory": "factory and warehouse managers", "high-rise": "high-rise building owners", "hmo": "HMO landlords and agents",
}

BANNED = re.compile(r"\b(wind|winds|windy|wind-rated|wind-rating|beaufort|storm|storms|coastal|hurricane|gust|gusts|exposed)\b|km/h|£\s*\d", re.I)
TIMING = re.compile(r"(within \d+ ?(minutes|mins|hours|hrs)|\d+[- ]hour response|same[- ]day|same[- ]week|next[- ]day|24/7|fast response|rapid response|guaranteed (attendance|response|arrival)|we will be there|on site in \d)", re.I)


def h(*parts: str) -> int:
    return int(hashlib.sha1("|".join(parts).encode()).hexdigest()[:8], 16)


def rotate(items: list, seed: int) -> list:
    if not items:
        return []
    k = seed % len(items)
    return items[k:] + items[:k]


def words(text: str) -> int:
    return len(re.findall(r"[A-Za-z0-9'’-]+", text))


def take(paras: list[str], seed: int, target: int, minimum: int = 2) -> list[str]:
    """Seeded sample (not a contiguous run) so sibling pages share fewer paragraphs."""
    order = list(paras)
    random.Random(seed).shuffle(order)
    out: list[str] = []
    for p in order:
        if len(out) >= minimum and sum(words(x) for x in out) >= target:
            break
        out.append(p)
    return sorted(out, key=paras.index)


def spread_combos(n: int, k: int) -> list[tuple[int, ...]]:
    """All k-of-n index sets, ordered so consecutive picks overlap as little as possible."""
    from itertools import combinations
    pool = list(combinations(range(n), k))
    out = [pool.pop(0)]
    while pool:
        best = min(pool, key=lambda c: (max(len(set(c) & set(o)) for o in out[-6:]), pool.index(c)))
        pool.remove(best)
        out.append(best)
    return out


_COMBO_CACHE: dict[tuple[int, int], list[tuple[int, ...]]] = {}
MODULE_USERS: dict[str, list[str]] = {}


def module_index(module: str, slug: str) -> int:
    users = MODULE_USERS.setdefault(module, [])
    if slug not in users:
        users.append(slug)
    return users.index(slug)


def take_spread(paras: list[str], module: str, slug: str, target: int, k: int = 3) -> list[str]:
    k = min(k, len(paras))
    key = (len(paras), k)
    if key not in _COMBO_CACHE:
        _COMBO_CACHE[key] = spread_combos(len(paras), k)
    combos = _COMBO_CACHE[key]
    combo = combos[module_index(module, slug) % len(combos)]
    out = [paras[i] for i in combo]
    for i in random.Random(h(slug, module)).sample(range(len(paras)), len(paras)):
        if sum(words(x) for x in out) >= target:
            break
        if paras[i] not in out:
            out.append(paras[i])
    return sorted(out, key=paras.index)


def take_core(paras: list[str], sibling_index: int, target: int) -> list[str]:
    """Core paragraphs: siblings on the same core get maximally different subsets."""
    k = 3
    combo = spread_combos(len(paras), k)[sibling_index % len(spread_combos(len(paras), k))]
    out = [paras[i] for i in combo]
    for i in range(len(paras)):
        if sum(words(x) for x in out) >= target:
            break
        if paras[i] not in out:
            out.append(paras[i])
    return sorted(out, key=paras.index)


# Secondary angle per page: a different intent module, picked by slug hash.
ANGLES = {
    "aov": ["installation", "maintenance", "repair", "commissioning", "service"],
    "barrier": ["installation", "maintenance", "repair", "survey", "replacement", "service"],
}
# Related cores a page may borrow one or two paragraphs from (same DEEP cluster).
CROSS = {
    "manual": ["heavy-duty", "manual-swing-arm", "manual-height", "arm-length"],
    "heavy-duty": ["manual", "arm-length"],
    "manual-height": ["height-restriction", "manual", "height-and-rising-arm"],
    "manual-width": ["width-restriction", "manual", "opening-width"],
    "manual-swing-arm": ["manual", "height-clearance", "opening-width"],
    "arm-length": ["opening-width", "height-clearance", "heavy-duty"],
    "height-restriction": ["manual-height", "hgv-height", "height-and-rising-arm", "height-clearance", "undercroft"],
    "height-clearance": ["articulated-arm", "undercroft", "arm-length", "manual-swing-arm"],
    "articulated-arm": ["height-clearance", "undercroft", "arm-length"],
    "undercroft": ["articulated-arm", "height-clearance", "height-restriction"],
    "height-and-rising-arm": ["height-restriction", "rising-arm", "height-clearance"],
    "hgv-height": ["height-restriction", "opening-width"],
    "width-restriction": ["manual-width", "opening-width"],
    "opening-width": ["arm-length", "width-restriction", "height-clearance"],
    "survey-spec": ["opening-width", "height-clearance", "arm-length", "height-restriction"],
    "aov": ["aov-system", "smoke-vent", "window-aov", "aov-control-panel"],
    "aov-system": ["aov-control-panel", "aov-actuator", "aov"],
    "aov-control-panel": ["aov-system", "aov-actuator"],
    "aov-actuator": ["window-aov", "aov-control-panel", "louvre-aov"],
    "louvre-aov": ["smoke-shaft", "aov-actuator"],
    "window-aov": ["aov-actuator", "aov"],
    "smoke-control": ["smoke-ventilation", "shevs", "aov-system"],
    "smoke-curtain": ["smoke-control", "shevs"],
    "smoke-shaft": ["corridor-smoke-vent", "louvre-aov", "mechanical-smoke-ventilation"],
    "smoke-vent": ["aov", "window-aov", "natural-smoke-ventilation"],
    "smoke-ventilation": ["natural-smoke-ventilation", "mechanical-smoke-ventilation", "smoke-control"],
    "nshevs": ["natural-smoke-ventilation", "shevs", "smoke-curtain"],
    "shevs": ["nshevs", "mechanical-smoke-ventilation", "smoke-control"],
    "mechanical-smoke-ventilation": ["shevs", "smoke-shaft"],
    "natural-smoke-ventilation": ["nshevs", "smoke-vent"],
    "corridor-smoke-vent": ["smoke-shaft", "aov"],
    "fire-rated-aov": ["aov", "en-12101"],
    "en-12101": ["shevs", "smoke-control"],
    "colt-aov": ["louvre-aov", "nshevs"],
    "se-controls-aov": ["aov-control-panel", "aov-actuator"],
}


def cap(text: str) -> str:
    text = text.strip()
    return text[:1].upper() + text[1:] if text else text


def fill(text: str, thing: str) -> str:
    return cap(text.replace("{thing}", thing))


def fit_meta(text: str) -> str:
    text = re.sub(r"\s+", " ", text).strip()
    if len(text.encode()) > 160 and "—" in text:
        text = text.replace("—", "-", 1)
    if len(text.encode()) > 160:
        cut = text.encode()[:160].decode("utf-8", "ignore")
        sp = cut.rfind(" ")
        if sp > 40:
            cut = cut[:sp]
        text = cut.rstrip(" .,;:—-")
        extra = " Quote POA."
        if not text.endswith("POA") and len((text + extra).encode()) <= 160:
            text += extra
    for pad in (" Scope is written first.", " No catalogue price.", " Call 07517806082.", " Scope first.", " POA only."):
        if len(text.encode()) >= 140:
            break
        if len((text + pad).encode()) <= 160:
            text += pad
    return text


def title_for(name: str) -> str:
    full = f"{name} | iComply Property Services"
    return full if len(full) <= 60 else f"{name} — iComply"


def intro_for(slug: str, name: str, info: dict) -> str:
    hook = HOOK[info["core"]]
    intent = info["intent"]
    intent_key = intent if isinstance(intent, str) else intent[0]
    sector = info["sector"]
    line = info["line"]
    where = SECTORS[sector]["label"] if sector else ("buildings across the UK mainland" if line == "aov" else "entrances across the UK mainland")
    variants = [
        f"{name} from iComply Property Services covers {hook}. This guide is written for {where}, starting from a site survey and a written scope rather than a catalogue price.",
        f"{name}: {hook}. iComply works on {where} from its Stockport workshop, measures before it specifies, and quotes every job price on application.",
        f"This {name} guide explains {hook}, what decides the right approach for {where}, and how iComply scopes the work before anything is ordered.",
    ]
    lead = variants[h(slug, "intro") % len(variants)]
    tail = {
        "installation": "Installation is quoted after measurement and product selection from published data.",
        "installer": "It sets out what a good installer measures, names and hands over.",
        "installers": "It shows how to compare installers on scope, not headline price.",
        "near-me": "Sites across the UK mainland are quoted from Stockport, with travel in the written quote.",
        "repair": "Repairs start by tracing the cause on site before any part is ordered.",
        "maintenance": "Maintenance follows the maker's guidance and the building's own routine.",
        "service": "Each service leaves a written record the next visit can build on.",
        "servicing": "Servicing records are kept comparable from one visit to the next.",
        "commissioning": "Commissioning proves every function and interface against a written description.",
        "engineer": "The engineer arrives with the site facts already gathered.",
        "engineers": "Records stay consistent whichever engineer attends.",
        "emergency-repair": "Emergency calls are treated as priority instructions; no attendance time is promised.",
        "emergency-installer": "Urgent instructions are prioritised; no attendance time is promised.",
        "replacement": "Replacements are matched from published data or a new site measurement.",
        "shortening": "The new length comes from a fresh measurement of the clear opening.",
        "survey": "The survey report belongs to the client and can be used with any installer.",
        "guide": "Dimensions are site-measured or taken from the manufacturer's published range.",
    }[intent_key]
    return f"{lead} {tail}"


def meta_for(slug: str, name: str, info: dict) -> str:
    aud = AUDIENCE[None][info["line"]] if info["sector"] is None else AUDIENCE[info["sector"]]
    hook = HOOK[info["core"]]
    text = f"{name} for {aud} across the UK mainland: {hook}. Request a quote — POA."
    if len(text.encode()) > 160:
        text = f"{name} for {aud}: {hook}. Request a quote — POA."
    if len(text.encode()) > 160:
        text = f"{name}: {hook}. Request a quote — POA."
    return fit_meta(text)


def build_page(slug: str, info: dict, related: str, sibling_index: int = 0) -> dict:
    name = display_name(slug)
    line = info["line"]
    core = (AOV_CORES if line == "aov" else BARRIER_CORES)[info["core"]]
    thing = info["thing"]
    intents = info["intent"] if isinstance(info["intent"], tuple) else (info["intent"],)
    seed = h(slug)

    body: list[str] = []
    if slug in LENSES:
        body.append(LENSES[slug])
    body += [cap(p) for p in take_core(core["paras"], sibling_index, 230)]
    for i, intent in enumerate(intents):
        mod = INTENTS[intent]
        body += [fill(p, thing) for p in take_spread(mod["paras"], f"{line}:intent:{intent}", slug, 200 if i == 0 else 110, 3 if i == 0 else 2)]
    angles = [a for a in ANGLES[line] if a not in intents]
    angle = angles[h(slug, "angle") % len(angles)]
    body += [fill(p, thing) for p in take_spread(INTENTS[angle]["paras"], f"{line}:angle:{angle}", slug, 110, 2)]
    pool = (AOV_CORES if line == "aov" else BARRIER_CORES)
    cross_cores = CROSS.get(info["core"], [])
    for other in random.Random(h(slug, "cross")).sample(cross_cores, min(2, len(cross_cores))):
        paras = pool[other]["paras"]
        body.append(cap(paras[h(slug, "cross", other) % len(paras)]))
    if line == "barrier" and info["core"] in {"general", "rising-arm", "car-park", "automatic", "boom", "anpr", "came"}:
        # DEEP theme: width / height depth on the popular barrier hubs.
        for cross in ("arm-length", "height-clearance", "opening-width", "height-restriction"):
            paras = BARRIER_CORES[cross]["paras"]
            body.append(paras[h(slug, cross) % len(paras)])
    if info["sector"]:
        body += [fill(p, thing) for p in take_spread(SECTORS[info["sector"]]["paras"], "sector:" + info["sector"], slug, 180, 3)]
    else:
        body += take_spread(CONTEXT[line], "context:" + line, slug, 150, 3)
    body += take_spread(SHARED[line], "shared:" + line, slug, 150, 3)

    # de-duplicate while keeping order
    seen: set[str] = set()
    body = [p for p in body if not (p in seen or seen.add(p))]

    faqs = list(core["faqs"])
    for intent in intents:
        faqs += [(fill(q, thing), fill(a, thing)) for q, a in INTENTS[intent]["faqs"]]
    if info["sector"]:
        faqs += [(fill(q, thing), fill(a, thing)) for q, a in SECTORS[info["sector"]]["faqs"]]
    faqs = rotate(faqs, seed)[:6]
    faqs.append((
        f"How do I ask for {name}?",
        f"Phone 07517806082, use WhatsApp or the contact form. Send the address, photos and what you need. The reply is price on application. Workshop: {NAP}.",
    ))

    focus = list(core["focus"])
    page = {
        "name": name,
        "h1": name,
        "service": "aov-air-handling" if line == "aov" else "barriers",
        "related": related,
        "seo_title": title_for(name),
        "meta_desc": meta_for(slug, name, info),
        "intro": intro_for(slug, name, info),
        "body": "\n\n".join(body),
        "focus_points": focus,
        "faq": [[q, a] for q, a in faqs],
        "seo_keywords": ", ".join(dict.fromkeys([name.lower(), thing.lower(), (core["label"]).lower(), "price on application"])),
    }
    blob = " ".join([page["intro"], page["body"], page["meta_desc"], " ".join(focus), " ".join(q + " " + a for q, a in faqs)])
    if BANNED.search(blob):
        raise SystemExit(f"banned wording in {slug}: {BANNED.search(blob).group(0)}")
    if TIMING.search(blob):
        raise SystemExit(f"timing promise in {slug}: {TIMING.search(blob).group(0)}")
    return page


def images_for(slug: str, info: dict) -> list[str]:
    if info["line"] == "aov":
        key = CORE_POOL.get(info["core"], "aov")
        if info["sector"] == "car-park":
            key = "aov-carpark"
    else:
        key = CORE_POOL[info["core"]]
    pool = rotate(IMAGE_POOLS[key], h(slug, "img"))
    out = []
    for img in pool:
        if img not in out:
            out.append(img)
        if len(out) == 3:
            break
    return out


def main() -> None:
    seo_dir = "/workspace/icomply-ops/seo"
    for arg in sys.argv[1:]:
        if arg.startswith("--seo-dir="):
            seo_dir = arg.split("=", 1)[1]
    p0 = [l.strip() for l in open(os.path.join(seo_dir, "AOV-BARRIERS-DEEP-P0.txt"), encoding="utf-8") if l.strip()]
    if len(p0) != 179 or len(set(p0)) != 179:
        raise SystemExit(f"P0 must be 179 unique slugs, got {len(p0)}")
    infos = {s: classify(s) for s in p0}
    # related = a sibling on the same core (different slug), else the first P0 of the line
    by_core: dict[str, list[str]] = {}
    for s, i in infos.items():
        by_core.setdefault(i["line"] + ":" + i["core"], []).append(s)
    hubs, keywords = [], {}
    for s in p0:
        info = infos[s]
        sibs = [x for x in by_core[info["line"] + ":" + info["core"]] if x != s]
        if not sibs:
            sibs = [x for x in p0 if infos[x]["line"] == info["line"] and x != s]
        related = sibs[h(s, "rel") % len(sibs)]
        siblings = by_core[info["line"] + ":" + info["core"]]
        keywords[s] = build_page(s, info, related, siblings.index(s))
        hubs.append({
            "slug": s,
            "line": info["line"],
            "service": keywords[s]["service"],
            "name": keywords[s]["name"],
            "images": images_for(s, info),
            "geo": "gm60" if s in GM_ONLY else "top5000",
        })
    for hub in hubs:
        for img in hub["images"]:
            if not os.path.isfile(ROOT + img):
                raise SystemExit(f"missing image {img}")
    doc = {
        "lock": LOCK,
        "wave": "P0",
        "theme": "AOV + barriers DEEP — barrier restriction theme is manual + width/height (wind theme withdrawn)",
        "towns": "uk-top5000-towns",
        "geo_rules": {
            "top5000": "keyword hub × UK TOP 5000 towns (+ GM core places kept on the GM path)",
            "gm60": "manufacturer × job heads: Greater Manchester core 60 only",
        },
        "follow_on": "P1 wave (AOV-BARRIERS-DEEP-P1-WAVE1.txt, 200) and the 3766 manual + width/height supplement are parked — not in this PR.",
        "hubs": hubs,
        "keywords": keywords,
    }
    with open(OUT, "w", encoding="utf-8") as fh:
        json.dump(doc, fh, ensure_ascii=False, indent=1)
        fh.write("\n")
    wc = [words(k["intro"] + " " + k["body"]) for k in keywords.values()]
    print(f"wrote {os.path.relpath(OUT, ROOT)}: hubs={len(hubs)} aov={sum(1 for x in hubs if x['line']=='aov')} barrier={sum(1 for x in hubs if x['line']=='barrier')} gm60={sum(1 for x in hubs if x['geo']=='gm60')} intro+body words min={min(wc)} max={max(wc)} avg={sum(wc)//len(wc)}")


if __name__ == "__main__":
    main()
