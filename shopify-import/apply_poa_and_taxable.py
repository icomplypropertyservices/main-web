#!/usr/bin/env python3
"""Ensure all variants taxable; flag weak prices as POA; tag products."""

from __future__ import annotations

import json
import re
import time
from pathlib import Path

from shopify_client import api, get_token

ROOT = Path(__file__).resolve().parent
OUT = ROOT / "catalog" / "pricing_poa_applied.json"

POA_HTML = (
    '<p><strong>Price on Application (POA)</strong> — Contact us for live trade '
    'pricing and stock. Tel: <a href="tel:07517806082">07517 806082</a> · '
    'Email: <a href="mailto:icomplypropertyservices@gmail.com">icomplypropertyservices@gmail.com</a>.</p>'
)

# Reasonable GBP bands by keyword in product_type/title/tags
BANDS = [
    (r"service package|service-package", 50, 800),
    (r"control panel|panel", 120, 3500),
    (r"smoke|heat|multi-sensor|detector", 8, 120),
    (r"call point|mcp", 8, 80),
    (r"sounder|beacon|vad", 10, 120),
    (r"base|mounting", 2, 60),
    (r"module|interface|io |i/o", 30, 250),
    (r"battery|psu|power", 10, 250),
    (r"cable|gland|junction", 5, 900),
    (r"emergency|exit|bulkhead|twin", 10, 150),
]


def price_ok(price: float, blob: str) -> bool:
    if price <= 0:
        return False
    for pat, lo, hi in BANDS:
        if re.search(pat, blob, re.I):
            return lo <= price <= hi
    # default band
    return 1 <= price <= 5000


def iter_products():
    since = 0
    while True:
        data = api(
            "GET",
            f"/products.json?limit=50&fields=id,title,handle,tags,product_type,body_html,variants&since_id={since}",
        )
        batch = data.get("products") or []
        if not batch:
            break
        for p in batch:
            since = max(since, int(p["id"]))
            yield p
        if len(batch) < 50:
            break


def main():
    get_token(force=True)
    results = []
    kept = poa = taxable_fixed = 0

    for p in iter_products():
        tags = [t.strip() for t in (p.get("tags") or "").split(",") if t.strip()]
        tagset = {t.lower() for t in tags}
        blob = f"{p.get('title','')} {p.get('product_type','')} {p.get('tags','')}"
        variants = p.get("variants") or []
        if not variants:
            continue
        v = variants[0]
        try:
            price = float(v.get("price") or 0)
        except ValueError:
            price = 0.0

        need_poa = not price_ok(price, blob)
        body = p.get("body_html") or ""
        new_tags = list(tags)
        actions = []

        if need_poa:
            if "poa" not in tagset:
                new_tags.append("poa")
            if "price-on-application" not in tagset:
                new_tags.append("price-on-application")
            new_tags = [t for t in new_tags if t.lower() != "priced"]
            if "Price on Application" not in body:
                body = POA_HTML + "\n" + body
            # update variant to 0.00 and taxable
            api(
                "PUT",
                f"/variants/{v['id']}.json",
                {
                    "variant": {
                        "id": v["id"],
                        "price": "0.00",
                        "taxable": True,
                        "inventory_policy": "continue",
                    }
                },
            )
            api(
                "PUT",
                f"/products/{p['id']}.json",
                {
                    "product": {
                        "id": p["id"],
                        "tags": ", ".join(new_tags),
                        "body_html": body,
                    }
                },
            )
            poa += 1
            actions.append("poa")
            results.append(
                {
                    "id": p["id"],
                    "handle": p["handle"],
                    "old_price": price,
                    "new_price": 0.0,
                    "action": "poa",
                    "reason": "out_of_band_or_zero",
                }
            )
        else:
            if "priced" not in tagset:
                new_tags.append("priced")
            new_tags = [t for t in new_tags if t.lower() not in ("poa", "price-on-application")]
            # ensure taxable
            if v.get("taxable") is False:
                api(
                    "PUT",
                    f"/variants/{v['id']}.json",
                    {"variant": {"id": v["id"], "taxable": True}},
                )
                taxable_fixed += 1
                actions.append("taxable")
            if set(t.lower() for t in new_tags) != tagset or "priced" not in tagset:
                api(
                    "PUT",
                    f"/products/{p['id']}.json",
                    {"product": {"id": p["id"], "tags": ", ".join(new_tags)}},
                )
                actions.append("tags")
            kept += 1
            results.append(
                {
                    "id": p["id"],
                    "handle": p["handle"],
                    "old_price": price,
                    "new_price": price,
                    "action": "keep",
                    "reason": "in_band",
                }
            )

        time.sleep(0.2)

    summary = {
        "kept_priced": kept,
        "set_poa": poa,
        "taxable_fixed": taxable_fixed,
        "total": len(results),
    }
    OUT.write_text(json.dumps({"summary": summary, "items": results}, indent=2), encoding="utf-8")
    print(json.dumps(summary, indent=2))
    print(f"Wrote {OUT}")


if __name__ == "__main__":
    main()
