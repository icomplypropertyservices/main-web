"""Composition engine for Property SEO packs.

A pack config supplies services, families (with paragraph pools), cores, a P0
map (slug -> core, modifier, display name) and modifier copy. This module turns
that into keyword hubs, job hubs and service hubs with >=800 words of page
prose, FAQs, three on-topic images, full meta and absolute canonicals, then
self-checks word counts, doubled words, banned claims and 5-gram overlap.

Identical in every pack PR.
"""
import csv
import hashlib
import json
import os
import re
import sys

SITE = "https://icomplypropertyservices.co.uk"
NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"
PHONE = "07517806082"
BRAND = " | iComply Property Services"
GAS_PHRASE = "carried out by Gas Safe registered engineers"
PARTP_PHRASE = "carried out by Part P registered engineers"
NO_TIME = ("This page does not promise an attendance date, a start date or a response time. "
           "Visits and work are booked when the diary allows and are confirmed in writing, and every quote is price on application.")
TIMING_RE = re.compile(r"(within \d+ ?(minutes|mins|hours|hrs|days|weeks)|\d+[- ]hour response|same[- ]day|same[- ]week|next[- ]day|"
                       r"fast response|rapid response|guaranteed (attendance|response|arrival|start)|we will be there|on site in \d|"
                       r"in \d+ (days|weeks) flat|24/7 attendance)", re.I)

TOWNS = []


def load_list(path):
    with open(path, encoding="utf-8") as fh:
        return [ln.strip() for ln in fh if ln.strip()]


def load_towns(seo_dir):
    with open(os.path.join(seo_dir, "AREA-HUB-ALLOWLIST-50MI-DUAL-2026-10-05.csv"), encoding="utf-8") as fh:
        rows = list(csv.DictReader(fh))
    if len(rows) != 269:
        sys.exit(f"Dual allowlist must be 269 towns, got {len(rows)}")
    TOWNS[:] = rows
    return rows


def h(*parts):
    return hashlib.sha1("|".join(str(p) for p in parts).encode()).hexdigest()


def pick(slug, salt, items, n):
    """Deterministic per-slug subset (original order kept)."""
    ranked = sorted(range(len(items)), key=lambda i: h(salt, slug, i))
    return [items[i] for i in sorted(ranked[:n])]


def choose(slug, salt, items):
    return items[int(h(salt, slug), 16) % len(items)]


def town_sample(slug, salt, n=10):
    ranked = sorted(TOWNS, key=lambda r: h(salt, slug, r["slug"]))
    return [r["name"] for r in ranked[:n]]


def lc(name):
    """Lower-case the first letter unless it starts an acronym (HMO, AI, CCTV ...)."""
    first = name.split(" ")[0]
    if first.isupper() or any(c.isdigit() for c in first) or first in ("Article", "Part", "C4"):
        return name
    return name[0].lower() + name[1:]


def fill(text, ctx):
    out = text
    for k, v in ctx.items():
        out = out.replace("{" + k + "}", v)
    return out


def words(text):
    return re.findall(r"[A-Za-z0-9'\u2019-]+", text)


def doubled(text):
    return [m.group(0) for m in re.finditer(r"\b([A-Za-z]+)\s+\1\b", text, re.I) if m.group(1).lower() not in ("had", "that")]


def fit_meta(candidates):
    """First candidate 140-160 chars; else the closest one trimmed at a word boundary."""
    for c in candidates:
        if 140 <= len(c) <= 160:
            return c
    best = min(candidates, key=lambda c: abs(len(c) - 150))
    if len(best) > 160:
        cut = best[:157].rsplit(" ", 1)[0].rstrip(",;:")
        best = cut + "..."
    return best


def towns_para(slug, salt, cn, kind):
    towns = town_sample(slug, salt)
    return (f"Each of the 269 dual-ring towns has its own {cn} {kind} page under this one, for example "
            + ", ".join(towns[:-1]) + f" and {towns[-1]}. Coverage is that list of towns in Greater Manchester and within 50 miles of Manchester and Burnley; places outside it are not served from these pages.")


def shingles(text, n=5):
    w = re.findall(r"\w+", text.lower())
    return {" ".join(w[i:i + n]) for i in range(len(w) - n + 1)}


def jaccard(a, b):
    if not a or not b:
        return 0.0
    inter = len(a & b)
    return inter / (len(a) + len(b) - inter)


def resolve_core(cores, key):
    core = cores[key]
    if "alias_of" in core:
        base = dict(cores[core["alias_of"]])
        base.update({k: v for k, v in core.items() if k != "alias_of"})
        base["_alias"] = True
        return base
    return core


def keyword_text(k):
    parts = [k["intro"], k["body"]] + list(k["focus_points"])
    for s in k["sections"]:
        parts.append(s["h2"])
        parts.extend(s["p"])
    for q, a in k["faq"]:
        parts.append(q + " " + a)
    return " ".join(parts)


def job_text(j):
    parts = [j["lede"]] + list(j["paragraphs"]) + list(j["points"])
    for q, a in j["faqs"]:
        parts.append(q + " " + a)
    return " ".join(parts)


def build_keyword(cfg, slug, core_key, mod, name, siblings):
    core = resolve_core(cfg["cores"], core_key)
    fam_key = core["family"]
    fam = cfg["families"][fam_key]
    svc = fam["service"]
    cn = lc(core["name"])
    ln = lc(name)
    ctx = {"cn": cn, "Cn": cn[0].upper() + cn[1:], "name": name, "ln": ln}
    intro, mod_para, mod_faqs = cfg["modifier_copy"](mod, name, core, slug)
    pool = [fill(t, ctx) for t in fam["pool"]]
    picks = pick(slug, "kwpool", pool, cfg.get("pool_picks", 6))
    half = len(picks) // 2
    heads = fam["pool_heads"]
    h_a = choose(slug, "ha", heads[0]).replace("{ln}", ln).replace("{name}", name)
    h_b = choose(slug, "hb", heads[1]).replace("{ln}", ln).replace("{name}", name)
    body = core["what"] + " " + core["scope"]
    angle = [core["angle"]] if core.get("angle") else []
    sections = [
        {"h2": f"How {ln} is scoped", "p": angle + [mod_para, core["pitfalls"]]},
        {"h2": h_a, "p": picks[:half]},
        {"h2": "What we ask for before quoting", "p": [fill(fam["prep"], ctx)]},
        {"h2": h_b, "p": picks[half:]},
    ]
    extra = cfg.get("extra_sections")
    if extra:
        sections += extra(slug, core_key, core, mod, name)
    rel_names = [cfg["p0_map"][x][2] for x in siblings if x != slug][:6]
    sections += [
        {"h2": f"Towns covered for {ln}", "p": [
            towns_para(slug, "kw", ln, "town"),
            ("Related guides: " + ", ".join(rel_names) + ". Each has the same 269-town coverage and is priced on application.") if rel_names else "Related guides sit on the service hub linked below.",
        ]},
        {"h2": "What you have at the end", "p": [
            core["handover"] + " " + fill(fam["after"], ctx),
            f"The work is arranged from {NAP}. Phone {PHONE} or use the form below with the postcode, the property type and what you need. " + NO_TIME,
        ]},
    ]
    fam_faqs = pick(slug, "kwfaq", fam["faqs"], 2)
    faqs = [list(f) for f in mod_faqs] + [list(f) for f in core["faqs"]] + ([list(core["faq"])] if core.get("faq") else []) + [list(f) for f in fam_faqs]
    related = next((s for s in siblings if s != slug), slug)
    images = list(cfg["services"][svc]["images"])
    rot = int(h("img", slug), 16) % 3
    images = images[rot:] + images[:rot]
    row = {
        "name": name,
        "service": svc,
        "related": related,
        "h1": name,
        "seo_title": cfg["seo_title"](name, "keyword"),
        "intro": intro,
        "body": body,
        "meta_desc": cfg["meta_desc"](name, fam_key, "keyword", slug),
        "focus_points": list(core["checks"]),
        "faq": faqs,
        "sections": sections,
        "seo_keywords": ", ".join([name, f"{name} Stockport", f"{name} Manchester", f"{name} Burnley", cfg["services"][svc]["label"], "North West"]),
        "images": images,
        "core": core_key,
        "modifier": mod or "",
    }
    if slug in cfg.get("replaces_existing", set()):
        row["replaces_existing"] = True
    return row


def build_job(cfg, slug, core_key, mod, name, job_siblings):
    core = resolve_core(cfg["cores"], core_key)
    fam_key = core["family"]
    fam = cfg["families"][fam_key]
    svc = fam["service"]
    cn = lc(core["name"])
    ln = lc(name)
    ctx = {"cn": cn, "Cn": cn[0].upper() + cn[1:], "name": name, "ln": ln}
    checks = core["checks"]
    steps = (f"On a {ln} job the order of work is: {checks[0][0].lower() + checks[0][1:]}; then {checks[1][0].lower() + checks[1][1:]}; "
             f"then {checks[2][0].lower() + checks[2][1:]}; and finally {checks[3][0].lower() + checks[3][1:]}. Each stage is written up so the client can see what was done and what is still open.")
    pool = [fill(t, ctx) for t in fam["pool"]]
    picks = pick(slug, "jobpool", pool, cfg.get("job_pool_picks", 6))
    job_mod = cfg.get("job_modifier_copy")
    mod_bits = job_mod(mod, name, core, slug) if job_mod else []
    paragraphs = [core["what"], steps, fill(fam["prep"], ctx), core["pitfalls"], fill(fam["exclude"], ctx)] + mod_bits + picks[:3] + \
                 [core["handover"] + " " + fill(fam["after"], ctx)] + picks[3:] + [
        towns_para(slug, "job", ln, "job"),
        f"Jobs are arranged from {NAP}, across the 269 towns in Greater Manchester and within 50 miles of Manchester and Burnley. " + NO_TIME,
    ]
    points = list(checks) + ["Price on application after the scope is agreed; no fee is published"]
    faqs = [
        (f"What happens on a {ln} job?", steps),
        (f"What do you need from us before a {ln} job?", fill(fam["prep"], ctx)),
        (f"What is not included in a {ln} job?", fill(fam["exclude"], ctx)),
    ] + [tuple(f) for f in core["faqs"]] + [tuple(f) for f in pick(slug, "jobfaq", fam["faqs"], 2)]
    links = [[f"/pages/services/{svc}", cfg["services"][svc]["label"]], [f"/pages/keywords/{slug}", f"{name} guide"]]
    for other in job_siblings[:4]:
        if other != slug:
            links.append([f"/pages/jobs/{other}", cfg["p0_map"][other][2]])
    links.append(["/pages/jobs", "All job types"])
    image = cfg["services"][svc]["images"][0]
    return {
        "slug": slug,
        "name": name,
        "service": svc,
        "group": cfg["services"][svc]["label"],
        "title": cfg["seo_title"](name, "job"),
        "meta": cfg["meta_desc"](name, fam_key, "job", slug),
        "keywords": f"{name}, {name} job, Stockport, Manchester, Burnley, North West",
        "h1": name,
        "accent": fam["job_accent"],
        "kicker": f"{cfg['services'][svc]['label']} · Job",
        "lede": f"What a {ln} job covers from the first enquiry to the handover, and what stays out of scope.",
        "blurb": core["what"].split(". ")[0].rstrip(".") + ".",
        "paragraphs": paragraphs,
        "points": points,
        "faqs": [list(f) for f in faqs],
        "links": links,
        "image": image,
        "image_alt": f"{name}, iComply Property Services, Stockport",
        "service_label": cfg["services"][svc]["label"],
        "service_href": f"/pages/services/{svc}",
        "parent_label": "Job types",
        "parent_href": "/pages/jobs",
        "focus": list(checks[:3]),
        "core": core_key,
        "modifier": mod or "",
    }


STUB_KW = """<?php
/** {title} keyword hub. Copy: data/property-packs/{pack}.json (bin/build-{pack}-pack.py). */
require_once __DIR__ . '/../../includes/render.php';
renderKeywordPage('{slug}');
"""
STUB_JOB = """<?php
/** {title} job hub. Copy: data/property-packs/{pack}.json (bin/build-{pack}-pack.py). */
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/property-packs.php';
renderPropertyPackJob('{slug}');
"""
STUB_SVC = """<?php
/** {title} service hub. Copy: data/property-packs/{pack}.json (bin/build-{pack}-pack.py). */
require_once __DIR__ . '/../../includes/render.php';
renderServiceHubPage('{slug}');
"""


def self_check(cfg, keywords, jobs, max_j=0.7):
    """Word counts, doubled words, banned claims and 5-gram overlap across every hub."""
    problems = []
    texts = {}
    for s, k in keywords.items():
        texts["kw:" + s] = keyword_text(k)
    for s, j in jobs.items():
        texts["job:" + s] = job_text(j)
    min_words = 10 ** 9
    for key, t in texts.items():
        n = len(words(t))
        min_words = min(min_words, n)
        if n < 800:
            problems.append(f"{key} only {n} words")
        d = doubled(t)
        if d:
            problems.append(f"{key} doubled words {d[:3]}")
        if TIMING_RE.search(t):
            problems.append(f"{key} timing promise: {TIMING_RE.search(t).group(0)}")
        if "\u00a3" in t:
            problems.append(f"{key} contains a pound price")
        for m in re.finditer(r"Gas Safe", t):
            if t[max(0, m.start() - 15):m.start() + 34] != GAS_PHRASE:
                problems.append(f"{key} Gas Safe outside the approved phrase")
                break
        for m in re.finditer(r"Part P", t):
            seg = t[max(0, m.start() - 15):m.start() + 33]
            if seg != PARTP_PHRASE and "Part P electrical" not in t[m.start():m.start() + 20]:
                problems.append(f"{key} Part P outside the approved phrase")
                break
        for bad in cfg.get("banned_copy", []):
            if re.search(bad, t, re.I):
                problems.append(f"{key} banned copy /{bad}/")
    sh = {k: shingles(t) for k, t in texts.items()}
    keys = list(sh)
    worst = (0.0, "")
    for i in range(len(keys)):
        for j in range(i + 1, len(keys)):
            v = jaccard(sh[keys[i]], sh[keys[j]])
            if v > worst[0]:
                worst = (v, f"{keys[i]} ~ {keys[j]}")
    if worst[0] > max_j:
        problems.append(f"overlap {worst[0]:.2f} {worst[1]}")
    return problems, min_words, worst


def build_pack(cfg):
    seo = cfg["seo_dir"]
    load_towns(seo)
    p0 = load_list(os.path.join(seo, cfg["p0_file"]))
    if len(p0) != len(set(p0)):
        sys.exit("P0 list has duplicates")
    if len(p0) != cfg["expected_p0"]:
        sys.exit(f"P0 count {len(p0)} != lock {cfg['expected_p0']}")
    pmap = cfg["p0_map"]
    missing = [s for s in p0 if s not in pmap]
    extra = [s for s in pmap if s not in p0]
    if missing or extra:
        sys.exit(f"P0 map out of sync: missing={missing} extra={extra}")
    banned = cfg.get("banned_slug_re")
    if banned:
        bad = [s for s in p0 if re.search(banned, s)]
        if bad:
            sys.exit(f"Banned slugs in P0: {bad}")
    jobs_all = set()
    for f in cfg["jobtypes_files"]:
        jobs_all |= set(load_list(os.path.join(seo, f)))
    by_core, by_service = {}, {}
    for s in p0:
        ck = pmap[s][0]
        core = resolve_core(cfg["cores"], ck)
        base = cfg["cores"][ck].get("alias_of", ck)
        by_core.setdefault(base, []).append(s)
        by_service.setdefault(cfg["families"][core["family"]]["service"], []).append(s)
    keywords = {}
    for s in p0:
        ck, mod, name = pmap[s]
        core = resolve_core(cfg["cores"], ck)
        base = cfg["cores"][ck].get("alias_of", ck)
        svc = cfg["families"][core["family"]]["service"]
        sibs = by_core[base] + [x for x in by_service[svc] if x not in by_core[base]]
        sibs = sibs[:3] + pick(s, "sibs", sibs[3:], 5)
        keywords[s] = build_keyword(cfg, s, ck, mod, name, sibs)
    job_slugs = [s for s in p0 if s in jobs_all]
    jobs = {}
    # One canonical URL per slug (SEO ruling 2026-10-06): P0 job intents are served by
    # the keyword hub and keyword×town, so job hubs are only built when a pack opts in.
    for s in (job_slugs if cfg.get("job_hubs") else []):
        ck, mod, name = pmap[s]
        svc = cfg["families"][resolve_core(cfg["cores"], ck)["family"]]["service"]
        fam_jobs = [j for j in job_slugs if j != s and cfg["families"][resolve_core(cfg["cores"], pmap[j][0])["family"]]["service"] == svc]
        jobs[s] = build_job(cfg, s, ck, mod, name, pick(s, "jsibs", fam_jobs, 4))
    services = {}
    for svc, data in cfg["services"].items():
        row = {k: v for k, v in data.items() if k != "hub_builder"}
        row["hub"] = data["hub_builder"](svc, by_service.get(svc, []), keywords, jobs)
        services[svc] = row
    problems, min_words, worst = self_check(cfg, keywords, jobs, cfg.get("max_jaccard", 0.7))
    pack = {
        "version": 1,
        "pack": cfg["pack_id"],
        "title": cfg["title"],
        "lock": "/workspace/icomply-ops/seo/" + cfg["lock"],
        "p0_source": cfg["p0_file"],
        "p0_count": len(p0),
        "geo": "dual-ring-269",
        "matrix": {"keyword_x_town": len(keywords) * 269, "job_x_town": len(jobs) * 269, "job_intents_x_town_via_keyword": len(job_slugs) * 269, "service_x_town": len(services) * 269},
        "categories": cfg.get("categories", {}),
        "services": services,
        "keywords": keywords,
        "jobs": jobs,
        "job_intents_served_by_keyword_hub": [] if cfg.get("job_hubs") else job_slugs,
        "image_credits": cfg.get("image_credits", []),
    }
    root = cfg["root"]
    out = os.path.join(root, "data", "property-packs", cfg["pack_id"] + ".json")
    os.makedirs(os.path.dirname(out), exist_ok=True)
    with open(out, "w", encoding="utf-8") as fh:
        json.dump(pack, fh, ensure_ascii=False, indent=1)
        fh.write("\n")

    def write(path, text):
        os.makedirs(os.path.dirname(path), exist_ok=True)
        with open(path, "w", encoding="utf-8") as fh:
            fh.write(text)

    t = cfg["title"]
    for s in p0:
        write(os.path.join(root, "pages", "keywords", s + ".php"), STUB_KW.replace("{slug}", s).replace("{pack}", cfg["pack_id"]).replace("{title}", t))
    for s in jobs:
        write(os.path.join(root, "pages", "jobs", s + ".php"), STUB_JOB.replace("{slug}", s).replace("{pack}", cfg["pack_id"]).replace("{title}", t))
    for s in services:
        write(os.path.join(root, "pages", "services", s + ".php"), STUB_SVC.replace("{slug}", s).replace("{pack}", cfg["pack_id"]).replace("{title}", t))
    print(f"keywords={len(keywords)} job_hubs={len(jobs)} job_intents={len(job_slugs)} services={len(services)} min_words={min_words} worst_overlap={worst[0]:.2f} ({worst[1]}) -> {out}")
    for p in problems:
        print("PROBLEM:", p)
    return 1 if problems else 0
