#!/usr/bin/env python3
"""Batch 1 gate (KEYWORD-JOBTYPE-SEO-PLAN-2026-10-21 §7) on pages from render-batch1.mjs.
Usage: check-batch1.py <rendered-dir> <dist-dir> [--full]
"""
import difflib, glob, hashlib, html, json, os, re, sys, random
from collections import defaultdict
root, dist = sys.argv[1], sys.argv[2]
full = "--full" in sys.argv
fails = []
def fail(p, m):
    fails.append(f"{p}: {m}")
pages = sorted(glob.glob(os.path.join(root, "pages", "**", "*.html"), recursive=True))
photos = json.load(open("website/data/building-batch1-photos.json"))
src_fam = {}
for fam, rows in photos.items():
    for r in rows:
        src_fam[r["src"]] = fam
# byte-identical check across families
digest = defaultdict(set)
for src, fam in src_fam.items():
    f = os.path.join("website", src.lstrip("/"))
    if not os.path.exists(f): fail(src, "photo missing"); continue
    digest[hashlib.md5(open(f, "rb").read()).hexdigest()].add(fam)
for f in glob.glob("website/assets/images/services/*.jpg"):
    h = hashlib.md5(open(f, "rb").read()).hexdigest()
    if h in digest: digest[h].add("site:" + os.path.basename(f)[:-4])
for h, fams in digest.items():
    real = {x for x in fams if not x.startswith("site:")}
    sites = {x[5:] for x in fams if x.startswith("site:")}
    others = sites - {s for s in sites if any(s.startswith(r) or r == "gas" for r in real)}
    if len(real) > 1 or others: fail(h, f"image shared across families {sorted(fams)}")
from importlib import util
fam_of = {}
titles, metas = defaultdict(list), defaultdict(list)
prose_by_intent = defaultdict(dict)
def prose(main):
    m = re.sub(r'<ul class="b1-links">.*?</ul>', " ", main, flags=re.S)
    m = re.sub(r"<script.*?</script>|<style.*?</style>|<nav.*?</nav>", " ", m, flags=re.S)
    parts = re.findall(r"<(p|li|h[1-4]|summary)[^>]*>(.*?)</\1>", m, flags=re.S)
    return html.unescape(re.sub(r"<[^>]+>", " ", " ".join(t for _, t in parts)))
BANNED = re.compile(r"07471454785|info@|BAFE-backed|no scheme badge|no published fee|nothing here is a fixed fee", re.I)
stats = defaultdict(list)
for f in pages:
    rel = "/" + os.path.relpath(f, root)[:-5]
    h = open(f).read()
    kind = "keyword" if rel.startswith("/pages/keywords/") else "service"
    parts = rel.split("/")
    intent, town = (parts[3], parts[4]) if kind == "keyword" else (parts[2], parts[3])
    t = html.unescape(re.search(r"<title>(.*?)</title>", h).group(1))
    d = html.unescape(re.search(r'<meta name="description" content="([^"]*)"', h).group(1))
    titles[t].append(rel); metas[d].append(rel)
    if len(t) > 60: fail(rel, f"title {len(t)}")
    if "| Same/Next-Day" not in t: fail(rel, "title lacks Same/Next-Day")
    if not (140 <= len(d) <= 155): fail(rel, f"meta {len(d)}")
    if not d.endswith("Free quote, call 07517 806082."): fail(rel, "meta ending")
    if "noindex" in h.lower(): fail(rel, "noindex")
    if f'<link rel="canonical" href="https://icomplypropertyservices.co.uk{rel}">' not in h: fail(rel, "canonical")
    h1s = re.findall(r"<h1>(.*?)</h1>", h)
    if len(h1s) != 1 or not re.match(r".+ in .+", html.unescape(h1s[0])): fail(rel, f"h1 {h1s}")
    main = h[h.find("<main"):h.find("</main>")]
    after = main[main.find("</h1>"):]
    if not re.match(r'</h1>\s*<div class="b1-cta" data-b1="hero-cta"><a class="b1-call" href="tel:07517806082">Call 07517 806082</a><a class="b1-wa" href="https://wa.me/447517806082">', after): fail(rel, "hero CTA not directly under H1")
    for need in ("jobs we do in", "How a ", "Areas near ", "FAQs – ", "Get a "):
        if not re.search(r"<h2>[^<]*" + re.escape(need), main): fail(rel, f"h2 '{need}' missing")
    words = len(prose(main).split())
    stats[kind + "_words"].append(words)
    if words < 800: fail(rel, f"words {words}")
    links = {x for x in re.findall(r'href="(/[^"#]*)"', h) if not x.startswith("/assets/")}
    stats[kind + "_links"].append(len(links))
    if len(links) < 85: fail(rel, f"links {len(links)}")
    m = re.search(r'<script type="application/ld\+json">(.*?)</script>', h, re.S)
    try:
        g = json.loads(m.group(1))["@graph"]
        types = {x["@type"] for x in g}
        if not {"FAQPage", "Service"} <= types: fail(rel, "schema types")
        if any(x["@type"] in ("Review", "AggregateRating") for x in g): fail(rel, "review schema")
        if len([q for x in g if x["@type"] == "FAQPage" for q in x["mainEntity"]]) < 5: fail(rel, "faq < 5")
    except Exception as e:
        fail(rel, f"schema {e}")
    imgs = re.findall(r'<img src="([^"]+)" alt="([^"]*)"', main)
    stats["photos"].append(len(imgs))
    fams = {src_fam.get(s, "?") for s, _ in imgs}
    if len(fams) > 1 or "?" in fams: fail(rel, f"mixed/unknown photo families {fams}")
    for s, a in imgs:
        if not os.path.exists(os.path.join(dist, s.lstrip("/"))) and not os.path.exists(os.path.join("website", s.lstrip("/"))): fail(rel, f"missing {s}")
        if " – " not in a or not a.endswith(" in " + html.unescape(h1s[0]).split(" in ", 1)[1]): fail(rel, f"alt '{a}'")
    if BANNED.search(h): fail(rel, f"banned {BANNED.search(h).group(0)}")
    for mm in re.finditer(r"Gas Safe registered(?! engineers)", h): fail(rel, "gas phrase")
    body_nohead = re.sub(r"<header.*?</header>", "", h, flags=re.S)
    for mm in re.finditer(r"£\s?\d[\d,]*", body_nohead):
        ctx = body_nohead[max(0, mm.start() - 12):mm.end()]
        if "£160" not in ctx: fail(rel, f"price {mm.group(0)}")
    for mm in re.finditer(r"tel:([0-9+]+)", h):
        if mm.group(1) not in ("07517806082", "+447517806082"): fail(rel, f"tel {mm.group(1)}")
    for mm in re.finditer(r"[\w.+-]+@[\w-]+(?:\.[\w-]+)+", h):
        if mm.group(0) != "icomplypropertyservices@gmail.com": fail(rel, f"email {mm.group(0)}")
    dw = None
    for _el in re.findall(r"<(?:p|li|h[1-4]|summary)[^>]*>(.*?)</", main, flags=re.S):
        dw = dw or re.search(r"\b(\w+) \1\b", html.unescape(re.sub(r"<[^>]+>", " ", _el)), re.I)
    if dw: fail(rel, f"doubled word {dw.group(0)}")
    prose_by_intent[(kind, intent)][town] = re.sub(r"\s+", " ", prose(main).replace(town_name := html.unescape(h1s[0]).split(" in ", 1)[1], " ")).split()
for t, v in titles.items():
    if len(v) > 1: fail(v[0], f"duplicate title x{len(v)}")
for t, v in metas.items():
    if len(v) > 1: fail(v[0], f"duplicate meta x{len(v)}")
worst = (0, None)
per = {}
rnd = random.Random(7)
for key, towns in prose_by_intent.items():
    ts = sorted(towns)
    pairs = [(a, b) for i, a in enumerate(ts) for b in ts[i + 1:]]
    if not full: pairs = rnd.sample(pairs, min(int(os.environ.get('B1_PAIRS', '60')), len(pairs)))
    w = (0, None)
    for a, b in pairs:
        sm = difflib.SequenceMatcher(None, towns[a], towns[b], autojunk=False)
        r = sm.ratio()
        if r > w[0]: w = (r, (a, b))
    per[key] = w
    if w[0] > 0.70: fail(f"{key}", f"worst-pair {w[0]:.3f} {w[1]}")
    if w[0] > worst[0]: worst = (w[0], (key, w[1]))
pb = per.get(("keyword", "plasterboard-installation"))
for k in ("service_words", "keyword_words", "service_links", "keyword_links", "photos"):
    if stats[k]: print(f"{k}: min {min(stats[k])} median {sorted(stats[k])[len(stats[k])//2]}")
print(f"pages {len(pages)}; worst-pair {worst[0]:.3f} {worst[1]}; plasterboard-installation {pb[0]:.3f} {pb[1]}; mode {'full' if full else 'sample'}")
top = sorted(per.items(), key=lambda x: -x[1][0])[:5]
print("top intents:", ", ".join(f"{k[1]} {v[0]:.3f}" for k, v in top))
for x in (fails if os.environ.get("B1_ALL") else fails[:40]): print("FAIL", x)
print(f"check-batch1: {'OK' if not fails else f'{len(fails)} FAIL'}")
sys.exit(1 if fails else 0)
