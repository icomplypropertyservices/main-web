#!/usr/bin/env python3
"""Inventory specialist: untrack stock (or continue selling) + shipping flags.

For trade catalogue:
  - inventory_management = null (don't track) AND inventory_policy = continue
  - requires_shipping = false for Service Package / service-package tag
  - requires_shipping = true for physical goods
Writes inventory_result.json with counts.
"""

from __future__ import annotations

import json
import time
from pathlib import Path

from shopify_client import api, get_token

ROOT = Path(__file__).resolve().parent
OUT = ROOT / "inventory_result.json"


def is_service_package(product: dict) -> bool:
    ptype = (product.get("product_type") or "").strip().lower()
    if ptype == "service package":
        return True
    tags = [t.strip().lower() for t in (product.get("tags") or "").split(",") if t.strip()]
    return "service-package" in tags


def fetch_all_products() -> list[dict]:
    products: list[dict] = []
    since = 0
    while True:
        data = api(
            "GET",
            f"/products.json?limit=250&fields=id,title,handle,tags,product_type,variants&since_id={since}",
        )
        batch = data.get("products") or []
        if not batch:
            break
        for p in batch:
            since = max(since, int(p["id"]))
            products.append(p)
        if len(batch) < 250:
            break
    return products


def variant_ok(variant: dict, want_shipping: bool) -> bool:
    inv_mgmt = variant.get("inventory_management")
    inv_policy = (variant.get("inventory_policy") or "").lower()
    shipping = bool(variant.get("requires_shipping"))
    return (
        inv_mgmt is None
        and inv_policy == "continue"
        and shipping is want_shipping
    )


def main():
    print("Auth…")
    get_token(force=True)
    print("Fetching products…")
    products = fetch_all_products()
    print(f"Loaded {len(products)} products")

    counts = {
        "products_seen": 0,
        "variants_seen": 0,
        "variants_updated": 0,
        "variants_already_ok": 0,
        "variants_failed": 0,
        "shipping_physical_set": 0,
        "shipping_service_set": 0,
        "inventory_untracked": 0,
        "inventory_policy_continue": 0,
        "service_products": 0,
        "physical_products": 0,
    }
    items = []
    errors = []

    for i, p in enumerate(products, 1):
        counts["products_seen"] += 1
        service = is_service_package(p)
        if service:
            counts["service_products"] += 1
        else:
            counts["physical_products"] += 1
        want_shipping = not service
        variants = p.get("variants") or []
        to_fix = []

        for v in variants:
            counts["variants_seen"] += 1
            if variant_ok(v, want_shipping):
                counts["variants_already_ok"] += 1
                counts["inventory_untracked"] += 1
                counts["inventory_policy_continue"] += 1
                if want_shipping:
                    counts["shipping_physical_set"] += 1
                else:
                    counts["shipping_service_set"] += 1
                continue
            to_fix.append(v)

        for v in to_fix:
            vid = v["id"]
            payload = {
                "variant": {
                    "id": vid,
                    "inventory_management": None,
                    "inventory_policy": "continue",
                    "requires_shipping": want_shipping,
                }
            }
            try:
                resp = api("PUT", f"/variants/{vid}.json", payload)
                updated = resp.get("variant") or {}
                counts["variants_updated"] += 1
                # Count final intended state (API may omit null fields)
                counts["inventory_untracked"] += 1
                counts["inventory_policy_continue"] += 1
                if want_shipping:
                    counts["shipping_physical_set"] += 1
                else:
                    counts["shipping_service_set"] += 1
                items.append(
                    {
                        "product_id": p["id"],
                        "handle": p["handle"],
                        "variant_id": vid,
                        "sku": v.get("sku"),
                        "service_package": service,
                        "requires_shipping": want_shipping,
                        "inventory_management": updated.get("inventory_management"),
                        "inventory_policy": updated.get("inventory_policy", "continue"),
                        "action": "updated",
                    }
                )
            except Exception as e:
                counts["variants_failed"] += 1
                err = {
                    "product_id": p["id"],
                    "handle": p["handle"],
                    "variant_id": vid,
                    "error": str(e)[:500],
                }
                errors.append(err)
                items.append({**err, "action": "failed"})

        if i % 25 == 0 or i == len(products):
            print(
                f"  progress {i}/{len(products)} "
                f"updated={counts['variants_updated']} "
                f"ok={counts['variants_already_ok']} "
                f"fail={counts['variants_failed']}"
            )
            # checkpoint
            OUT.write_text(
                json.dumps(
                    {"summary": counts, "errors": errors, "items": items},
                    indent=2,
                ),
                encoding="utf-8",
            )

    result = {"summary": counts, "errors": errors, "items": items}
    OUT.write_text(json.dumps(result, indent=2), encoding="utf-8")
    print(json.dumps(counts, indent=2))
    print(f"Wrote {OUT}")
    if errors:
        print(f"ERRORS: {len(errors)}")
        for e in errors[:5]:
            print(e)


if __name__ == "__main__":
    main()
