#!/usr/bin/env python3
"""Semrush/Ahrefs basics on exported HTML (21 Oct): title <=60, meta 140-155,
exactly one non-empty <h1>, every JSON-LD block parses and FAQPage/Service
objects carry their required fields, internal <img src> files exist in dist.
Usage: check-seo-basics.py dist [--report out.tsv] [--strict]
Without --strict it reports counts and exits 0 (legacy debt); with --strict any
JSON-LD error or missing image fails."""
import json, os, re, sys, collections
dist = sys.argv[1] if len(sys.argv) > 1 else "dist"
strict = "--strict" in sys.argv
rep = sys.argv[sys.argv.index("--report") + 1] if "--report" in sys.argv else None
C = collections.Counter(); rows = []
def check_ld(o, errs):
    if isinstance(o, list):
        for x in o: check_ld(x, errs); return
    if not isinstance(o, dict): return
    if "@graph" in o: check_ld(o["@graph"], errs)
    t = o.get("@type"); t = t if isinstance(t, list) else [t]
    if "FAQPage" in t:
        me = o.get("mainEntity")
        if not isinstance(me, list) or not me: errs.append("FAQPage without mainEntity")
        else:
            for q in me:
                if not (isinstance(q, dict) and q.get("@type") == "Question" and str(q.get("name", "")).strip()
                        and isinstance(q.get("acceptedAnswer"), dict) and str(q["acceptedAnswer"].get("text", "")).strip()):
                    errs.append("FAQ Question missing name/acceptedAnswer.text"); break
    if "Service" in t:
        if not str(o.get("name", "") or o.get("serviceType", "")).strip(): errs.append("Service without name/serviceType")
        if not o.get("provider"): errs.append("Service without provider")
for root, _, files in os.walk(dist):
    if "/assets" in root: continue
    for f in files:
        if not f.endswith((".php", ".html")): continue
        fp = os.path.join(root, f); rel = "/" + os.path.relpath(fp, dist)
        html = open(fp, errors="ignore").read()
        if "<html" not in html[:2000].lower(): continue
        if re.search(r'<meta name="robots" content="[^"]*noindex', html) and not rel.startswith("/404"): C["noindex"] += 1; rows.append((rel, "noindex"))
        if re.search(r'http-equiv="refresh"', html): continue
        C["pages"] += 1
        t = re.search(r"<title>([^<]*)</title>", html)
        tl = len(re.sub(r"&amp;", "&", t.group(1)).strip()) if t else 0
        if tl == 0: C["title_missing"] += 1; rows.append((rel, "title missing"))
        elif tl > 60: C["title_long"] += 1; rows.append((rel, f"title {tl}"))
        m = re.search(r'<meta\s+name="description"\s+content="([^"]*)"', html)
        ml = len(re.sub(r"&amp;|&#0?39;|&quot;", "x", m.group(1))) if m else 0
        if not m or ml == 0: C["meta_missing"] += 1; rows.append((rel, "meta missing"))
        elif ml > 155: C["meta_long"] += 1; rows.append((rel, f"meta {ml}"))
        elif ml < 140: C["meta_short"] += 1; rows.append((rel, f"meta {ml}"))
        h1s = [re.sub(r"<[^>]+>", "", x).strip() for x in re.findall(r"<h1\b[^>]*>([\s\S]*?)</h1>", html)]
        if not any(h1s): C["h1_missing"] += 1; rows.append((rel, "h1 missing/empty"))
        elif len(h1s) > 1: C["h1_multi"] += 1
        for blk in re.findall(r'<script[^>]+application/ld\+json[^>]*>([\s\S]*?)</script>', html):
            errs = []
            try: check_ld(json.loads(blk), errs)
            except Exception as e: errs.append(f"JSON parse: {str(e)[:60]}")
            for e in errs: C["jsonld_invalid"] += 1; rows.append((rel, "jsonld " + e)); break
        for src in re.findall(r'<img\b[^>]*\ssrc="(/[^"?#]+)', html):
            if not os.path.exists(os.path.join(dist, src.lstrip("/"))): C["img_missing"] += 1; rows.append((rel, "img " + src))
        if os.path.getsize(fp) > 2_000_000: C["over_2mb"] += 1; rows.append((rel, f"size {os.path.getsize(fp)}"))
if rep: open(rep, "w").write("\n".join("\t".join(r) for r in rows) + "\n")
print("check-seo-basics:", dict(C))
sys.exit(1 if strict and (C["jsonld_invalid"] or C["img_missing"] or C["noindex"]) else 0)
