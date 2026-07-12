#!/usr/bin/env python3
import json
import re
from collections import Counter, defaultdict
from difflib import SequenceMatcher
from pathlib import Path

products = json.loads(Path("catalog/_live_products.json").read_text(encoding="utf-8"))
print("products", len(products), Counter(p.get("status") for p in products))


def norm(t):
    t = t.lower().strip()
    t = re.sub(r"[^\w\s\-./+]", " ", t)
    return re.sub(r"\s+", " ", t).strip()


def sim(a, b):
    return SequenceMatcher(None, norm(a), norm(b)).ratio()


by_nt = defaultdict(list)
for p in products:
    by_nt[norm(p.get("title") or "")].append(p)
print("\nEXACT same normalized title:")
for t, pl in sorted(by_nt.items(), key=lambda x: -len(x[1])):
    if len(pl) < 2:
        continue
    print(f"  n={len(pl)} | {t}")
    for p in pl:
        skus = [v.get("sku") for v in p.get("variants") or []]
        blen = len(p.get("body_html") or "")
        print(
            f"    id={p['id']} status={p['status']} handle={p['handle']} "
            f"sku={skus} body={blen}"
        )

by_sku = defaultdict(list)
for p in products:
    for v in p.get("variants") or []:
        s = (v.get("sku") or "").strip().upper()
        if s:
            by_sku[s].append(p)
print("\nEXACT same SKU:")
for s, pl in sorted(by_sku.items()):
    uniq = {p["id"]: p for p in pl}
    if len(uniq) < 2:
        continue
    print(f"  sku={s} n={len(uniq)}")
    for p in uniq.values():
        print(f"    id={p['id']} status={p['status']} title={p['title']}")

print("\nNEAR titles (sim>=0.88) same vendor:")
by_v = defaultdict(list)
for p in products:
    by_v[(p.get("vendor") or "").lower()].append(p)
count = 0
for v, pl in by_v.items():
    for i in range(len(pl)):
        for j in range(i + 1, len(pl)):
            s = sim(pl[i]["title"], pl[j]["title"])
            if s >= 0.88 and norm(pl[i]["title"]) != norm(pl[j]["title"]):
                count += 1
                if count <= 60:
                    a, b = pl[i], pl[j]
                    print(
                        f"  {s:.3f} | [{a['id']}] {a['title']}  <==>  "
                        f"[{b['id']}] {b['title']}"
                    )
print("total near pairs", count)

print("\nHANDLE base dups:")
by_h = defaultdict(list)
for p in products:
    h = re.sub(r"-(copy|dup|duplicate|\d+)$", "", (p.get("handle") or "").lower())
    by_h[h].append(p)
for h, pl in sorted(by_h.items()):
    uniq = {p["id"]: p for p in pl}
    if len(uniq) < 2:
        continue
    # only if titles similar
    titles = [p["title"] for p in uniq.values()]
    if len(uniq) == 2:
        vals = list(uniq.values())
        if sim(vals[0]["title"], vals[1]["title"]) < 0.85:
            continue
    print(f"  base={h}")
    for p in uniq.values():
        print(f"    id={p['id']} handle={p['handle']} title={p['title']}")
