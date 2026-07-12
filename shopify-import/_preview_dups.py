#!/usr/bin/env python3
"""Preview clear duplicates without writing to Shopify."""
import json
from pathlib import Path

# Import detection from catalog_dedupe
from catalog_dedupe import fetch_all_products, find_duplicate_groups, body_text, all_skus

products = fetch_all_products(use_cache=True)
groups = find_duplicate_groups(products)
clear = [g for g in groups if g["clear_duplicate"]]
print(f"groups={len(groups)} clear={len(clear)}")
print("\n=== CLEAR ===")
for g in clear:
    print(f"\n[{g['confidence']}] score={g['max_score']} variants={g['likely_size_variants']}")
    for r in g["reasons"][:5]:
        print(f"  {r}")
    for m in g["members"]:
        print(
            f"  id={m['id']} body={len(body_text(m))} imgs={len(m.get('images') or [])} "
            f"sku={all_skus(m)} handle={m.get('handle')}"
        )
        print(f"    title={m.get('title')}")

print("\n=== REPORT-ONLY (first 25) ===")
for g in [x for x in groups if not x["clear_duplicate"]][:25]:
    print(f"\nscore={g['max_score']} size_var={g['likely_size_variants']}")
    for t in g["titles"]:
        print(f"  - {t}")
