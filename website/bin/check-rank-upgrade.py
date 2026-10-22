#!/usr/bin/env python3
"""GSC rankings 22 Oct gate on render-rank-pages.mjs output. Every upgraded page: 200, index,follow,
self-canonical, title = data title (<=60), meta = data meta (140-155), call 07517 806082 +
WhatsApp row directly under the H1, FAQPage + Service JSON-LD, >=800 prose words, >=80 unique
internal links, contact details only 07517 806082 / icomplypropertyservices@gmail.com, no banned
phrases, worst-pair similarity <=0.70 per family. Every rendered hub carries its inbound links.
Usage: check-rank-upgrade.py /tmp/rank-render"""
import collections, difflib, html as H, itertools, json, os, re, sys
sys.path.insert(0, os.path.dirname(__file__))
import importlib.util
spec = importlib.util.spec_from_file_location("g", os.path.join(os.path.dirname(__file__), "check-gsc-upgrade.py"))
src = open(spec.origin).read().split("\nd = sys.argv")[0]; G = {}; exec(src, G); Prose = G["Prose"]
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
DOC = json.load(open(os.path.join(ROOT, "website/data/gsc-upgrade-2026-10-22.json")))
SITE = "https://icomplypropertyservices.co.uk"
BAN = re.compile(r"07471|info@|BAFE-backed|fixed pricing is used|fixed written quote|we do not dump|Gas Safe registered(?! engineers)", re.I)
d = sys.argv[1] if len(sys.argv) > 1 else "/tmp/rank-render"
rows = [l.rstrip("\n").split("\t") for l in open(os.path.join(d, "index.tsv")) if l.strip()]
fails, fam, report = [], collections.defaultdict(dict), []
def hrefs(h):
    s = set()
    for x in re.findall(r'href="([^"]+)"', h):
        x = H.unescape(x)
        if x.startswith(SITE): x = x[len(SITE):] or "/"
        if not x.startswith("/") or x.startswith("//"): continue
        x = x.split("#")[0].split("?")[0]
        if not x or x.startswith("/assets/") or re.search(r"\.(css|js|png|jpe?g|webp|svg|ico|xml|json|webmanifest)$", x, re.I): continue
        s.add(x.rstrip("/") or "/")
    return s
for p, kind, status, f in rows:
    if kind == "hub":
        if status != "200": continue
        h = open(os.path.join(d, f), errors="ignore").read(); have = hrefs(h)
        miss = [t for t, _ in DOC["inbound"][p] if t != p and t not in have]
        if miss: fails.append(f"hub {p}: missing inbound {miss[:3]}")
        continue
    e = DOC["pages"][p]
    if status != "200": fails.append(f"{p}: status {status}"); continue
    h = open(os.path.join(d, f), errors="ignore").read()
    t = H.unescape((re.search(r"<title>([\s\S]*?)</title>", h) or [None, ""])[1]).strip()
    md = H.unescape((re.search(r'<meta name="description" content="([^"]*)"', h) or [None, ""])[1])
    if t != e["title"] or len(t) > 60: fails.append(f"{p}: title {t!r}")
    if md != e["meta"] or not 140 <= len(md) <= 155: fails.append(f"{p}: meta {len(md)} {md[:50]!r}")
    rb = re.search(r'<meta name="robots" content="([^"]*)"', h)
    if not rb or "noindex" in rb.group(1): fails.append(f"{p}: robots")
    cn = re.search(r'<link rel="canonical" href="([^"]+)"', h)
    if not cn or cn.group(1).rstrip("/") != SITE + p: fails.append(f"{p}: canonical {cn.group(1) if cn else None}")
    if not re.search(r'</h1>\s*(<div class="hero-cta" data-cta="hero"><a class="call" href="tel:07517806082">)', h): fails.append(f"{p}: no CTA under H1")
    if "wa.me/447517806082" not in h: fails.append(f"{p}: no WhatsApp")
    lds = " ".join(re.findall(r'<script type="application/ld\+json">([\s\S]*?)</script>', h))
    for ty in ("FAQPage", "Service"):
        if f'"{ty}"' not in lds: fails.append(f"{p}: no {ty} schema")
    vis = re.sub(r"<script[\s\S]*?</script>", "", h)
    for ph in set(re.findall(r"0\d{3}\s?\d{3}\s?\d{4}|07\d{3}\s?\d{6}", vis)):
        if re.sub(r"\s", "", ph) not in ("07517806082", "0800111999"): fails.append(f"{p}: phone {ph}")
    for em in set(re.findall(r"[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[a-z]{2,}", h)):
        if em.lower() != "icomplypropertyservices@gmail.com" and not em.endswith((".png", ".jpg", ".webp")): fails.append(f"{p}: email {em}")
    b = BAN.search(vis)
    if b: fails.append(f"{p}: banned {b.group(0)!r}")
    n = len(hrefs(h) - {p})
    if n < 80: fails.append(f"{p}: {n} internal links")
    pr = Prose(); pr.feed(h); w = " ".join(pr.parts).split()
    if len(w) < 800: fails.append(f"{p}: {len(w)} words")
    seg = p.split("/")[3] if p.startswith("/pages/keywords/") else p.split("/")[2]
    fam[seg][p] = w
    report.append((p, t, len(t), md, len(md), len(w), n))
worst_all = (0, "", "")
for name, pages in sorted(fam.items()):
    worst = (0.0, "", "")
    for a, b in itertools.combinations(sorted(pages), 2):
        sm = difflib.SequenceMatcher(None, pages[a], pages[b], autojunk=False)
        if sm.real_quick_ratio() <= worst[0] or sm.quick_ratio() <= worst[0]: continue
        r = sm.ratio()
        if r > worst[0]: worst = (r, a, b)
    if worst[0] > 0.70: fails.append(f"{name}: {worst[1]} vs {worst[2]} similarity {worst[0]:.3f}")
    if worst[0] > worst_all[0]: worst_all = worst
with open(os.path.join(d, "report.tsv"), "w") as o:
    o.write("path\ttitle\ttitle_len\tmeta\tmeta_len\twords\tlinks\n")
    for r in report: o.write("\t".join(map(str, r)) + "\n")
for f in fails[:80]: print("FAIL", f)
print(f"check-rank-upgrade: {'FAIL' if fails else 'OK'} pages={len(report)} hubs={sum(1 for r in rows if r[1]=='hub' and r[2]=='200')} min_words={min((r[5] for r in report), default=0)} min_links={min((r[6] for r in report), default=0)} worst_pair={worst_all[0]:.3f} ({worst_all[1]} vs {worst_all[2]}) fails={len(fails)}")
sys.exit(1 if fails else 0)
