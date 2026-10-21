#!/usr/bin/env python3
"""Build website/data/legacy-product-map.json for netlify/edge-functions/legacy-products.js.
Inputs: prod.txt / coll.txt (live shop.icomplypropertyservices.co.uk sitemap handles, pulled 21 Oct 2026),
keywords.json and manufacturers.json. Refresh prod.txt/coll.txt from the shop sitemap when the catalogue changes."""
import json, pathlib
D = pathlib.Path(__file__).resolve().parent
ROOT = D.parents[1]
prod = sorted(set(open(D / "prod.txt").read().split()))
coll = sorted(set(open(D / "coll.txt").read().split()) - {"frontpage", "hardware-and-tools-example-products"})
kw = sorted(json.load(open(ROOT / "website/data/keywords.json")).keys())
mfr = sorted(json.load(open(ROOT / "website/data/manufacturers.json"))["catalog"].keys())
alias = {"ctec": "c-tec", "c-tec": "c-tec", "bell-systems": "bell", "advanced-electronics": "advanced",
         "advanced-mx": "advanced", "advanced-mxp": "advanced", "system-sensor": "system-sensor",
         "morley": "morley-ias", "gent": "fire-alarm-control-panels", "notifier": "fire-alarm-control-panels"}
mfr_alias = {"advanced": "advanced-electronics", "ctec": "c-tec", "morley-ias": "morley", "eaton": "eaton-fire"}
out = {"shop": "https://shop.icomplypropertyservices.co.uk", "products": prod, "collections": coll,
       "brandAlias": alias, "keywords": kw, "manufacturers": mfr, "manufacturerAlias": mfr_alias}
(ROOT / "website/data/legacy-product-map.json").write_text(json.dumps(out, separators=(",", ":")) + "\n")
print(len(prod), "products,", len(coll), "collections,", len(kw), "keywords,", len(mfr), "manufacturers")
