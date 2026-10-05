#!/usr/bin/env python3
"""Build the Property networking / Wi-Fi / Bluetooth + IT support P0 pack.

SEO lock: /workspace/icomply-ops/seo/NETWORKING-IT-DUAL-RING-LOCK-2026-10-05.md
P0 list:  /workspace/icomply-ops/seo/NETWORKING-IT-P0.txt (95)
Job list: /workspace/icomply-ops/seo/NETWORKING-IT-JOB-TYPES.txt (67)

Writes website/data/networking-it-pack.json plus thin PHP stubs for
/pages/keywords/{slug}, /pages/jobs/{slug} and /pages/services/{slug}.
Town pages are not files: the edge matrix serves keyword×town, job×town and
service×town on the dual-ring 269 from the catalogue.

Usage: python3 bin/build-networking-it-pack.py [--seo-dir DIR]
"""
import argparse, json, os, sys

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
sys.path.insert(0, HERE)
from networking_it.services import SERVICES, FAMILY  # noqa: E402
from networking_it.cores_it import CORES_IT  # noqa: E402
from networking_it.cores_net import CORES_NET  # noqa: E402
from networking_it.cores_wifi import CORES_WIFI  # noqa: E402
from networking_it.p0map import P0_MAP  # noqa: E402
from networking_it.modifiers import modifier_copy, NO_TIME  # noqa: E402
from networking_it.mod_extra import mod_extra  # noqa: E402
from networking_it.extras import PROPERTY_TYPES, PRICE_FACTORS, ON_SITE, FAMILY_FAQS  # noqa: E402
import csv, hashlib  # noqa: E402

TOWNS = []


def load_towns(seo_dir):
    with open(os.path.join(seo_dir, "AREA-HUB-ALLOWLIST-50MI-DUAL-2026-10-05.csv"), encoding="utf-8") as fh:
        rows = list(csv.DictReader(fh))
    if len(rows) != 269:
        sys.exit(f"Dual allowlist must be 269 towns, got {len(rows)}")
    TOWNS[:] = rows


def town_sample(slug, salt, n=10):
    ranked = sorted(TOWNS, key=lambda r: hashlib.sha1((salt + slug + r["slug"]).encode()).hexdigest())
    return [r["name"] for r in ranked[:n]]


def fill(text, cn):
    return text.replace("{cn}", cn).replace("{Cn}", cn[0].upper() + cn[1:])


def pick(slug, salt, items, n):
    """Deterministic per-slug subset (order preserved) so sibling pages do not repeat every block."""
    ranked = sorted(range(len(items)), key=lambda i: hashlib.sha1(f"{salt}|{slug}|{i}".encode()).hexdigest())
    return [items[i] for i in sorted(ranked[:n])]


def property_paras(fam, cn, slug=None, n=None):
    paras = [f"{title}. {fill(text, cn)}" for title, text in PROPERTY_TYPES[fam]]
    if slug is None or n is None:
        return paras
    return pick(slug, "prop", paras, n)


def towns_para(slug, salt, name, kind):
    towns = town_sample(slug, salt)
    return (f"Every one of the 269 dual-ring towns has its own {name} {kind} page under this one. Examples include "
            + ", ".join(towns[:-1]) + f" and {towns[-1]}. Coverage stops at that list; towns outside it are not served from these pages.")

CORES = {**CORES_IT, **CORES_NET, **CORES_WIFI}
SITE = "https://icomplypropertyservices.co.uk"

JOB_FAMILY = {
    "it-support": {
        "prep": "Before the job we ask for a list of the people affected, the devices involved, who holds the admin passwords, and any supplier contracts such as broadband, phones or lettings software. If the business has a previous IT provider, we ask for their handover notes too.",
        "exclude": "Not included unless the quote says so: hardware and licence costs, work on tenant-owned devices, third-party software that the vendor must support, data recovery from physically damaged drives, and any project such as a migration that has not been scoped.",
        "after": "After the job the business keeps every password and admin login. Recurring faults are flagged in the note so that the next spend goes where it fixes the most problems.",
    },
    "networking": {
        "prep": "Before the job we ask for photos of the comms cupboard and router, the broadband provider's details, a rough floor plan, and a list of other systems on the network such as CCTV recorders, door entry and card payment terminals.",
        "exclude": "Not included unless the quote says so: broadband or leased-line contracts, provider equipment, electrical supply work, builder's work in ceilings and walls, and fire-stopping where cables pass through compartment walls.",
        "after": "After the job the client keeps the configuration backups, the diagram and the admin credentials. If another engineer takes over later, the record lets them start without guesswork.",
    },
    "wifi": {
        "prep": "Before the job we ask for floor plans or photos of each floor, where the broadband enters the building, how many people and devices use the Wi-Fi, which rooms are worst, and whether guests or tenants need a separate network.",
        "exclude": "Not included unless the quote says so: broadband contracts, tenant-owned devices, electrical work, builder's work, and coverage outside the building such as car parks or yards.",
        "after": "After the job the client keeps the network names, passwords and controller login. The access point map shows where each unit is so that a failed one can be found and swapped.",
    },
    "structured-cabling": {
        "prep": "Before the job we ask for floor plans, the outlet positions wanted, where the comms cabinet is or should go, ceiling and floor types, and whether the building is occupied, listed or contains asbestos survey records that affect drilling.",
        "exclude": "Not included unless the quote says so: active equipment such as switches, electrical supply work, fire-stopping by a specialist, builder's work, and redecoration after containment is fitted.",
        "after": "After the job the client keeps the outlet schedule and test results. Labels on faceplates and the patch panel match the schedule so faults can be traced quickly.",
    },
}

JOB_FAMILY["bluetooth"] = {
    "prep": "Before the job we ask for a list of the Bluetooth devices, their makes and models, the computers or phones they should pair with, and the apps they are used in such as Teams, Zoom or a card-payment app.",
    "exclude": "Not included unless the quote says so: hardware repairs, which go to the manufacturer, replacement devices, and set-up on personal phones that the business does not manage.",
    "after": "After the job the business has a device list showing what is paired to what, plus simple reconnect steps for staff.",
}

SERVICE_HUB = {
    "it-support": {
        "hero_accent": "Onsite and remote. Written scope. POA.",
        "pillars": [
            ("Audit first", "We list users, devices, accounts and suppliers before changing anything."),
            ("Onsite or remote", "Remote where it fixes the fault; a visit from Stockport when hands are needed."),
            ("POA, no time promises", "Every job is price on application. We do not promise attendance or response times on this page."),
        ],
    },
    "networking": {
        "hero_accent": "Survey, configure, document. POA.",
        "pillars": [
            ("Map the network", "We document what is plugged into what, including CCTV, door entry and payment terminals."),
            ("Change safely", "Configuration backups before every change; tests before and after cut-over."),
            ("Hand it back", "Diagram, settings and admin logins stay with the client."),
        ],
    },
    "wifi": {
        "hero_accent": "Coverage where people sit. POA.",
        "pillars": [
            ("Survey the building", "Walls, floors, steel and lift shafts decide where access points go."),
            ("Separate networks", "Staff, guest and tenant traffic kept apart."),
            ("Bluetooth too", "Headsets, speakerphones, card readers and printers paired and tested."),
        ],
    },
    "structured-cabling": {
        "hero_accent": "Tested, labelled, scheduled. POA.",
        "pillars": [
            ("Planned routes", "Containment and separation from mains, agreed before drilling."),
            ("Every link tested", "Test results handed over with the as-built schedule."),
            ("Network side only", "CCTV camera supply stays with the CCTV service; we cable the data runs."),
        ],
    },
}


def load_list(path):
    with open(path, encoding="utf-8") as fh:
        return [ln.strip() for ln in fh if ln.strip()]


def lc(name):
    return name if name[:3].isupper() or name[:2] in ("VP", "LA", "SD") else name[0].lower() + name[1:]


def meta_desc(name, family):
    base = {
        "it-support": f"{name} for offices, letting agents and landlords from Stockport. Onsite or remote, written scope, price on application. 269 North West towns.",
        "networking": f"{name} for offices, blocks and HMOs from Stockport. Survey, configuration and handover notes. Price on application across 269 towns.",
        "wifi": f"{name} for offices, HMOs and blocks from Stockport. Survey-led, separate guest and staff networks. Price on application across 269 towns.",
        "structured-cabling": f"{name} from Stockport: planned routes, tested and labelled links, schedule handed over. Price on application across 269 towns.",
        "bluetooth": f"{name} for offices and meeting rooms from Stockport: headsets, speakerphones, card readers and printers paired and tested. Price on application.",
    }[family]
    if len(base) > 160:
        base = base.replace(" across 269 towns", "").replace(" 269 North West towns.", "")
    return base


def build_keyword(slug, core_key, mod, name, core, siblings):
    fam = core["family"]
    tf = core.get("text", fam)
    famtxt = FAMILY[tf]
    cn = lc(core["name"])
    intro, mod_para, mod_faqs = modifier_copy(mod, name, core["name"])
    body = core["what"] + " " + core["scope"]
    extra = mod_extra(mod, core_key)
    sections = [
        {"h2": f"How a {lc(name)} job is scoped", "p": [mod_para, core["pitfalls"]]},
        {"h2": f"The checks behind {cn}", "p": [
            f"On {cn} we work through four checks and write each one into the visit note: "
            + "; ".join(c[0].lower() + c[1:] for c in core["checks"])
            + f". If one of them shows a problem outside the {lc(name)} scope, it is reported and quoted separately rather than fixed without agreement."]},
    ]
    if extra:
        sections.append({"h2": f"Before you book {lc(name)}", "p": extra})
    sections += [
        {"h2": f"Who asks for {cn}", "p": pick(slug, "who", [famtxt["who"], famtxt["property"]], 1)},
        {"h2": f"{name} in different buildings", "p": property_paras(tf, cn, slug, 3)},
        {"h2": "Quotes, access and your data", "p": [famtxt["quote"], fill(PRICE_FACTORS[tf], cn)] + pick(slug, "data", [famtxt["data"], ON_SITE[tf]], 1)},
        {"h2": f"Towns covered for {lc(name)}", "p": [towns_para(slug, "kw", lc(name), "town"),
            "Related guides on this service: " + ", ".join(P0_MAP[x][2] for x in siblings[:6] if x != slug) + ". Each one has the same 269-town coverage and is quoted price on application."]},
        {"h2": "What you get at the end", "p": [
            core["handover"] + " No scheme badge, accreditation or certification is claimed on this page, and nothing here is a fixed fee.",
            "The office arranging the work is 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Coverage is the 269 towns in Greater Manchester and within 50 miles of Manchester and Burnley, each with its own town page under this guide. Phone 07517806082 or use the form below with the postcode and what you need.",
        ]},
    ]
    faqs = [list(f) for f in mod_faqs] + [list(f) for f in core["faqs"]] + [list(f) for f in FAMILY_FAQS[tf]]
    related = next((s for s in siblings if s != slug), slug)
    service = fam
    return {
        "name": name,
        "service": service,
        "related": related,
        "h1": name,
        "seo_title": f"{name} | Stockport & North West | iComply",
        "intro": intro,
        "body": body,
        "meta_desc": meta_desc(name, tf),
        "focus_points": core["checks"],
        "faq": faqs,
        "sections": sections,
        "seo_keywords": ", ".join([name, f"{name} Stockport", f"{name} Manchester", f"{name} Burnley", SERVICES[service]["label"], "North West"]),
        "pack": "networking-it-p0",
        "core": core_key,
        "modifier": mod or "",
    }


def build_job(slug, core_key, mod, name, core, job_siblings):
    fam = core["family"]
    tf = core.get("text", fam)
    jf = JOB_FAMILY[tf]
    checks = core["checks"]
    steps = (f"On a {lc(name)} job the order of work is: {checks[0].lower()}; then {checks[1].lower()}; "
             f"then {checks[2].lower()}; and finally {checks[3].lower()}. Each step is written into the visit note so the client can see what was done and what is left.")
    paragraphs = [
        core["what"],
        steps,
        jf["prep"],
        core["pitfalls"],
        jf["exclude"],
        core["handover"] + " " + jf["after"],
    ] + mod_extra(mod, core_key) + [
        FAMILY[tf]["who"],
        ON_SITE[tf],
        fill(PRICE_FACTORS[tf], lc(core["name"])),
    ] + property_paras(tf, lc(core["name"]), slug, 4) + [
        towns_para(slug, "job", lc(name), "job"),
        "Jobs are arranged from 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE, across the 269 towns in Greater Manchester and within 50 miles of Manchester and Burnley. Each town has a job page under this one. " + NO_TIME,
    ]
    points = list(checks) + ["Price on application after scope, with no published fee"]
    faqs = [
        (f"What happens on a {lc(name)} job?", steps),
        (f"What do you need from us before a {lc(name)} job?", jf["prep"]),
    ] + [tuple(f) for f in core["faqs"]] + [tuple(f) for f in FAMILY_FAQS[tf]]
    links = [[f"/pages/services/{fam}", SERVICES[fam]["label"]], [f"/pages/keywords/{slug}", f"{name} guide"]]
    for other in job_siblings[:3]:
        if other != slug:
            links.append([f"/pages/jobs/{other}", P0_MAP[other][2]])
    links.append(["/pages/jobs", "All job types"])
    return {
        "slug": slug,
        "name": name,
        "service": fam,
        "title": f"{name} job | Stockport & North West | iComply",
        "meta": meta_desc(name + " jobs", tf)[:160],
        "keywords": f"{name}, {name} job, Stockport, Manchester, Burnley, North West",
        "h1": name,
        "accent": {"it-support": "scoped, logged, handed back", "networking": "mapped, configured, documented",
                   "wifi": "surveyed, placed, checked", "structured-cabling": "routed, tested, labelled"}[fam],
        "kicker": f"{SERVICES[fam]['label']} · Job",
        "lede": f"What a {lc(name)} job covers from the first call to the handover note, and what stays out of scope.",
        "paragraphs": paragraphs,
        "points": points,
        "faqs": [list(f) for f in faqs],
        "links": links,
        "image": f"/assets/images/services/{fam}.jpg",
        "image_alt": f"{name} — iComply Property Services, Stockport",
        "service_label": SERVICES[fam]["label"],
        "service_href": f"/pages/services/{fam}",
        "parent_label": "Job types",
        "parent_href": "/pages/jobs",
        "focus": checks[:3],
    }


def service_hub_copy(svc, p0_by_service):
    fam = FAMILY[svc]
    hub = SERVICE_HUB[svc]
    kws = p0_by_service.get(svc, [])
    names = ", ".join(P0_MAP[s][2] for s in kws[:8])
    return {
        "hero_accent": hub["hero_accent"],
        "pillars": [{"title": t, "text": x} for t, x in hub["pillars"]],
        "intro": [SERVICES[svc]["blurb"], fam["who"], fam["property"]],
        "sections": [
            {"h2": "How the work is quoted", "p": [fam["quote"], NO_TIME]},
            {"h2": "Records you keep", "p": [fam["data"], "No scheme badge, accreditation or certification is claimed on this page."]},
            {"h2": "Guides and job types", "p": [f"Guides under this service include {names}. Each guide and job type has pages for the 269 towns we cover."]},
        ],
        "faq": [
            [f"What areas do you cover for {SERVICES[svc]['label']}?", "The 269 towns in Greater Manchester and within 50 miles of Manchester and Burnley, arranged from Offerton, Stockport SK2."],
            [f"How is {SERVICES[svc]['label']} priced?", "Price on application after scope. No fee is published on this page."],
            ["Do you promise attendance times?", "No. A visit or remote session is proposed when the diary allows."],
            ["Do you work with our existing suppliers?", "Yes. Broadband, phone, CCTV and software suppliers stay in place; we agree who does what."],
        ],
        "cta_line": "Postcode, building type, users or devices, and what is wrong or wanted. POA.",
        "quote_placeholder": "Postcode, building type, number of users/devices, what you need…",
        "links": [["/pages/services/" + o, SERVICES[o]["label"]] for o in SERVICES if o != svc] + [["/pages/jobs", "Job types"]],
    }


STUB_KW = """<?php
/** Networking / IT P0 keyword hub. Copy: data/networking-it-pack.json (bin/build-networking-it-pack.py). */
require_once __DIR__ . '/../../includes/render.php';
renderKeywordPage('{slug}');
"""
STUB_JOB = """<?php
/** Networking / IT P0 job hub. Copy: data/networking-it-pack.json (bin/build-networking-it-pack.py). */
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/networking-it.php';
renderNetworkingItJob('{slug}');
"""
STUB_SVC = """<?php
/** Networking / IT service hub. Copy: data/networking-it-pack.json (bin/build-networking-it-pack.py). */
require_once __DIR__ . '/../../includes/render.php';
renderServiceHubPage('{slug}');
"""


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--seo-dir", default="/workspace/icomply-ops/seo")
    args = ap.parse_args()
    load_towns(args.seo_dir)
    p0 = load_list(os.path.join(args.seo_dir, "NETWORKING-IT-P0.txt"))
    jobs_all = set(load_list(os.path.join(args.seo_dir, "NETWORKING-IT-JOB-TYPES.txt")))
    if len(p0) != len(set(p0)):
        sys.exit("P0 list has duplicates")
    missing = [s for s in p0 if s not in P0_MAP]
    extra = [s for s in P0_MAP if s not in p0]
    if missing or extra:
        sys.exit(f"P0 map out of sync: missing={missing} extra={extra}")
    banned = ("advert", "dooh", "billboard", "gas-safe")
    bad = [s for s in p0 if any(b in s for b in banned)]
    if bad:
        sys.exit(f"Banned slugs in P0: {bad}")

    by_core, by_service = {}, {}
    for s in p0:
        core_key = P0_MAP[s][0]
        by_core.setdefault(core_key, []).append(s)
        by_service.setdefault(CORES[core_key]["family"], []).append(s)

    keywords = {}
    for s in p0:
        core_key, mod, name = P0_MAP[s]
        core = CORES[core_key]
        sibs = by_core[core_key] + by_service[core["family"]]
        keywords[s] = build_keyword(s, core_key, mod, name, core, sibs)

    job_slugs = [s for s in p0 if s in jobs_all]
    jobs = {}
    for s in job_slugs:
        core_key, mod, name = P0_MAP[s]
        core = CORES[core_key]
        fam_jobs = [j for j in job_slugs if CORES[P0_MAP[j][0]]["family"] == core["family"] and j != s]
        jobs[s] = build_job(s, core_key, mod, name, core, fam_jobs)

    services = {k: dict(v, hub=service_hub_copy(k, by_service)) for k, v in SERVICES.items()}
    pack = {
        "version": 1,
        "lock": "/workspace/icomply-ops/seo/NETWORKING-IT-DUAL-RING-LOCK-2026-10-05.md",
        "p0_source": "NETWORKING-IT-P0.txt",
        "p0_count": len(p0),
        "geo": "dual-ring-269",
        "services": services,
        "keywords": keywords,
        "jobs": jobs,
    }
    out = os.path.join(ROOT, "data", "networking-it-pack.json")
    with open(out, "w", encoding="utf-8") as fh:
        json.dump(pack, fh, ensure_ascii=False, indent=1)
        fh.write("\n")

    def write(path, text):
        os.makedirs(os.path.dirname(path), exist_ok=True)
        with open(path, "w", encoding="utf-8") as fh:
            fh.write(text)

    for s in p0:
        write(os.path.join(ROOT, "pages", "keywords", s + ".php"), STUB_KW.replace("{slug}", s))
    for s in job_slugs:
        write(os.path.join(ROOT, "pages", "jobs", s + ".php"), STUB_JOB.replace("{slug}", s))
    for s in SERVICES:
        write(os.path.join(ROOT, "pages", "services", s + ".php"), STUB_SVC.replace("{slug}", s))
    print(f"keywords={len(keywords)} jobs={len(jobs)} services={len(services)} -> {out}")


if __name__ == "__main__":
    main()
