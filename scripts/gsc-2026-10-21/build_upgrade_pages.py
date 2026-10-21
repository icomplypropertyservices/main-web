#!/usr/bin/env python3
"""GSC 21 Oct (task 5): 165 pages at position >20 and 55 quick wins at 8-20 from
rankings/2026-10-21/upgrade-queue.csv. Writes website/data/gsc-upgrade-2026-10-21.json,
read by netlify/lib/money-ctr.js and website/includes/money-ctr.php (C1-C15 entries win).
Each page gets: a title <=60 with Same/Next-Day, a meta of 140-155 ending
"Free quote, call 07517 806082", a page-specific local section, FAQs and related links
(85 unique internal links on the page). Never touches H1, robots, canonical or sitemap.
Prices: EICR 'from £160'; FRA 'From £350 for a typical 6-bed HMO in the North West; other
properties POA'; everything else POA."""
import csv, hashlib, json, math, pathlib, re
D = pathlib.Path(__file__).resolve().parent
ROOT = D.parents[1]
J = lambda p: json.loads((ROOT / p).read_text())
KW = J("website/data/keywords.json")
SVC = J("website/data/services.json")
GEO = J("website/data/town-geo-slim.json")
ALLOW = J("website/data/dual-ring-allowlist.json")["towns"]
LOCAL = {t["slug"]: t for t in ALLOW}
KEEP = set(J("website/data/gsc-keep-towns.json")["paths"])
CTR = set(J("website/data/money-ctr-c1-c15.json")["pages"])
PHONE = "07517 806082"; MAIL = "icomplypropertyservices@gmail.com"
FRA = "From £350 for a typical 6-bed HMO in the North West; other properties POA"

def h(s, n):
    return int(hashlib.sha1(s.encode()).hexdigest(), 16) % n

def pick(seed, opts):
    return opts[h(seed, len(opts))]

def tname(slug):
    if slug in LOCAL: return LOCAL[slug]["name"]
    if slug in GEO: return GEO[slug][0]
    return " ".join(w.capitalize() for w in slug.split("-"))

def geo(slug):
    g = GEO.get(slug)
    if not g: return None
    return {"pcs": [p for p in g[2] if p][:4], "wards": [w for w in g[3] if w][:5], "seat": g[4], "lat": g[5], "lon": g[6]}

def near(slug, n=8):
    g = GEO.get(slug)
    if not g: return []
    out = []
    for t in LOCAL:
        if t == slug or t not in GEO: continue
        o = GEO[t]
        d = math.hypot((o[5] - g[5]) * 69, (o[6] - g[6]) * 69 * math.cos(math.radians(g[5])))
        out.append((d, t))
    return [(t, round(d, 1)) for d, t in sorted(out)[:n]]

def price_line(slug):
    if "eicr" in slug: return "EICRs are from £160 for a typical property; larger or older installations are POA until we know the circuit count."
    if "fire-risk-assessment" in slug: return FRA + "."
    return "Pricing is POA: you get a written quote once the job has been seen, never a guessed figure over the phone."

def title_for(name, town, slug):
    fr = " from £160" if "eicr" in slug else (" from £350" if "fire-risk-assessment" in slug else "")
    cands = []
    if town:
        cands += [f"{name} {town}{fr} | Same/Next-Day | iComply", f"{name} {town}{fr} | Same/Next-Day",
                  f"{name} in {town}{fr} | Same/Next-Day", f"{name} {town} | Same/Next-Day"]
    cands += [f"{name}{fr} | Same/Next-Day | iComply", f"{name}{fr} | Same/Next-Day", f"{name} | Same/Next-Day"]
    for c in cands:
        if len(c) <= 60 and (not fr or fr in c or not town): return c
    short = re.sub(r"\b(Installation|Services?|Systems?)\b", "", name).replace("  ", " ").strip()
    c = f"{short} {town} | Same/Next-Day" if town else f"{short} | Same/Next-Day"
    return c if len(c) <= 60 else c[:44].rsplit(" ", 1)[0] + " | Same/Next-Day"

def meta_for(name, town, slug):
    tail = ". Free quote, call 07517 806082"
    if "fire-risk-assessment" in slug:
        where = f" in {town}" if town else ""
        for o in [f"{name}{where}. From £350 for a typical 6-bed HMO in the North West; other properties POA",
                  f"{name}{where}, from £350 for a typical 6-bed HMO in the North West; other properties POA"]:
            for pad in ["", ", Same/Next-Day", " Same/Next-Day"]:
                if 140 <= len(o + pad + tail) <= 155: return o + pad + tail
    where = f"in {town}" if town else "across Greater Manchester and the North West"
    pr = " from £160" if "eicr" in slug else (" from £350 for a typical 6-bed HMO" if "fire-risk-assessment" in slug else "")
    opts = [f"{name}{pr} {where}. Local team from Stockport, written scope, Same/Next-Day where the diary allows",
            f"{name}{pr} {where}. Local team from Stockport, written scope and Same/Next-Day visits",
            f"{name}{pr} {where}. Local Stockport team, written scope, Same/Next-Day visits",
            f"{name}{pr} {where} from our Stockport team, with a written scope",
            f"{name}{pr} {where}. Written scope, Same/Next-Day visits",
            f"{name}{pr} {where}, Same/Next-Day",
            f"{name} {where}"]
    for o in opts:
        if 140 <= len(o + tail) <= 155: return o + tail
    for o in opts:
        for pad in [" and tidy, careful work on site", " and clear dates", " and tidy work", " and updates"]:
            if 140 <= len(o + pad + tail) <= 155: return o + pad + tail
    o = opts[-1]
    while len(o + tail) > 155: o = o.rsplit(" ", 1)[0]
    return o + tail

OPEN = [
 "{kw} in {town} usually starts with a phone call or a few photos. We ask what you have noticed, how old the property is and whether anyone is living there, then book a visit that suits you.",
 "Most {kwl} jobs we see in {town} begin with a simple question: what is wrong, and what will it take to put it right? We answer that on site, in writing, before any work is agreed.",
 "If you are looking for {kwl} in {town}, the first thing we do is see the job. A visit lets us check access, the condition of what is already there and what the work really involves.",
 "Homeowners, landlords and small businesses in {town} call us for {kwl} when they want a clear answer and a date. We visit, explain what we found and give you a written scope.",
]
LOCALP = [
 "We cover all of {town}{pcs}, including {wards}. Our team travels from Stockport, and {nearl} are on the same routes, so a visit can often be fitted in around jobs nearby.",
 "Work in {town} takes in {wards}{pcs}. We plan visits around our other jobs in {nearl}, which is how we keep Same/Next-Day slots open where the diary allows.",
 "Our engineers know {town} well: {wards}{pcs}. Jobs here sit alongside regular work in {nearl}, so travel time stays short and dates are easier to agree.",
]
SCOPE = [
 "The written scope sets out what is included, what is not, the materials, how long the work should take and what you get at the end. {focus}",
 "Before any work starts you get the scope in writing: the tasks, the parts, the time on site and the paperwork you will receive. {focus}",
 "You will know exactly what you are paying for. The quote lists the work, the materials and the finish, and says what happens if we find something unexpected. {focus}",
]
AFTER = [
 "On the day we protect floors and furniture, keep the area safe and tidy up before we leave. If the job needs a certificate or report, it is sent to you by email with photos of the finished work.",
 "We arrive at the agreed time, explain the plan again and keep you updated as the work goes on. At the end we walk you through what was done and leave any paperwork and photos with you.",
 "Work is done with care for the property and the people in it. Once finished we test what needs testing, clear up and send you the paperwork and photos for your records.",
]
CLOSE = [
 "To book {kwl} in {town}, call {phone} or email {mail} with your postcode and a short description of the job. {price}",
 "Call {phone} or email {mail} to arrange {kwl} in {town}. Tell us your postcode and what you need, and we will come back with a date. {price}",
 "For {kwl} in {town}, ring {phone} or send the details to {mail}. A postcode, a photo and a line about the problem is enough to start. {price}",
]

SERVICE_COPY = {
 "fire-extinguishers": [
  "Portable fire extinguishers need a basic service every year under BS 5306-3, carried out by a trained technician. That service checks the cylinder for damage and corrosion, confirms the pressure gauge reads in the green, weighs CO2 units, replaces tamper seals and records the visit on the label and in your logbook.",
  "Every five years water, foam and powder extinguishers need an extended service, which means discharging the unit, inspecting the inside and refilling it; CO2 units are pressure tested or replaced at ten years. We tell you in advance which units are due so you can budget, and swap in a replacement when that is cheaper than refurbishing.",
  "Choosing the right type matters. Water and foam suit paper, wood and fabric; CO2 suits electrical equipment; wet chemical is for kitchen deep-fat fryers; and powder is usually kept for outdoor or plant areas because it is messy indoors. We match types to the risks in each room rather than leaving the same pair on every landing.",
  "Units should hang on a bracket with the handle about a metre from the floor, close to exits and on escape routes, with a sign showing the type. Fire blankets belong in kitchens. In HMOs and small blocks the fire risk assessment usually sets out what is needed and where; we can follow it or point out where it looks out of date.",
  "Common faults we find on extinguisher visits include missing or broken tamper tags, units knocked off brackets and used as door stops, gauges reading low after a discharge nobody reported, and CO2 horns that have cracked. Tenants and staff rarely mention these, so a yearly visit is often the only time they are caught.",
 ],
 "fire-stopping": [
  "Fire stopping seals the gaps where pipes, cables and ducts pass through fire-resisting walls and floors, so smoke and flame cannot jump from one compartment to the next. Holes left by trades during refits are one of the most common findings in fire risk assessments of flats and HMOs.",
  "We survey service risers, ceiling voids and plant rooms, photograph each penetration and seal it with a tested product suited to what passes through it: intumescent mastic, pillows, collars on plastic pipes, batt and coating systems for cable bundles, or fire-rated boards for larger openings.",
  "Each seal is labelled and logged with before-and-after photos, the product used and its fire rating, so you have evidence for the fire risk assessment, building insurer or the next survey. Where a seal is outside a product's tested range, we say so rather than guessing.",
  "Work is planned around residents, with dust kept down and services left running. If the survey shows wider compartment problems, such as missing cavity barriers or poor door sets, we list them separately so you can prioritise.",
  "Typical problem spots are the riser cupboards on each floor, gaps above suspended ceilings where corridors meet flats, holes around soil stacks, and new data or TV cables pushed through after a block was handed over. A short walk-round often finds dozens in a single building.",
 ],
 "fire-suppression": [
  "Fire suppression covers fixed systems that put a fire out or hold it back automatically: wet chemical systems over commercial cooking, gas or aerosol systems in server and plant rooms, and water mist where sprinklers are not practical.",
  "Each system is designed around the hazard. A kitchen canopy system has nozzles aimed at each appliance and a gas shut-off; a server room system is sized to the room volume with a sealed enclosure so the agent holds its concentration long enough.",
  "Systems need servicing to the manufacturer's schedule, usually every six or twelve months: checking cylinders and pressures, detection, release lines, nozzles and interlocks, and testing the link to the fire alarm. We record every visit and tell you about anything that needs replacing before it fails an inspection.",
  "Insurers often ask for suppression in commercial kitchens and in rooms holding valuable equipment. We can survey what is there, explain whether it still covers the current layout and quote for upgrades where appliances or rooms have changed.",
  "Changes to a kitchen, such as a new fryer, a moved range or a longer canopy, can leave an older suppression system no longer covering the cooking line. A quick survey after any refit tells you whether nozzles need moving or adding before your next insurer visit.",
 ],
 "electrics-first-fix": [
  "First fix electrics is everything that goes in before the plaster: cable runs, back boxes, ceiling roses, consumer unit position and any containment, all set out from your drawings or a walk-round with you. Getting it right now saves chasing walls later.",
  "We mark out sockets, switches, lighting, data points and outdoor supplies with you on site, so heights and positions suit how you will use each room. Kitchens, bathrooms and garden rooms get extra attention because their layouts change most often.",
  "Cables are clipped and protected, notched joists are kept within limits, and safe zones are followed so future drilling does not hit a cable. Circuits are labelled at the consumer unit end ready for second fix and testing.",
  "First fix fits into the wider build: we coordinate with plumbers, joiners and plasterers so boards go up on time. When the walls are finished we return for second fix, testing and the certificate.",
  "Typical first fix jobs include extensions, loft conversions, kitchen refits, garage conversions and full rewires during a renovation. We work from your plans, or help draw up a simple layout if you only have an idea of where things should go.",
 ],
}

def lst(xs):
    xs = [x for x in xs if x]
    return xs[0] if len(xs) == 1 else ", ".join(xs[:-1]) + " and " + xs[-1] if xs else ""

def build(path):
    m = re.match(r"^/pages/keywords/([a-z0-9-]+)(?:/([a-z0-9-]+))?$", path)
    town_slug = None
    if m:
        kslug, town_slug = m.group(1), m.group(2)
        k = KW.get(kslug)
        if not k: return None
        name, service = k["name"], k["service"]
        focus = list(k.get("focus_points") or [])
    else:
        m2 = re.match(r"^/pages/(?:services/)?([a-z0-9-]+)(?:/([a-z0-9-]+))?$", path)
        if not m2: return None
        service, town_slug = m2.group(1), m2.group(2)
        if path.startswith("/pages/services/"): town_slug = None
        if path.startswith("/pages/jobs/"):
            job = m2.group(2) or service
            service = next((k for k in sorted(SVC, key=len, reverse=True) if k.rstrip("s") in job), "building-maintenance")
            if "barrier" in job: service = "barriers"
            town_slug = None
            name = " ".join(w.capitalize() for w in job.split("-"))
        else:
            name = SVC.get(service) or service.replace("-", " ").title()
        kslug = service; focus = []
    svc_name = SVC.get(service, service.replace("-", " ").title())
    town = tname(town_slug) if town_slug else ""
    seed = path
    g = geo(town_slug) if town_slug else None
    nr = near(town_slug) if town_slug else []
    kwl = " ".join(w if (w.isupper() and len(w) > 1) else w.lower() for w in name.split())
    wards = lst((g or {}).get("wards", [])[:4]) or f"the centre of {town}"
    pcs = f" ({', '.join(g['pcs'])} postcodes)" if g and g["pcs"] else ""
    nearl = lst([f"{tname(t)} ({d} miles)" for t, d in nr[:4]]) or "Greater Manchester"
    fpts = [f.rstrip(".") for f in focus if not re.search(r"facilities managers|investors|light commercial|Gas Safe|£", f, re.I)]
    focus_s = ("It covers " + "; ".join(x[0].lower() + x[1:] for x in fpts[:3]) + ".") if fpts else ""
    town_disp = town or "Greater Manchester"
    fmt = dict(kw=name, kwl=kwl, town=town_disp, wards=wards, pcs=pcs, nearl=nearl, focus=focus_s,
               phone=PHONE, mail=MAIL, price=price_line(kslug))
    paras = [pick(seed + "o", OPEN).format(**fmt)]
    intro = (KW.get(kslug) or {}).get("intro", "")
    intro = re.sub(r"^\s*(Searching for|Looking for|Need|Want)[^?]*\?\s*", "", intro)
    intro = " ".join(x for x in re.split(r"(?<=\.)\s+", intro) if not re.search(r"facilities managers|FM teams|HMO|investors", x)).strip()
    if intro and not re.search(r"£|Gas Safe|info@|07471", intro):
        paras.append(intro)
    if town_slug: paras.append(pick(seed + "l", LOCALP).format(**fmt))
    paras += [pick(seed + "s", SCOPE).format(**fmt).strip(), pick(seed + "a", AFTER).format(**fmt)]
    if "gas" in service or "boiler" in kslug:
        paras.append("All gas work is carried out by Gas Safe registered engineers, and the safety checks are recorded on the job sheet you receive.")
    if not town_slug:
        # Hubs: more page-specific depth (task 5 asks for 800+ words on every queued page).
        body = (KW.get(kslug) or {}).get("body", "")
        body = " ".join(x for x in re.split(r"(?<=\.)\s+", body) if not re.search(r"facilities managers|FM teams|investors|£|Gas Safe|info@|07471", x)).strip()
        if body and body != intro: paras.append(body)
        towns = [t["name"] for t in ALLOW if t.get("bucket") == "gm_core"][:0] or ["Manchester", "Stockport", "Bolton", "Oldham", "Rochdale", "Wigan", "Bury", "Salford", "Warrington", "Preston", "Blackburn", "Burnley"]
        start = h(seed + "t", len(towns))
        tl = towns[start:] + towns[:start]
        if path.startswith("/pages/services/") and service in SERVICE_COPY:
            paras += SERVICE_COPY[service]
        else:
            bank = [
                [f"What affects the cost of {kwl}? Mostly the size of the property, access, the condition of what is already there and any making good afterwards. {price_line(kslug)}",
                 f"The price of {kwl} depends on the building more than anything: how big it is, how easy it is to reach the work and what state the existing installation is in. {price_line(kslug)}",
                 f"No two {kwl} jobs cost the same. Size, access, existing condition and finishing work all move the figure, which is why we quote after seeing it. {price_line(kslug)}"],
                [f"We carry out {kwl} for homeowners, landlords, letting agents and small businesses, and plan the work around whoever uses the building.",
                 f"Customers for {kwl} range from families in terraced homes to landlords with a handful of flats and shop owners who need the work done out of hours.",
                 f"Whether it is your own home, a rented flat or a small business unit, {kwl} is planned so the people inside are disturbed as little as possible."],
                [f"Our team works across {lst(tl[:8])}. Same/Next-Day slots are offered where the diary allows.",
                 f"From {tl[0]} and {tl[1]} to {tl[2]}, {tl[3]} and {tl[4]}, we book {kwl} visits on routes we already drive, which keeps dates short.",
                 f"We are regularly in {lst(tl[2:7])}, so a {kwl} visit can usually be fitted in within days."],
                [f"Before the visit, gather any old certificates, photos of the problem and the times the property is occupied. It helps us bring the right parts for {kwl}.",
                 f"A few photos and any previous paperwork make a big difference: we arrive with the right kit and finish {kwl} in fewer visits.",
                 f"If you have earlier reports, drawings or photos, send them over before we come out for {kwl}; it saves a return trip."],
                [f"Afterwards you get a summary, photos of the finished job and any certificate that applies to {kwl}.",
                 f"Once {kwl} is complete we send the paperwork and photos by email, with any follow-up items explained plainly.",
                 f"You keep a clear record of the {kwl} work: what was done, when, photos and any certificate or report."],
            ]
            order = sorted(range(len(bank)), key=lambda i: h(seed + str(i), 1000))
            paras += [pick(seed + "b" + str(i), bank[i]) for i in order]
            rel = [v["name"] for k2, v in KW.items() if v.get("service") == service and k2 != kslug][:40]
            rel.sort(key=lambda x: h(seed + x, 10**6))
            if rel: paras.append(f"Related work we often do alongside {kwl}: {lst(rel[:6])}.")
            paras.append(pick(seed + "w", [
                f"Why use us for {kwl}? A small local team, a written scope before any work, and the same people from first visit to sign-off. If plans change on site, the change is priced and agreed before it goes ahead, so the final bill matches what you approved.",
                f"People choose us for {kwl} because we turn up when we say, explain the job in plain English and put everything in writing. Any extra work found on the day is shown to you and priced before we carry on.",
                f"With {kwl} you deal with one local team from start to finish. We confirm dates in writing, keep you posted during the work and leave the property tidy, with nothing added to the bill that you did not agree first.",
            ]))
        for q, a_ in ((KW.get(kslug) or {}).get("faq") or [])[:4]:
            if not re.search(r"£|Gas Safe|info@|07471|facilities managers", a_):
                paras.append(f"{q} {a_}")
    paras.append(pick(seed + "c", CLOSE).format(**fmt))
    faqs = [
        [f"How soon can you do {kwl} in {town_disp}?", f"We offer Same/Next-Day visits where the diary allows. Call {PHONE} and we will give you the earliest slot for your postcode."],
        [f"How much does {kwl} cost?", price_line(kslug)],
        [f"Do you cover the whole of {town_disp}?", (f"Yes, including {wards}. We also work in {nearl}." if town_slug else "Yes. We cover Greater Manchester, Lancashire, Cheshire, Merseyside and the wider North West from Stockport.")],
        [f"What do I need to send to get a quote for {kwl}?", f"Your postcode, a short description and a photo or two if you can. Email {MAIL} or call {PHONE}."],
    ]
    links = []
    hub = f"/pages/keywords/{kslug}" if path.startswith("/pages/keywords/") else None
    if hub and hub != path: links.append([hub, f"{name} guide"])
    links.append([f"/pages/services/{service}", f"{svc_name} services"])
    if town_slug and town_slug in LOCAL:
        links.append([f"/pages/areas/{town_slug}", f"All services in {town}"])
    hubs = [l[0] for l in links]
    for t, d in nr:
        if path.startswith("/pages/keywords/"):
            links.append([f"/pages/keywords/{kslug}/{t}", f"{name} in {tname(t)}"])
        links.append([f"/pages/areas/{t}", f"Property services in {tname(t)}"])
    sib = [s for s, v in KW.items() if v.get("service") == service and s != kslug and not v.get("hub_only")]
    sib.sort(key=lambda s: h(seed + s, 10**6))
    for s in sib[:30]:
        if town_slug and town_slug in LOCAL:
            links.append([f"/pages/keywords/{s}/{town_slug}", f"{KW[s]['name']} in {town}"])
        links.append([f"/pages/keywords/{s}", KW[s]["name"]])
    links += [["/pages/services", "All services"], ["/pages/areas", "Areas we cover"], ["/pages/jobs", "Job types"], ["/contact", "Contact us"]]
    words = sum(len(p.split()) for p in paras)
    return {
        "brief": "GSC-2026-10-21",
        "kind": "xtown" if town_slug else "hub",
        "minWords": 800,
        "title": title_for(name, town, kslug),
        "meta": meta_for(name, town, kslug),
        "h2": f"{name} in {town_disp}: what to expect",
        "paras": paras,
        "faqHeading": f"{name} in {town_disp}: common questions",
        "faqs": faqs,
        "linksHeading": f"More {svc_name.lower()} pages near {town_disp}",
        "links": links,
        "hubs": hubs,
        "linkTarget": 85,
        "_words": words,
    }

import sys
DIST = pathlib.Path(sys.argv[1]) if len(sys.argv) > 1 else None
P0KEEP = lambda p: p in set(open(D / "queue-edge-200.txt").read().split()) if (D / "queue-edge-200.txt").exists() else False
REDIRECTS = set(open(D / "queue-redirects.txt").read().split())
rows = list(csv.DictReader(open(D / "upgrade-queue.csv")))
pages, skipped = {}, []
for r in rows:
    if r["problem"] not in ("position >20", "pos 8-20 with impressions (quick win)"): continue
    p = re.sub(r"^https?://[^/]+", "", r["url"]).rstrip("/")
    if p in CTR: skipped.append((p, "already in C1-C15")); continue
    if p in pages: skipped.append((p, "duplicate (www/apex)")); continue
    if p in REDIRECTS:
        skipped.append((p, "not a page (one-hop 301 to its hub, live-checked 21 Oct)")); continue
    if DIST and not p.startswith("/pages/keywords/") and not any((DIST / f).is_file() for f in (p.lstrip("/") + ".php", p.lstrip("/") + ".html", p.lstrip("/") + "/index.html")):
        skipped.append((p, "not a page (one-hop 301 to its hub)")); continue
    tm = re.match(r"^/pages/keywords/[a-z0-9-]+/([a-z0-9-]+)$", p)
    if tm and tm.group(1) not in LOCAL and p not in KEEP and not P0KEEP(p):
        skipped.append((p, "out-of-ring town: one-hop 301 to its keyword hub")); continue
    e = build(p)
    if not e: skipped.append((p, "no keyword/service data")); continue
    e["queue"] = r["problem"]
    pages[p] = e
# SEO 21 Oct fix 5: the restored keyword x town pages get the same block (links to 85).
for f in ("areas12.txt", "hub53.txt"):
    for u in open(D / f).read().split():
        p = re.sub(r"^https?://[^/]+", "", u).rstrip("/")
        if p in pages or p in CTR: continue
        e = build(p)
        if e: e["queue"] = "restored ranking page"; pages[p] = e
for e in pages.values(): e.pop("_words", None)
out = {"note": __doc__.strip().splitlines()[0], "pages": pages}
(ROOT / "website/data/gsc-upgrade-2026-10-21.json").write_text(json.dumps(out, indent=1, ensure_ascii=False) + "\n")
print(len(pages), "pages;", len(skipped), "skipped")
for s in skipped: print("  skip", *s)
