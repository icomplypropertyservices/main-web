#!/usr/bin/env python3
"""Build website/data/network-pack.json for the networking, data cabling and
mesh Wi-Fi pack (lock: icomply-ops/seo/NETWORK-DATA-MESH-LOCK-2026-10-20.md).

Inputs: bin/network_pack/{topics,angles}.py, data/network-pack-lists.json
(the lock's keyword / job / town lists), data/dual-ring-allowlist.json.
Output: data/network-pack.json, read by netlify/lib/network-pack.js (town
pages) and includes/network-pack.php (hubs, pre-rendered here).
usage: python3 website/bin/build-network-pack.py
"""
import hashlib, html, json, pathlib, sys

HERE = pathlib.Path(__file__).resolve().parent
SITE = HERE.parent
sys.path.insert(0, str(HERE))
from network_pack.topics import TOPICS, SERVICE_TOPICS, TOPIC_FAQS  # noqa: E402
from network_pack.angles import ANGLES  # noqa: E402

lists = json.loads((SITE / "data/network-pack-lists.json").read_text())
allow = json.loads((SITE / "data/dual-ring-allowlist.json").read_text())
NAMES = {t["slug"]: t["name"] for t in allow["towns"]}
BASE = "https://icomplypropertyservices.co.uk"

SERVICES = {
    "networking": {
        "name": "Networking", "label": "Networking",
        "h1": "Networking for homes, offices, HMOs and blocks",
        "title": "Networking: Office, HMO & Home Networks | iComply",
        "meta": "Network installation, switches, comms cabinets and fault finding in Stockport, Greater Manchester and the North West. Same/Next-Day visits, price on application. 07517 806082.",
        "intro": [
            "We design, install and fix wired and wireless networks for landlords, managing agents, offices, shops and homeowners, working from our base at 17 Woodlands Park Road, Offerton, Stockport SK2 5DE.",
            "A network is more than a router. It is the position of the hub, the switch that links everything, the cable to each desk, TV, camera and access point, and the Wi-Fi that fills the gaps. We plan those together, so the building ends up with one tidy, documented system instead of a pile of boosters and loose leads.",
            "Most of our networking work falls into four kinds of job: new installs in offices, shops and refurbished houses; HMO and block networks where tenant Wi-Fi has to be kept apart from CCTV and door entry; fault finding on networks that are slow or keep dropping; and reconnecting cameras, door stations and other building systems after a broadband provider changes the router.",
        ],
        "image": ["networking", "networking-photo", "structured-cabling"],
    },
    "structured-cabling": {
        "name": "Data cabling", "label": "Data Cabling",
        "h1": "Data cabling: Cat5e, Cat6, Cat6a, fibre, data points and PoE",
        "title": "Data Cabling: Cat5e, Cat6, Fibre & PoE | iComply",
        "meta": "Structured data cabling, data points, patch panels, PoE and fibre links across Greater Manchester and the North West. Tested and labelled. Same/Next-Day, price on application.",
        "intro": [
            "We install structured data cabling for homes, offices, HMOs and blocks: Cat5e, Cat6 and Cat6a copper, fibre between buildings and floors, data points, patch panels, comms cabinets and Power over Ethernet runs for cameras, access points and door stations.",
            "Structured cabling means every outlet has its own cable back to a patch panel, every run is tested and every port is labelled at both ends. That is what makes later changes quick: a desk moves, a camera is added or a tenant needs a wired point, and the change is a patch lead in the cabinet rather than a new cable through the walls.",
            "We cover single extra points, whole-house cabling during a refurbishment, office fit-outs and the cabling behind CCTV, door entry and intercom systems. Every job is surveyed and quoted in writing first.",
        ],
        "image": ["structured-cabling", "structured-cabling-photo", "networking-photo"],
    },
    "wifi": {
        "name": "Wi-Fi and mesh Wi-Fi", "label": "Wi-Fi & Mesh Wi-Fi",
        "h1": "Wi-Fi and mesh Wi-Fi: dead spots, boosters, extenders and access points",
        "title": "Wi-Fi & Mesh Wi-Fi Installation, Dead Spots | iComply",
        "meta": "Mesh Wi-Fi, access points and dead-spot fixes, plus CCTV and door entry that works with your Sky or BT hub. Greater Manchester and the North West. Same/Next-Day, price on application.",
        "intro": [
            "We fix Wi-Fi that does not reach upstairs, the back bedroom, the loft conversion or the garden office, and we install Wi-Fi for HMOs, offices and shops where every room needs a usable signal.",
            "We measure before we recommend anything. A room-by-room check of signal and speed shows whether the answer is moving the hub, adding a mesh node with wired backhaul, fitting ceiling access points on PoE, or simply replacing a booster that is halving your speed.",
            "We also connect the building systems that depend on Wi-Fi: CCTV recorders, Wi-Fi cameras, video door entry and doorbells. They work with your Sky or BT hub, and we set them up so a router swap does not leave the app saying offline.",
        ],
        "image": ["wifi", "wifi-photo", "networking"],
    },
}

SERVICE_FAQS = {
    "networking": [
        ("Do you work on networks you did not install?", "Yes. We test and document what is there first, then fix, extend or replace only what needs it."),
        ("Can you separate tenant Wi-Fi from building systems?", "Yes. In HMOs and blocks we put CCTV, door entry and other building kit on their own network or VLAN and give tenants a separate network."),
    ],
    "structured-cabling": [
        ("Do you label and test every run?", "Yes. Each run is tested end to end after termination and labelled at the outlet and the patch panel, with results recorded at handover."),
        ("Can you cable an occupied house or office?", "Yes. We plan routes through lofts, floor voids and stud walls and use neat trunking where walls are solid, working room by room."),
    ],
    "wifi": [
        ("Do you sell a particular Wi-Fi brand?", "No. We recommend kit that suits the building and works with your Sky or BT hub or other provider's router."),
        ("Can you fix Wi-Fi in one room only?", "Yes. Many jobs are a single dead spot fixed with a wired point and one node or access point."),
    ],
}


def seed_int(*parts):
    return int(hashlib.sha1("|".join(parts).encode()).hexdigest()[:8], 16)


def fill(text, town, subject):
    return text.replace("{town}", town).replace("{subject}", subject)


def pick(slots, key, town, subject):
    return " ".join(fill(alts[seed_int(key, str(i)) % len(alts)], town, subject) for i, alts in enumerate(slots))


def esc(s):
    return html.escape(s, quote=True)


def topic_html(tid, key, town, subject, full=True):
    t = TOPICS[tid]
    h2 = fill(t["h2"][seed_int(key, tid, "h2") % len(t["h2"])], town, subject)
    paras = t["paras"] if full else t["paras"][:1]
    body = "".join(f"<p>{esc(pick(p, key + tid + str(j), town, subject))}</p>" for j, p in enumerate(paras))
    return f'<section class="np-topic" id="np-{tid}"><h2>{esc(h2)}</h2>{body}</section>'


def faq_html(faqs):
    return ('<section id="faq" class="faq"><h2>Frequently asked questions</h2>'
            + "".join(f'<details class="faq-item"><summary>{esc(q)}</summary><p>{esc(a)}</p></details>' for q, a in faqs)
            + "</section>")


def links_html(title, items):
    return (f'<section class="np-links"><h2>{esc(title)}</h2><ul class="np-link-list">'
            + "".join(f'<li><a href="{esc(h)}">{esc(t)}</a></li>' for h, t in items) + "</ul></section>")


def img_html(bases, alt):
    imgs = "".join(
        f'<img src="/assets/images/services/{b}.jpg" alt="{esc(alt)} photo {i + 1}" width="640" height="420" loading="{"eager" if i == 0 else "lazy"}">'
        for i, b in enumerate(bases))
    return (f'<figure class="quality-bar-images np-images">{imgs}'
            '<figcaption>Photos: Wikimedia Commons, used under their CC licences.</figcaption></figure>')


def schema(name, service_type, desc, url, crumbs, faqs):
    return {
        "@context": "https://schema.org",
        "@graph": [
            {"@type": "BreadcrumbList", "itemListElement": [
                {"@type": "ListItem", "position": i + 1, "name": n, "item": BASE + h} for i, (h, n) in enumerate(crumbs)]},
            {"@type": "Service", "name": name, "serviceType": service_type, "description": desc, "url": url,
             "areaServed": "Greater Manchester and the North West",
             "provider": {"@type": "LocalBusiness", "@id": BASE + "/#business", "name": "iComply Property Services",
                          "telephone": "+447517806082", "email": "icomplypropertyservices@gmail.com",
                          "address": {"@type": "PostalAddress", "streetAddress": "17 Woodlands Park Road, Offerton",
                                      "addressLocality": "Stockport", "postalCode": "SK2 5DE", "addressCountry": "GB"}}},
            {"@type": "FAQPage", "mainEntity": [
                {"@type": "Question", "name": q, "acceptedAnswer": {"@type": "Answer", "text": a}} for q, a in faqs]},
        ],
    }


def town_list(prefix, towns):
    return [(f"{prefix}/{t}", NAMES.get(t, t)) for t in towns]


KW = {r[0]: {"service": r[1], "priority": r[2], "status": r[3]} for r in lists["keywords"]}
NEW_KW = [s for s, r in KW.items() if not r["status"].startswith("LIVE")]
LIVE_KW = [s for s, r in KW.items() if r["status"].startswith("LIVE")]
JOBS = {r[0]: {"priority": r[1]} for r in lists["jobs"]}
GM60, DUAL = lists["gm60"], lists["dual269"]

missing = [s for s in NEW_KW + list(JOBS) if s not in ANGLES]
if missing:
    sys.exit(f"angles missing for {missing}")


def hub_common_links(service):
    out = [(f"/pages/services/{s}", SERVICES[s]["name"]) for s in SERVICES]
    out += [("/pages/services/cctv", "CCTV"), ("/pages/services/door-entry", "Door entry"),
            ("/pages/services/intercoms", "Intercoms"), ("/pages/services/access-control", "Access control")]
    out += [(f"/pages/keywords/{s}", ANGLES[s]["name"]) for s in NEW_KW if KW[s]["service"] == service]
    out += [(f"/pages/keywords/{s}", s.replace("-", " ").capitalize()) for s in LIVE_KW]
    return out


def build_hub(kind, slug):
    if kind == "service":
        sv = SERVICES[slug]
        name, service, key = sv["name"], slug, f"hub|service|{slug}"
        h1, title, meta = sv["h1"], sv["title"], sv["meta"]
        lead = "".join(f"<p>{esc(p)}</p>" for p in sv["intro"])
        topics = SERVICE_TOPICS[slug]
        full = set(topics)
        faqs = SERVICE_FAQS[slug] + [f for t in topics for f in TOPIC_FAQS.get(t, [])[:1]]
        url = f"/pages/services/{slug}"
        crumbs = [("/", "Home"), ("/pages/services", "Services"), (url, name)]
        images = sv["image"]
        towns = town_list(f"/pages/{slug}", DUAL)
        town_title = f"{name} by town ({len(DUAL)} towns)"
        extra = [(f"/pages/jobs/{j}", ANGLES[j]["name"] + " (job)") for j in JOBS if ANGLES[j]["service"] == slug]
    else:
        a = ANGLES[slug]
        service = a["service"]
        name, key = a["name"], f"hub|{kind}|{slug}"
        sv = SERVICES[service]
        if kind == "keyword":
            url = f"/pages/keywords/{slug}"
            h1 = f"{name} in Stockport, Greater Manchester and the North West"
            title = f"{name} | Greater Manchester | iComply"
            crumbs = [("/", "Home"), ("/pages/keywords", "Service guides"), (url, name)]
            towns = town_list(url, GM60)
        else:
            url = f"/pages/jobs/{slug}"
            h1 = f"{name}: the job, step by step"
            title = f"{name} Job | Greater Manchester | iComply"
            crumbs = [("/", "Home"), ("/pages/jobs", "Jobs"), (url, name)]
            towns = town_list(url, GM60)
        meta = (f"{name} from our Stockport base across Greater Manchester. "
                + ("Works with your Sky or BT hub. " if service == "wifi" else "")
                + "Same/Next-Day visits, price on application. Call 07517 806082.")
        lead = "".join(f"<p>{esc(pick(s and [s] or [], key + 'lead' + str(i), 'Greater Manchester', name))}</p>" for i, s in enumerate(a["lead"]))
        lead += f"<p>{esc('We work from 17 Woodlands Park Road, Offerton, Stockport SK2 5DE and cover the 60 Greater Manchester towns listed below for this work, with same-day and next-day visits available. Price is on application once the scope is clear.')}</p>"
        if kind == "job":
            lead += f'<h2>{esc("What happens on the day")}</h2><p>{esc(fill(" ".join(a["steps"]), "the property", name))} We explain the findings before any extra work, and nothing beyond the agreed scope is done without your say-so.</p>'
        topics = list(dict.fromkeys(a["topics"] + SERVICE_TOPICS[service]))
        full = set(topics)
        faqs = [(fill(q, "Greater Manchester", name), fill(x, "Greater Manchester", name)) for q, x in a["faqs"]]
        faqs += [f for t in topics for f in TOPIC_FAQS.get(t, [])[:1]][:5]
        images = sv["image"]
        town_title = f"{name} by town"
        extra = [(f"/pages/services/{service}", sv["name"] + " hub")]
        if kind == "keyword" and slug in JOBS:
            extra.append((f"/pages/jobs/{slug}", name + " job"))
        if kind == "job":
            extra.append((f"/pages/keywords/{slug}", name + " guide"))
    body = [lead]
    for t in topics:
        body.append(topic_html(t, key, "Greater Manchester", name, full=t in full))
    body.append(links_html(town_title, towns))
    body.append(links_html("Related services and guides", extra + hub_common_links(service)))
    body.append(faq_html(faqs))
    fragment = "".join(body)
    return {
        "url": url, "title": title, "meta": meta, "h1": h1, "name": name, "service": service,
        "crumbs": crumbs, "images": img_html(images, name),
        "og": f"/assets/images/services/{images[0]}.jpg",
        "html": fragment,
        "schema": schema(name, sv["label"], meta, BASE + url, crumbs, faqs),
    }


def main():
    hubs = {}
    for s in SERVICES:
        hubs[f"service/{s}"] = build_hub("service", s)
    for s in NEW_KW:
        hubs[f"keyword/{s}"] = build_hub("keyword", s)
    for s in JOBS:
        hubs[f"job/{s}"] = build_hub("job", s)
    towns = {t: {"name": NAMES.get(t, t.replace("-", " ").title())} for t in DUAL}
    out = {
        "_note": "Generated by website/bin/build-network-pack.py. Edit the bin/network_pack/*.py sources, then rebuild.",
        "services": {k: {kk: v[kk] for kk in ("name", "label", "image")} for k, v in SERVICES.items()},
        "keywords": {s: {**ANGLES[s], "priority": KW[s]["priority"]} for s in NEW_KW},
        "jobs": {s: {**ANGLES[s], "priority": JOBS[s]["priority"]} for s in JOBS},
        "live_cabling": LIVE_KW,
        "topics": TOPICS, "service_topics": SERVICE_TOPICS,
        "topic_faqs": {k: [list(x) for x in v] for k, v in TOPIC_FAQS.items()},
        "service_faqs": {k: [list(x) for x in v] for k, v in SERVICE_FAQS.items()},
        "gm60": GM60, "dual269": DUAL, "towns": towns,
    }
    (SITE / "data/network-pack.json").write_text(json.dumps(out, ensure_ascii=False, separators=(",", ":")) + "\n")
    (SITE / "data/network-pack-hubs.json").write_text(json.dumps(hubs, ensure_ascii=False, separators=(",", ":")) + "\n")
    # Hub stubs (one PHP file per hub so static export and the sitemap pick them up).
    stub = "<?php\n/** Network pack hub. Copy: data/network-pack-hubs.json (bin/build-network-pack.py). */\nrequire_once __DIR__ . '/../../includes/network-pack.php';\nrenderNetworkPackHub('%s');\n"
    for key in hubs:
        kind, slug = key.split("/")
        d = {"service": "services", "keyword": "keywords", "job": "jobs"}[kind]
        (SITE / "pages" / d / f"{slug}.php").write_text(stub % key)
    print(f"network-pack: {len(SERVICES)} service hubs, {len(NEW_KW)} keyword hubs, {len(JOBS)} job hubs, "
          f"{len(NEW_KW) * len(GM60)} keyword x GM-60, {len(JOBS) * len(GM60)} job x GM-60, {len(SERVICES) * len(DUAL)} service x 269")


if __name__ == "__main__":
    main()
