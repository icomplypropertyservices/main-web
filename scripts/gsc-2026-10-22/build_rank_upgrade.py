#!/usr/bin/env python3
"""GSC rankings upgrade 22 Oct: 250 rows in rankings/2026-10-22/upgrade-queue.csv. Writes
website/data/gsc-upgrade-2026-10-22.json (pages + inbound hub links), read by
netlify/lib/money-ctr.js and website/includes/money-ctr.php; 22 Oct entries win over C1-C15 and
21 Oct. Reuses the 21 Oct generator (scripts/gsc-2026-10-21/build_upgrade_pages.py) for the body,
then: keyword+town-first titles <=60 and metas 140-155 for every page, hand-written copy for the 4
quick wins and 3 deep upgrades (copy.py), <=5-impression pages now included, and an 'inbound' map
so each upgraded page is linked from its keyword hub, service hub and town area page.
Never touches robots, canonical, H1 or the sitemap."""
import csv, json, pathlib, re, sys
D = pathlib.Path(__file__).resolve().parent
ROOT = D.parents[1]
src = (ROOT / "scripts/gsc-2026-10-21/build_upgrade_pages.py").read_text()
src = src.split("\nimport sys\nDIST")[0].replace('pathlib.Path(__file__).resolve().parent', 'pathlib.Path(%r)' % str(ROOT / "scripts/gsc-2026-10-21"))
g = {"__name__": "gsc21"}; exec(compile(src, "build_upgrade_pages.py", "exec"), g)
sys.path.insert(0, str(D)); from rank_copy import PRIORITY, DEEP  # noqa
BRIEF = "GSC-2026-10-22"
REDIRECTS = set(open(D / "queue-redirects.txt").read().split())
EDGE200 = set(open(D / "queue-edge-200.txt").read().split()) if (D / "queue-edge-200.txt").exists() else set()
CTR = json.loads((ROOT / "website/data/money-ctr-c1-c15.json").read_text())["pages"]; LOCAL = g["LOCAL"]; KEEP = g["KEEP"]; KW = g["KW"]; SVC = g["SVC"]
BAN = re.compile(r"07471|info@|BAFE-backed|Gas Safe registered(?! engineers)|fixed pricing is used|fixed written quote", re.I)

def check(e, p):
    t, m = e["title"], e["meta"]
    assert len(t) <= 60, (p, len(t), t)
    assert 140 <= len(m) <= 155, (p, len(m), m)
    blob = json.dumps(e, ensure_ascii=False)
    assert not BAN.search(blob), (p, BAN.search(blob).group(0))
    assert "£" not in blob.replace("from £160", "").replace("From £160", "").replace("From £350", "").replace("from £350", "").replace("£350", ""), p

rows = list(csv.DictReader(open(D / "upgrade-queue.csv")))
pages, inbound, skipped = {}, {}, []
for r in rows:
    p = re.sub(r"^https?://[^/]+", "", r["url"]).rstrip("/")
    if r["problem"].startswith("FAIL"):
        skipped.append((p, "www/apex FAIL row: the page itself is upgraded via its apex row; www->apex is the host-level 301")); 
        if p not in DEEP: continue
    if p in pages: skipped.append((p, "duplicate (www/apex)")); continue
    if p in REDIRECTS: skipped.append((p, "not a page (one-hop 301 to its hub)")); continue
    if p.endswith(".php"): skipped.append((p, "legacy .php URL, 301s to the pretty URL")); continue
    tm = re.match(r"^/pages/keywords/[a-z0-9-]+/([a-z0-9-]+)$", p)
    if tm and tm.group(1) not in LOCAL and p not in KEEP and p not in EDGE200:
        skipped.append((p, "out-of-ring town: one-hop 301 to its keyword hub")); continue
    if p in CTR and p not in DEEP:
        skipped.append((p, "C1-C15 money page already authored (inbound hub links still added)"))
        e = None
    else:
        e = g["build"](p)
        if not e: skipped.append((p, "no keyword/service data")); continue
        e.pop("_words", None)
        e["brief"] = BRIEF; e["queue"] = r["problem"]; e["position"] = r["avg_position"]
        if p in PRIORITY:
            x = PRIORITY[p]; e["title"], e["meta"] = x["title"], x["meta"]
            e["paras"] = x["paras"] + e["paras"]; e["faqs"] = x["faqs"] + e["faqs"]
        elif p in DEEP:
            x = DEEP[p]
            e.update(title=x["title"], meta=x["meta"], h2=x["h2"], paras=x["paras"], faqs=x["faqs"], kind="deep")
            if p in CTR:  # keep the C1-C15 related links too
                e["links"] = CTR[p].get("links", []) + e["links"]; e["hubs"] = list(dict.fromkeys(CTR[p].get("hubs", []) + e["hubs"]))
        tw = re.match(r"^/pages/(?:keywords/[a-z0-9-]+|[a-z0-9-]+)/([a-z0-9-]+)$", p)
        if tw and not p.startswith("/pages/services/") and not p.startswith("/pages/jobs/"): e["town"] = g["tname"](tw.group(1))
        if e.get("town") and e["town"].lower() not in e["title"].lower() and p not in PRIORITY and p not in DEEP:
            nm = e["h2"].split(" in ")[0]; fr = " from £160" if "eicr" in p else (" from £350" if "fire-risk-assessment" in p else "")
            for c in (f"{nm} {e['town']}{fr} | iComply", f"{nm} {e['town']}{fr}", f"{nm} {e['town']} | iComply", f"{nm} {e['town']}"):
                if len(c) <= 60: e["title"] = c; break
        check(e, p)
        pages[p] = e
    # inbound: keyword hub, service hub, town area page link to the upgraded page
    m = re.match(r"^/pages/keywords/([a-z0-9-]+)(?:/([a-z0-9-]+))?$", p)
    label = (pages.get(p) or CTR.get(p) or {}).get("title", p).split(" | ")[0]
    srcs = []
    if m:
        k = KW.get(m.group(1)) or {}
        if m.group(2): srcs.append(f"/pages/keywords/{m.group(1)}")
        if k.get("service"): srcs.append(f"/pages/services/{k['service']}")
        if m.group(2) and m.group(2) in LOCAL: srcs.append(f"/pages/areas/{m.group(2)}")
    else:
        parts = p.split("/")
        if len(parts) > 3: srcs.append("/".join(parts[:3]))
        if parts[2] == "jobs": srcs += ["/pages/jobs", "/pages/services/barriers"] if "barrier" in p else ["/pages/jobs"]
        if parts[2] == "services": srcs.append("/pages/services")
        if parts[2] == "keywords": srcs.append("/pages/keywords")
        if len(parts) > 3 and parts[3] in LOCAL: srcs.append(f"/pages/areas/{parts[3]}")
    for s in srcs:
        if s != p and [p, label] not in inbound.setdefault(s, []): inbound[s].append([p, label])
out = {"note": __doc__.strip().splitlines()[0], "brief": BRIEF, "pages": pages,
       "inbound": {k: v for k, v in sorted(inbound.items())}}
(ROOT / "website/data/gsc-upgrade-2026-10-22.json").write_text(json.dumps(out, indent=1, ensure_ascii=False) + "\n")
print(len(pages), "pages;", sum(map(len, inbound.values())), "inbound links on", len(inbound), "hubs;", len(skipped), "skipped")
(D / "skipped.tsv").write_text("".join(f"{a}\t{b}\n" for a, b in skipped))
