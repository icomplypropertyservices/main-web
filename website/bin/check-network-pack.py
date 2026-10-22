#!/usr/bin/env python3
"""Gate for the networking, data cabling and mesh Wi-Fi pack (lock 2026-10-20).

Town pages (keyword x GM-60, job x GM-60, service x 269), rendered with the
edge renderer by render-network-pack.mjs:
  >= 800 prose words (check-town-words.py method), no doubled words, index,
  follow, self-canonical, FAQPage + Service schema with >= 5 questions, >= 3
  photos that exist, >= 85 unique internal links on P0 and service pages
  (>= 80 otherwise), every pack link inside its town set, site banned
  phrases, gas wording, no pound prices, NAP (gmail + 07517 806082 only),
  no Sky/BT logo or partner claims, worst-pair similarity <= 0.70 within each
  keyword / job / service family.
Hubs (dist): service >= 1,200 words, keyword/job >= 800, same head rules.
Sitemap (dist): every hub and town page listed.
usage: python3 website/bin/check-network-pack.py dist [--full]
  default similarity: 20 towns per family (all pairs); --full: all 60 / 269.
"""
import collections, difflib, itertools, json, os, pathlib, re, subprocess, sys
from multiprocessing import Pool

HERE = pathlib.Path(__file__).resolve().parent
SITE = HERE.parent
REPO = SITE.parent
sys.path.insert(0, str(HERE))
spec = __import__("importlib.util").util.spec_from_file_location("ctw", HERE / "check-town-words.py")
ctw = __import__("importlib.util").util.module_from_spec(spec)
spec.loader.exec_module(ctw)
BANNED = re.compile([l for l in (HERE / "check-barriers-copy.py").read_text().splitlines() if l.startswith("SITE_BANNED")][0]
                    .split('re.compile(r"', 1)[1].rsplit('", re.I)', 1)[0], re.I)
EXTRA = re.compile(r"Sky (?:approved|partner)|BT (?:approved|partner)|approved (?:Sky|BT) installer|sky-logo|bt-logo|info@|iComply is (?:not )?Gas Safe", re.I)
PHONE = re.compile(r"\b0\d{3,4}[\s-]?\d{3}[\s-]?\d{3,4}\b")
EMAIL = re.compile(r"[\w.+-]+@[\w-]+(?:\.[\w-]+)+")
BASE = "https://icomplypropertyservices.co.uk"

dist = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 and not sys.argv[1].startswith("-") else REPO / "dist")
FULL = "--full" in sys.argv
OUT = "/tmp/network-pack"
pack = json.loads((SITE / "data/network-pack.json").read_text())
GM, DUAL = set(pack["gm60"]), set(pack["dual269"])


def prose(html):
    p = ctw.Prose(); p.feed(html); return " ".join(p.parts)


def links(html):
    out = set()
    for h in re.findall(r'<a\b[^>]*\bhref="([^"]+)"', html):
        h = h.replace("&amp;", "&")
        if re.match(r"^(#|mailto:|tel:|javascript:)", h, re.I):
            continue
        if h.startswith("http") and "icomplypropertyservices.co.uk" not in h:
            continue
        h = re.sub(r"^https?://[^/]+", "", h).split("#")[0].split("?")[0]
        out.add((h.rstrip("/") or "/").removesuffix(".php"))
    return out


def dist_has(p):
    rel = p.lstrip("/")
    return any((dist / c).is_file() for c in (rel + ".php", rel + ".html", rel + "/index.html", rel + "/index.php", rel)) or p == "/"


LIVE = set(pack["live_cabling"])


def link_ok(p):
    m = re.match(r"^/pages/keywords/([a-z0-9-]+)/([a-z0-9-]+)$", p)
    if m:
        if m[1] in pack["keywords"]:
            return m[2] in GM
        return m[1] in LIVE and m[2] in DUAL
    m = re.match(r"^/pages/jobs/([a-z0-9-]+)/([a-z0-9-]+)$", p)
    if m:
        return m[1] in pack["jobs"] and m[2] in GM
    m = re.match(r"^/pages/(networking|structured-cabling|wifi)/([a-z0-9-]+)$", p)
    if m:
        return m[2] in DUAL
    return dist_has(p)


def head_checks(where, html, canonical, fails, min_faq=5):
    if not re.search(r'<meta name="robots" content="index, follow', html):
        fails.append(f"{where}: robots not index, follow")
    if "noindex" in html.lower():
        fails.append(f"{where}: noindex")
    c = re.findall(r'<link rel="canonical" href="([^"]+)"', html)
    if c != [canonical]:
        fails.append(f"{where}: canonical {c} != {canonical}")
    blob = " ".join(re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S))
    if '"FAQPage"' not in blob or '"Service"' not in blob:
        fails.append(f"{where}: missing FAQPage/Service schema")
    if blob.count('"Question"') < min_faq:
        fails.append(f"{where}: {blob.count(chr(34) + 'Question' + chr(34))} FAQ questions < {min_faq}")
    imgs = re.findall(r'<img\b[^>]*\ssrc="(/assets/images/[^"]+)"', html.split("<main", 1)[-1])
    if len(set(imgs)) < 3:
        fails.append(f"{where}: {len(set(imgs))} photos < 3")
    for i in set(imgs) | set(re.findall(r'og:image" content="https://icomplypropertyservices\.co\.uk(/assets/[^"]+)"', html)):
        if not (SITE / i.lstrip("/")).is_file() and not (dist / i.lstrip("/")).is_file():
            fails.append(f"{where}: missing image {i}")
    main = html.split("<main", 1)[-1].split("</main>", 1)[0]  # site chrome (nav prices such as the £561 pack) is out of scope
    text = re.sub(r"\s+", " ", re.sub(r"<[^>]+>", " ", re.sub(r"<script.*?</script>|<style.*?</style>", " ", main, flags=re.S)))
    meta = " ".join(re.findall(r'<(?:meta|img)[^>]+(?:content|alt)="([^"]*)"', html))
    for rx, label in ((BANNED, "banned"), (EXTRA, "claim/NAP")):
        m = rx.search(text + " " + meta)
        if m:
            fails.append(f"{where}: {label} '{m.group(0)}'")
    if "£" in text:
        fails.append(f"{where}: pound price")
    for e in set(EMAIL.findall(text)):
        if e != "icomplypropertyservices@gmail.com":
            fails.append(f"{where}: email {e}")
    for ph in set(PHONE.findall(text)):
        if re.sub(r"\D", "", ph) != "07517806082":
            fails.append(f"{where}: phone {ph}")
    for m in re.finditer(r"Gas Safe registered", text):
        if not text[:m.start()].endswith("by ") or not text[m.end():].startswith(" engineer"):
            fails.append(f"{where}: gas wording")
    if re.search(r"\bSky\b|\bBT\b", text) and not re.search(r"works with your (?:Sky or BT|Sky|BT) hub", text, re.I):
        fails.append(f"{where}: Sky/BT without 'works with your ... hub'")
    return text


def sim(args):
    (ta, a), (tb, b) = args
    return difflib.SequenceMatcher(None, a, b, autojunk=False).ratio(), ta, tb


def main():
    r = subprocess.run(["node", str(HERE / "render-network-pack.mjs"), OUT], capture_output=True, text=True, cwd=REPO)
    print(r.stdout.strip() or r.stderr.strip())
    if r.returncode:
        print(r.stderr); return 1
    idx = json.load(open(f"{OUT}/index.json"))
    rows = idx["rows"]
    fails = []
    if idx["viaMatrix"] != len(rows):
        fails.append(f"renderTownPage parity {idx['viaMatrix']}/{len(rows)}")
    for o in idx["outside"]:
        if o["ok"]:
            fails.append(f"{o['path']}: renders outside its town set")
    groups = collections.defaultdict(list)
    stats = collections.Counter()
    minw, minl = {}, {}
    bad_links = collections.Counter()
    for row in rows:
        html = open(f"{OUT}/{row['file']}", encoding="utf-8").read()
        where = row["path"]
        if row["canonical"] != BASE + row["path"]:
            fails.append(f"{where}: canonical")
        head_checks(where, html, BASE + row["path"], fails)
        words = prose(html).split()
        d = ctw.doubled(" ".join(words))
        if d:
            fails.append(f"{where}: doubled {d[:2]}")
        if len(words) < 800:
            fails.append(f"{where}: {len(words)} words < 800")
        L = links(html)
        need = 85 if (row["p0"] or row["kind"] == "service") else 80
        if len(L) < need:
            fails.append(f"{where}: {len(L)} unique internal links < {need}")
        for p in L:
            if not link_ok(p):
                bad_links[p] += 1
        fam = (row["kind"], row["slug"])
        minw[fam] = min(minw.get(fam, 10 ** 6), len(words))
        minl[fam] = min(minl.get(fam, 10 ** 6), len(L))
        stats[row["kind"]] += 1
        groups[fam].append((row["town"], words))
    for p, n in bad_links.most_common(20):
        fails.append(f"broken link {p} on {n} page(s)")
    # Similarity
    sample = sorted(GM)[:0]
    TOWNS20 = ["manchester", "stockport", "bolton", "wigan", "oldham", "rochdale", "salford", "bury", "trafford", "tameside",
               "altrincham", "sale", "stretford", "denton", "hyde", "leigh", "middleton", "heywood", "prestwich", "cheadle"]
    jobs = []
    for fam, items in groups.items():
        use = items if FULL else [x for x in items if x[0] in TOWNS20] or items[:20]
        jobs.append((fam, list(itertools.combinations(use, 2))))
    worst_all = (0, None)
    report = []
    with Pool(os.cpu_count()) as pool:
        for fam, pairs in jobs:
            res = pool.map(sim, pairs, chunksize=64)
            w = max(res, key=lambda x: x[0])
            report.append((fam, len(pairs), w))
            if w[0] > worst_all[0]:
                worst_all = (w[0], (fam, w[1], w[2]))
            if w[0] > 0.70:
                fails.append(f"{fam[0]} {fam[1]}: worst-pair {w[0]:.3f} ({w[1]}, {w[2]}) > 0.70")
    # Hubs
    hubs = json.loads((SITE / "data/network-pack-hubs.json").read_text())
    hubw = {}
    for key, hub in hubs.items():
        f = dist / (hub["url"].lstrip("/") + ".php")
        if not f.is_file():
            f = dist / (hub["url"].lstrip("/") + ".html")
        if not f.is_file():
            fails.append(f"hub {hub['url']}: not exported"); continue
        html = f.read_text(errors="ignore")
        head_checks(hub["url"], html, BASE + hub["url"], fails)
        w = len(prose("<body><main" + html.split("<main", 1)[-1].split("</main>", 1)[0] + "</main>").split())
        hubw[key] = w
        need = 1200 if key.startswith("service/") else 800
        if w < need:
            fails.append(f"hub {hub['url']}: {w} words < {need}")
        for p in links(html):
            if p.startswith("/pages/") and not link_ok(p):
                bad_links[p] += 1
    # Sitemap
    locs = set()
    for sm in dist.glob("sitemap*.xml"):
        locs |= set(re.findall(r"<loc>https://icomplypropertyservices\.co\.uk([^<]*)</loc>", sm.read_text(errors="ignore")))
    miss = [r["path"] for r in rows if r["path"] not in locs] + [h["url"] for h in hubs.values() if h["url"] not in locs]
    if miss:
        fails.append(f"sitemap missing {len(miss)} pack URLs, e.g. {miss[:3]}")
    # Reverse links
    for p, want in (("pages/areas/bolton", "/pages/networking/bolton"), ("pages/areas/accrington", "/pages/wifi/accrington"),
                    ("pages/services/cctv", "/pages/services/wifi"), ("pages/services/door-entry", "/pages/services/networking"),
                    ("pages/services/intercoms", "/pages/services/structured-cabling"),
                    ("pages/keywords/data-cabling-installation", "/pages/services/structured-cabling")):
        f = dist / (p + ".php")
        if not f.is_file() or want not in links(f.read_text(errors="ignore")):
            fails.append(f"/{p}: no link to {want}")
    areas = [f for f in (dist / "pages/areas").glob("*.php")]
    no_rev = [f.stem for f in areas if f.stem in DUAL and f"/pages/services/networking" not in f.read_text(errors="ignore")]
    if no_rev:
        fails.append(f"{len(no_rev)} area hubs without the network links, e.g. {no_rev[:3]}")
    for fam, n, w in sorted(report):
        print(f"{'ok' if w[0] <= 0.70 else 'FAIL'} {fam[0]} {fam[1]}: pairs {n}, min words {minw[fam]}, min links {minl[fam]}, worst-pair {w[0]:.3f} ({w[1]}, {w[2]})")
    print(f"hubs: {len(hubw)} exported; min words service {min((v for k, v in hubw.items() if k.startswith('service/')), default=0)}, "
          f"keyword {min((v for k, v in hubw.items() if k.startswith('keyword/')), default=0)}, job {min((v for k, v in hubw.items() if k.startswith('job/')), default=0)}")
    print(f"town pages: {dict(stats)}; worst-pair overall {worst_all[0]:.3f} {worst_all[1]}; mode {'full' if FULL else 'sample-20'}")
    for x in fails[:40]:
        print("FAIL", x)
    print(f"check-network-pack: {len(fails)} failure(s)" if fails else f"check-network-pack: OK ({len(rows)} town pages, {len(hubw)} hubs)")
    return 1 if fails else 0


if __name__ == "__main__":
    sys.exit(main())
