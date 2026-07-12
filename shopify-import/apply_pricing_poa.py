#!/usr/bin/env python3
"""Pricing / POA specialist for iComply Supplys Shopify store.

Loads all products, validates prices against UK trade guide bands,
applies POA mode or priced tags, writes catalog/pricing_audit.json,
and updates products via Admin API.
"""

from __future__ import annotations

import json
import re
import sys
import time
from pathlib import Path

from shopify_client import api, get_token

ROOT = Path(__file__).resolve().parent
AUDIT_PATH = ROOT / "catalog" / "pricing_audit.json"

POA_HTML = (
    "<p><strong>Price on Application (POA)</strong> — "
    "contact us for live trade pricing and stock.</p>"
)

# Sensible UK trade guide bands by product category keyword.
# Order matters: more specific keywords first.
# (keywords, min_inclusive, max_inclusive, band_name)
BANDS: list[tuple[tuple[str, ...], float, float, str]] = [
    # service packages (title/type usually includes "package" or "service")
    (
        (
            "service package",
            "compliance package",
            "systems package",
            "fire alarm service",
            "emergency lighting package",
            "gas safety package",
            "electrical compliance",
            "nurse call",
            "door entry package",
            "access control package",
            "intruder alarm package",
            "cctv systems package",
            "aov-air",
            "aov air",
            "intercoms package",
        ),
        50.0,
        600.0,
        "service_package",
    ),
    # panels
    (("control panel", "fire alarm control panel", " panel", "panel "), 100.0, 3000.0, "panel"),
    # cable accessories / glands / boxes (before fire cable — "fire-cable-gland")
    (
        ("cable accessory", "cable gland", "junction box", "gland", "gland-pack"),
        2.0,
        150.0,
        "cable_accessory",
    ),
    # detector bases / mounting (before generic detector)
    (
        (
            "detector base",
            "standard base",
            "diode base",
            "isolator base",
            "sounder beacon base",
            "mounting accessory",
            "mounting",
            " base",
        ),
        3.0,
        80.0,
        "device",
    ),
    # detectors
    (
        (
            "smoke detector",
            "heat detector",
            "multi-sensor",
            "multisensor",
            "multi sensor",
            "detector",
        ),
        8.0,
        100.0,
        "detector",
    ),
    # batteries
    (("fire system battery", "battery", "batteries"), 10.0, 100.0, "battery"),
    # cable drums / fire cable (not accessories)
    (
        (
            "fire resistant cable",
            "fire cable",
            "cable drum",
            "fp200",
            "enhanced cable",
            "1-5mm",
            "2-5mm",
            "100m",
            "500m",
        ),
        50.0,
        800.0,
        "cable",
    ),
    # devices / call points / sounders / interfaces
    (
        (
            "manual call point",
            "call point",
            "sounder beacon",
            "sounder",
            "beacon / vad",
            "beacon",
            "interface module",
            "interface",
            "module",
        ),
        5.0,
        250.0,
        "device",
    ),
    # power
    (("power supply", "power accessory", "psu"), 40.0, 800.0, "power"),
    # emergency lighting
    (
        (
            "emergency luminaire",
            "exit sign",
            "emergency lighting accessory",
            "emergency lighting",
        ),
        15.0,
        250.0,
        "emergency_lighting",
    ),
]

DEFAULT_BAND = (1.0, 5000.0, "default")


def classify_band(product: dict) -> tuple[float, float, str]:
    """Return (min, max, band_name) for a product based on type/title/tags."""
    blob = " ".join(
        [
            str(product.get("product_type") or ""),
            str(product.get("title") or ""),
            str(product.get("handle") or ""),
            str(product.get("tags") or ""),
            str(product.get("vendor") or ""),
        ]
    ).lower()

    # Prefer more specific bands first (order of BANDS matters)
    for keywords, lo, hi, name in BANDS:
        for kw in keywords:
            if kw in blob:
                return lo, hi, name
    return DEFAULT_BAND


def parse_price(value) -> float | None:
    if value is None or value == "":
        return None
    try:
        return float(value)
    except (TypeError, ValueError):
        return None


def get_primary_variant(product: dict) -> dict | None:
    variants = product.get("variants") or []
    return variants[0] if variants else None


def tag_list(product: dict) -> list[str]:
    tags = product.get("tags") or ""
    if isinstance(tags, list):
        return [t.strip() for t in tags if str(t).strip()]
    return [t.strip() for t in str(tags).split(",") if t.strip()]


def tags_to_string(tags: list[str]) -> str:
    # Preserve order, unique case-insensitive
    seen = set()
    out = []
    for t in tags:
        key = t.lower()
        if key not in seen:
            seen.add(key)
            out.append(t)
    return ", ".join(out)


def has_poa_html(body: str | None) -> bool:
    if not body:
        return False
    low = body.lower()
    return "price on application" in low or "price on application (poa)" in low


def load_guide_prices() -> dict[str, float]:
    """Local catalog guide prices by handle (for restore after mis-POA)."""
    guides: dict[str, float] = {}
    for path in [
        ROOT / "catalog" / "all_products.json",
        *sorted((ROOT / "catalog").glob("products_*.json")),
    ]:
        if not path.is_file():
            continue
        try:
            data = json.loads(path.read_text(encoding="utf-8"))
        except (OSError, json.JSONDecodeError):
            continue
        if not isinstance(data, list):
            continue
        for item in data:
            h = item.get("handle")
            p = parse_price(item.get("price"))
            if h and p is not None and p >= 1.0:
                guides[h] = p
    # Also recover old_price from previous audit if present
    if AUDIT_PATH.is_file():
        try:
            prev = json.loads(AUDIT_PATH.read_text(encoding="utf-8"))
            for row in prev:
                h = row.get("handle")
                p = parse_price(row.get("old_price"))
                if h and p is not None and p >= 1.0 and h not in guides:
                    guides[h] = p
        except (OSError, json.JSONDecodeError):
            pass
    return guides


def decide_action(product: dict, guide_prices: dict[str, float] | None = None) -> dict:
    """Decide keep | poa | adjust for a product."""
    guide_prices = guide_prices or {}
    variant = get_primary_variant(product)
    shop_price = parse_price(variant.get("price") if variant else None)
    lo, hi, band = classify_band(product)
    handle = product.get("handle") or ""
    pid = product.get("id")
    tags = tag_list(product)
    tags_lower = {t.lower() for t in tags}
    guide = guide_prices.get(handle)

    reason_parts = [f"band={band} ({lo:.0f}-{hi:.0f})"]

    # If Shopify price is zero/missing but local guide price is in-band, restore it
    if (shop_price is None or shop_price < 1.0) and guide is not None and lo <= guide <= hi:
        return {
            "handle": handle,
            "id": pid,
            "old_price": f"{shop_price:.2f}" if shop_price is not None else None,
            "new_price": f"{guide:.2f}",
            "action": "adjust",
            "reason": f"restore guide price {guide:.2f} (shop was zero/POA); {'; '.join(reason_parts)}",
            "band": band,
            "tags": tags,
            "body_html": product.get("body_html") or "",
            "variant_id": variant.get("id") if variant else None,
            "restore_price": True,
            "strip_poa_html": True,
        }

    old_price = shop_price

    # Missing / zero / absurdly low
    if old_price is None:
        return {
            "handle": handle,
            "id": pid,
            "old_price": None,
            "new_price": "0.00",
            "action": "poa",
            "reason": "missing price",
            "band": band,
            "tags": tags,
            "body_html": product.get("body_html") or "",
            "variant_id": variant.get("id") if variant else None,
        }

    if old_price < 1.0:
        return {
            "handle": handle,
            "id": pid,
            "old_price": f"{old_price:.2f}",
            "new_price": "0.00",
            "action": "poa",
            "reason": f"price {old_price:.2f} absurdly low (<1) or zero; {'; '.join(reason_parts)}",
            "band": band,
            "tags": tags,
            "body_html": product.get("body_html") or "",
            "variant_id": variant.get("id") if variant else None,
        }

    # Absurdly high for type → POA
    if old_price > hi:
        return {
            "handle": handle,
            "id": pid,
            "old_price": f"{old_price:.2f}",
            "new_price": "0.00",
            "action": "poa",
            "reason": f"price {old_price:.2f} above band max {hi:.0f}; {'; '.join(reason_parts)}",
            "band": band,
            "tags": tags,
            "body_html": product.get("body_html") or "",
            "variant_id": variant.get("id") if variant else None,
        }

    # Below band minimum (but >= 1) — treat as invalid / POA
    if old_price < lo:
        return {
            "handle": handle,
            "id": pid,
            "old_price": f"{old_price:.2f}",
            "new_price": "0.00",
            "action": "poa",
            "reason": f"price {old_price:.2f} below band min {lo:.0f}; {'; '.join(reason_parts)}",
            "band": band,
            "tags": tags,
            "body_html": product.get("body_html") or "",
            "variant_id": variant.get("id") if variant else None,
        }

    # Within sensible band — keep / tag hygiene
    needs_tag_fix = (
        "priced" not in tags_lower
        or "poa" in tags_lower
        or "price-on-application" in tags_lower
    )
    has_erroneous_poa_html = has_poa_html(product.get("body_html")) and old_price >= 1.0

    if needs_tag_fix or has_erroneous_poa_html:
        return {
            "handle": handle,
            "id": pid,
            "old_price": f"{old_price:.2f}",
            "new_price": f"{old_price:.2f}",
            "action": "adjust",
            "reason": f"valid price in band; fix tags/html; {'; '.join(reason_parts)}",
            "band": band,
            "tags": tags,
            "body_html": product.get("body_html") or "",
            "variant_id": variant.get("id") if variant else None,
            "fix_tags": True,
            "strip_poa_html": has_erroneous_poa_html,
        }

    return {
        "handle": handle,
        "id": pid,
        "old_price": f"{old_price:.2f}",
        "new_price": f"{old_price:.2f}",
        "action": "keep",
        "reason": f"valid UK trade guide price; {'; '.join(reason_parts)}",
        "band": band,
        "tags": tags,
        "body_html": product.get("body_html") or "",
        "variant_id": variant.get("id") if variant else None,
    }


def strip_poa_html(body: str) -> str:
    if not body:
        return body
    # Remove leading POA paragraph if present
    cleaned = re.sub(
        r"<p>\s*<strong>\s*Price on Application \(POA\)\s*</strong>[^<]*</p>\s*",
        "",
        body,
        count=1,
        flags=re.IGNORECASE,
    )
    return cleaned


def api_put_with_backoff(path: str, body: dict, max_attempts: int = 8) -> dict:
    """PUT with extra backoff on 429 (shopify_client already retries some)."""
    for attempt in range(max_attempts):
        try:
            result = api("PUT", path, body)
            time.sleep(0.45)  # extra courtesy beyond client 0.35s (~1.2 rps)
            return result
        except RuntimeError as e:
            msg = str(e)
            if "429" in msg or "502" in msg or "503" in msg:
                wait = 2.0 * (attempt + 1)
                print(f"    rate-limit backoff {wait:.1f}s…", flush=True)
                time.sleep(wait)
                continue
            raise
    raise RuntimeError(f"Failed after {max_attempts} attempts: PUT {path}")


def apply_decision(decision: dict) -> dict:
    """Apply API updates for poa or adjust. keep = no write."""
    action = decision["action"]
    if action == "keep":
        return {"status": "skipped", "id": decision["id"]}

    pid = decision["id"]
    tags = list(decision.get("tags") or [])
    body = decision.get("body_html") or ""
    variant_id = decision.get("variant_id")

    if action == "poa":
        # tags: add poa + price-on-application, remove priced
        new_tags = [t for t in tags if t.lower() not in ("priced", "poa", "price-on-application")]
        new_tags.extend(["poa", "price-on-application"])
        if not has_poa_html(body):
            body = POA_HTML + (body or "")

        product_payload: dict = {
            "id": pid,
            "tags": tags_to_string(new_tags),
            "body_html": body,
        }
        api_put_with_backoff(f"/products/{pid}.json", {"product": product_payload})

        if variant_id:
            variant_payload = {
                "id": variant_id,
                "price": "0.00",
                "inventory_policy": "continue",
                "compare_at_price": None,
            }
            api_put_with_backoff(
                f"/variants/{variant_id}.json", {"variant": variant_payload}
            )

        return {"status": "updated_poa", "id": pid}

    if action == "adjust":
        # Valid price: ensure priced, remove poa tags; optionally restore price
        new_tags = [
            t for t in tags if t.lower() not in ("poa", "price-on-application", "priced")
        ]
        new_tags.append("priced")
        product_payload: dict = {
            "id": pid,
            "tags": tags_to_string(new_tags),
        }
        if decision.get("strip_poa_html") or decision.get("restore_price"):
            product_payload["body_html"] = strip_poa_html(body)
        api_put_with_backoff(f"/products/{pid}.json", {"product": product_payload})

        if decision.get("restore_price") and variant_id:
            variant_payload = {
                "id": variant_id,
                "price": decision["new_price"],
                "compare_at_price": None,
            }
            api_put_with_backoff(
                f"/variants/{variant_id}.json", {"variant": variant_payload}
            )

        return {"status": "updated_adjust", "id": pid}

    return {"status": "unknown", "id": pid}


def load_all_products() -> list[dict]:
    """Paginate products via since_id."""
    products: list[dict] = []
    since_id = 0
    page = 0
    while True:
        path = f"/products.json?limit=250&since_id={since_id}"
        data = api("GET", path)
        batch = data.get("products") or []
        if not batch:
            break
        products.extend(batch)
        since_id = batch[-1]["id"]
        page += 1
        print(f"  fetched page {page}: +{len(batch)} (total {len(products)})", flush=True)
        if len(batch) < 250:
            break
    return products


def main() -> int:
    print("Authenticating…", flush=True)
    get_token()
    print("Loading local guide prices…", flush=True)
    guide_prices = load_guide_prices()
    print(f"Guide prices available for {len(guide_prices)} handles.", flush=True)

    print("Loading all products…", flush=True)
    products = load_all_products()
    print(f"Loaded {len(products)} products.", flush=True)

    decisions: list[dict] = []
    for p in products:
        decisions.append(decide_action(p, guide_prices))

    counts = {"keep": 0, "poa": 0, "adjust": 0}
    for d in decisions:
        counts[d["action"]] = counts.get(d["action"], 0) + 1

    print(
        f"Decisions: keep={counts.get('keep',0)} poa={counts.get('poa',0)} "
        f"adjust={counts.get('adjust',0)}",
        flush=True,
    )

    # Audit file (slim fields as required)
    audit = [
        {
            "handle": d["handle"],
            "id": d["id"],
            "old_price": d["old_price"],
            "new_price": d["new_price"],
            "action": d["action"],
            "reason": d["reason"],
        }
        for d in decisions
    ]
    AUDIT_PATH.parent.mkdir(parents=True, exist_ok=True)
    AUDIT_PATH.write_text(json.dumps(audit, indent=2), encoding="utf-8")
    print(f"Wrote audit: {AUDIT_PATH}", flush=True)

    # Apply updates
    applied = {"poa": 0, "adjust": 0, "errors": 0}
    to_apply = [d for d in decisions if d["action"] in ("poa", "adjust")]
    print(f"Applying {len(to_apply)} updates…", flush=True)

    for i, d in enumerate(to_apply, 1):
        try:
            result = apply_decision(d)
            if d["action"] == "poa":
                applied["poa"] += 1
            else:
                applied["adjust"] += 1
            print(
                f"  [{i}/{len(to_apply)}] {d['action'].upper()} {d['handle']} "
                f"({d['old_price']} -> {d['new_price']}) {result.get('status')}",
                flush=True,
            )
        except Exception as e:
            applied["errors"] += 1
            print(f"  [{i}/{len(to_apply)}] ERROR {d['handle']}: {e}", flush=True)

    print("\n========== SUMMARY ==========", flush=True)
    print(f"Total products:     {len(products)}", flush=True)
    print(f"Keep (no change):   {counts.get('keep', 0)}", flush=True)
    print(f"POA applied:        {applied['poa']}", flush=True)
    print(f"Adjust (tags/html): {applied['adjust']}", flush=True)
    print(f"Errors:             {applied['errors']}", flush=True)
    print(f"Audit file:         {AUDIT_PATH}", flush=True)
    print("=============================", flush=True)

    # Also dump counts by band for visibility
    band_counts: dict[str, dict[str, int]] = {}
    for d in decisions:
        b = d.get("band", "?")
        band_counts.setdefault(b, {"keep": 0, "poa": 0, "adjust": 0})
        band_counts[b][d["action"]] = band_counts[b].get(d["action"], 0) + 1
    print("\nBy band:", flush=True)
    for b, c in sorted(band_counts.items()):
        print(f"  {b}: {c}", flush=True)

    return 0 if applied["errors"] == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
