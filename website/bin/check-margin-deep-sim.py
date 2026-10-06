#!/usr/bin/env python3
"""Margin DEEP similarity gate (difflib, full body: intro + body + FAQ, word sequences).

Fails when any pair of hubs in a pack reaches 0.80, or when a hub reaches 0.70 against an
existing (non-DEEP) keyword hub on the site that it does not supersede. Prints the worst pairs.
Usage: python3 bin/check-margin-deep-sim.py [--pack=fencing]
"""
from __future__ import annotations

import difflib
import glob
import json
import os
import re
import subprocess
import sys
from itertools import combinations

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def seq(k: dict) -> list[str]:
    faq = " ".join(f"{q} {a}" for q, a in (k.get("faq") or []))
    return re.findall(r"[a-z0-9'’-]+", f"{k.get('intro', '')} {k.get('body', '')} {faq}".lower())


def worst(pairs, seqs_a, seqs_b):
    best = (0.0, "")
    for a, b in pairs:
        sm = difflib.SequenceMatcher(None, seqs_a[a], seqs_b[b])
        if sm.real_quick_ratio() <= best[0] or sm.quick_ratio() <= best[0]:
            continue
        r = sm.ratio()
        if r > best[0]:
            best = (r, f"{a} ~ {b}")
    return best


def main() -> int:
    only = next((a.split("=", 1)[1] for a in sys.argv[1:] if a.startswith("--pack=")), "")
    docs = []
    for path in sorted(glob.glob(os.path.join(ROOT, "data", "margin-deep", "*.json"))):
        doc = json.load(open(path, encoding="utf-8"))
        if isinstance(doc, dict) and doc.get("pack") and doc.get("hubs") and (not only or doc["pack"] == only):
            docs.append(doc)
    if not docs:
        print("FAIL no margin DEEP packs")
        return 1
    deep = {h["slug"] for d in docs for h in d["hubs"]}
    php = ("putenv('SITE_URL=https://icomplypropertyservices.co.uk'); require 'config.php';"
           "$out=[]; foreach (getMajorKeywords() as $s => $k) { if (!icomplyMarginDeepIsP0($s)) {"
           "$out[$s] = ['intro' => (string)($k['intro'] ?? ''), 'body' => (string)($k['body'] ?? ''), 'faq' => array_values((array)($k['faq'] ?? []))]; } }"
           "echo json_encode($out);")
    existing = json.loads(subprocess.run(["php", "-r", php], cwd=ROOT, capture_output=True, text=True, check=True).stdout)
    ex_seqs = {s: seq(k) for s, k in existing.items() if s not in deep}
    failed = False
    for doc in docs:
        kw = doc["keywords"]
        seqs = {s: seq(k) for s, k in kw.items()}
        slugs = [h["slug"] for h in doc["hubs"]]
        w = worst(combinations(slugs, 2), seqs, seqs)
        # Existing hubs: difflib on each hub's 5 nearest by 3-gram Jaccard (keeps CI fast).
        grams = lambda q: {" ".join(q[i:i + 3]) for i in range(len(q) - 2)}
        ex_grams = {s: grams(q) for s, q in ex_seqs.items()}
        cand = []
        for a in slugs:
            ga = grams(seqs[a])
            near = sorted(ex_grams, key=lambda b: -len(ga & ex_grams[b]) / (len(ga | ex_grams[b]) or 1))[:5]
            cand += [(a, b) for b in near]
        tw = worst(cand, seqs, ex_seqs)
        status_in = "ok" if w[0] < 0.80 else "FAIL"
        status_tw = "ok" if tw[0] <= 0.70 else "FAIL"
        failed |= status_in != "ok" or status_tw != "ok"
        print(f"{doc['pack']}: worst in-pack {w[0]:.3f} ({w[1]}) [{status_in}, target <0.80, aim <=0.70]; "
              f"worst vs existing keyword hubs {tw[0]:.3f} ({tw[1] or 'none'}) [{status_tw}, <=0.70]")
    print("FAIL" if failed else "PASS")
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
