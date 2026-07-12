#!/usr/bin/env python3
"""Audit + fix product images so each Shopify product has exactly one
branded trade card: alt text = product title, CDN src present.

Does NOT scrape manufacturer photo libraries (copyright). Uses distinct
branded trade cards per product so images cannot be mixed up.
"""

from __future__ import annotations

import base64
import json
import time
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

from shopify_client import api, get_token

ROOT = Path(__file__).resolve().parent
IMG_DIR = ROOT / "images" / "products_fixed"
LOG = ROOT / "image_fix_log.json"
AUDIT_FINAL = ROOT / "image_audit_final.json"

NAVY = (10, 37, 64)       # #0a2540
ORANGE = (255, 107, 0)    # #ff6b00
WHITE = (255, 255, 255)
LIGHT = (248, 250, 252)

# Distinct category colours so products don't look identical
CAT_COLOURS = {
    "fire-alarm-control-panels": (185, 28, 28),       # red
    "smoke-detectors": (37, 99, 235),                 # blue
    "heat-detectors": (234, 88, 12),                  # orange-red
    "multi-sensor-detectors": (124, 58, 237),         # purple
    "manual-call-points": (220, 38, 38),              # bright red
    "sounders-beacons": (202, 138, 4),                # amber
    "bases-mounting": (100, 116, 139),                # slate
    "interfaces-modules": (13, 148, 136),             # teal
    "batteries-power": (22, 163, 74),                 # green
    "cables-accessories": (161, 98, 7),               # brown-gold
    "emergency-lighting-products": (2, 132, 199),     # sky
    "service-packages": (255, 107, 0),                # brand orange
}

CAT_LABELS = {
    "fire-alarm-control-panels": "CONTROL PANEL",
    "smoke-detectors": "SMOKE DETECTOR",
    "heat-detectors": "HEAT DETECTOR",
    "multi-sensor-detectors": "MULTI-SENSOR",
    "manual-call-points": "CALL POINT",
    "sounders-beacons": "SOUNDER / BEACON",
    "bases-mounting": "BASE / MOUNTING",
    "interfaces-modules": "INTERFACE MODULE",
    "batteries-power": "BATTERY / PSU",
    "cables-accessories": "CABLE / ACCESSORY",
    "emergency-lighting-products": "EMERGENCY LIGHTING",
    "service-packages": "SERVICE PACKAGE",
}


def load_font(size: int) -> ImageFont.ImageFont:
    for path in (
        r"C:\Windows\Fonts\segoeuib.ttf",
        r"C:\Windows\Fonts\segoeui.ttf",
        r"C:\Windows\Fonts\arialbd.ttf",
        r"C:\Windows\Fonts\arial.ttf",
    ):
        if Path(path).exists():
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def wrap(draw, text, font, max_w):
    words = text.split()
    lines, cur = [], ""
    for w in words:
        test = f"{cur} {w}".strip()
        bbox = draw.textbbox((0, 0), test, font=font)
        if bbox[2] - bbox[0] > max_w and cur:
            lines.append(cur)
            cur = w
        else:
            cur = test
    if cur:
        lines.append(cur)
    return lines


def infer_collection(product: dict) -> str:
    tags = (product.get("tags") or "").lower()
    ptype = (product.get("product_type") or "").lower()
    handle = (product.get("handle") or "").lower()
    title = (product.get("title") or "").lower()
    blob = f"{tags} {ptype} {handle} {title}"
    mapping = [
        ("service-package", "service-packages"),
        ("service package", "service-packages"),
        ("control panel", "fire-alarm-control-panels"),
        ("panel", "fire-alarm-control-panels"),
        ("multi-sensor", "multi-sensor-detectors"),
        ("multisensor", "multi-sensor-detectors"),
        ("smoke", "smoke-detectors"),
        ("heat", "heat-detectors"),
        ("call point", "manual-call-points"),
        ("mcp", "manual-call-points"),
        ("sounder", "sounders-beacons"),
        ("beacon", "sounders-beacons"),
        ("base", "bases-mounting"),
        ("module", "interfaces-modules"),
        ("interface", "interfaces-modules"),
        ("battery", "batteries-power"),
        ("psu", "batteries-power"),
        ("power supply", "batteries-power"),
        ("cable", "cables-accessories"),
        ("gland", "cables-accessories"),
        ("junction", "cables-accessories"),
        ("emergency", "emergency-lighting-products"),
        ("exit", "emergency-lighting-products"),
        ("bulkhead", "emergency-lighting-products"),
    ]
    for needle, coll in mapping:
        if needle in blob:
            return coll
    return "cables-accessories"


def make_image(title: str, sku: str, collection: str, handle: str, out: Path) -> Path:
    w, h = 1200, 1200
    accent = CAT_COLOURS.get(collection, ORANGE)
    cat = CAT_LABELS.get(collection, "PRODUCT")

    img = Image.new("RGB", (w, h), LIGHT)
    draw = ImageDraw.Draw(img)

    # Left accent bar
    draw.rectangle([0, 0, 28, h], fill=accent)
    # Top navy
    draw.rectangle([28, 0, w, 200], fill=NAVY)
    draw.rectangle([28, 200, w, 214], fill=accent)
    # Bottom
    draw.rectangle([28, h - 140, w, h], fill=NAVY)

    font_brand = load_font(36)
    font_cat = load_font(28)
    font_title = load_font(52)
    font_sku = load_font(34)
    font_small = load_font(26)

    draw.text((60, 50), "iCOMPLY SUPPLYS", fill=WHITE, font=font_brand)
    draw.text((60, 110), "TRADE FIRE & SECURITY", fill=accent, font=font_small)

    # Category pill
    pill = f"  {cat}  "
    pb = draw.textbbox((0, 0), pill, font=font_cat)
    pw, ph = pb[2] - pb[0] + 24, pb[3] - pb[1] + 16
    draw.rounded_rectangle([60, 250, 60 + pw, 250 + ph], radius=12, fill=accent)
    draw.text((72, 258), pill.strip(), fill=WHITE, font=font_cat)

    # Title
    lines = wrap(draw, title, font_title, w - 140)
    y = 340
    for line in lines[:5]:
        draw.text((60, y), line, fill=NAVY, font=font_title)
        y += 68

    # SKU box
    y += 20
    draw.rounded_rectangle([60, y, w - 60, y + 90], radius=16, fill=(226, 232, 240))
    draw.text((90, y + 26), f"SKU: {sku or 'N/A'}", fill=NAVY, font=font_sku)

    # Handle fingerprint so images are unique & verifiable
    draw.text((60, h - 95), f"ID: {handle}", fill=(148, 163, 184), font=font_small)
    draw.text((60, h - 55), "Official product image card · Match title before install", fill=WHITE, font=font_small)

    # Decorative icon block by category (simple shapes)
    cx, cy = w - 220, 520
    draw.ellipse([cx - 90, cy - 90, cx + 90, cy + 90], outline=accent, width=10)
    if "panel" in collection:
        draw.rectangle([cx - 45, cy - 55, cx + 45, cy + 55], outline=accent, width=6)
    elif "detector" in collection:
        draw.ellipse([cx - 40, cy - 40, cx + 40, cy + 40], fill=accent)
    elif "call" in collection:
        draw.rectangle([cx - 50, cy - 50, cx + 50, cy + 50], fill=accent)
    elif "sounder" in collection:
        for i, r in enumerate((30, 50, 70)):
            draw.arc([cx - r, cy - r, cx + r, cy + r], 200, 340, fill=accent, width=4)
    elif "batter" in collection or "power" in collection:
        draw.rectangle([cx - 35, cy - 50, cx + 35, cy + 50], outline=accent, width=6)
        draw.rectangle([cx - 12, cy - 62, cx + 12, cy - 50], fill=accent)
    else:
        draw.polygon([(cx, cy - 55), (cx + 55, cy + 40), (cx - 55, cy + 40)], outline=accent)

    out.parent.mkdir(parents=True, exist_ok=True)
    img.save(out, "PNG", optimize=True)
    return out


def iter_products():
    since = 0
    while True:
        data = api(
            "GET",
            f"/products.json?limit=50&fields=id,title,handle,tags,product_type,images,variants&since_id={since}",
        )
        batch = data.get("products") or []
        if not batch:
            break
        for p in batch:
            since = max(since, int(p["id"]))
            yield p
        if len(batch) < 50:
            break
        time.sleep(0.1)  # extra courtesy between pages


def audit_product(product: dict) -> dict:
    """Return audit status for one product.

    OK when: exactly 1 image, alt == title, CDN src present.
    """
    pid = product["id"]
    handle = product.get("handle") or ""
    title = product.get("title") or ""
    images = product.get("images") or []

    issues = []
    n = len(images)
    if n == 0:
        issues.append("zero_images")
    elif n > 1:
        issues.append(f"multi_images:{n}")

    src = ""
    alt = ""
    if n >= 1:
        img = images[0]
        src = img.get("src") or ""
        alt = img.get("alt") if img.get("alt") is not None else ""
        if not src or "cdn.shopify.com" not in src:
            issues.append("missing_cdn_src")
        if alt != title:
            issues.append("alt_mismatch")
        # if multi, also check remaining alts for report
        for extra in images[1:]:
            if (extra.get("alt") or "") != title:
                if "alt_mismatch" not in issues:
                    issues.append("alt_mismatch")
                break

    return {
        "id": pid,
        "handle": handle,
        "title": title,
        "image_count": n,
        "src": src,
        "alt": alt,
        "issues": issues,
        "status": "ok" if not issues else "needs_fix",
    }


def fix_product(product: dict) -> dict:
    """Regenerate branded card, delete old images, upload single new image."""
    pid = product["id"]
    handle = product["handle"]
    title = product["title"]
    sku = ""
    if product.get("variants"):
        sku = product["variants"][0].get("sku") or ""
    coll = infer_collection(product)
    out = IMG_DIR / f"{handle}.png"

    make_image(title, sku, coll, handle, out)

    # Delete ALL existing images so wrong/stale ones cannot remain
    imgs = product.get("images") or []
    if not imgs:
        imgs = api("GET", f"/products/{pid}/images.json").get("images") or []
    for im in imgs:
        try:
            api("DELETE", f"/products/{pid}/images/{im['id']}.json")
        except Exception as ex:
            print(f"  warn delete image {im.get('id')}: {ex}")

    # Upload correct image with exact filename + alt = product title
    b64 = base64.b64encode(out.read_bytes()).decode()
    uploaded = api(
        "POST",
        f"/products/{pid}/images.json",
        {
            "image": {
                "attachment": b64,
                "filename": f"{handle}.png",
                "alt": title,
                "position": 1,
            }
        },
    )["image"]

    src = uploaded.get("src", "")
    alt = uploaded.get("alt", "")
    ok = bool(src) and "cdn.shopify.com" in src and alt == title
    return {
        "id": pid,
        "handle": handle,
        "sku": sku,
        "collection": coll,
        "src": src,
        "alt": alt,
        "ok": ok,
        "action": "fixed",
    }


def main():
    print("Refreshing token...")
    get_token(force=True)

    print("Fetching all products...")
    products = list(iter_products())
    print(f"Products found: {len(products)}")

    # --- Phase 1: Audit ---
    print("\n=== AUDIT ===")
    audit_rows = []
    needs_fix = []
    for i, p in enumerate(products, 1):
        row = audit_product(p)
        audit_rows.append(row)
        if row["status"] != "ok":
            needs_fix.append(p)
            print(f"  NEED [{i}] {row['handle']}: {', '.join(row['issues'])}")
        elif i % 50 == 0:
            print(f"  audited {i}/{len(products)}...")

    ok_count = sum(1 for r in audit_rows if r["status"] == "ok")
    print(f"Audit: ok={ok_count} needs_fix={len(needs_fix)} total={len(products)}")

    # --- Phase 2: Fix only broken products ---
    print("\n=== FIX ===")
    fix_results = []
    fixed = 0
    failed = 0

    for i, p in enumerate(needs_fix, 1):
        handle = p["handle"]
        print(f"[{i}/{len(needs_fix)}] fixing {handle}")
        try:
            result = fix_product(p)
            fix_results.append(result)
            if result.get("ok"):
                fixed += 1
                print(f"  OK src=...{(result.get('src') or '')[-60:]}")
            else:
                failed += 1
                print(f"  FAIL verify: alt={result.get('alt')!r} src={result.get('src')!r}")
        except Exception as ex:
            failed += 1
            print(f"  ERROR: {ex}")
            fix_results.append({
                "id": p["id"],
                "handle": handle,
                "error": str(ex),
                "ok": False,
                "action": "failed",
            })
        time.sleep(0.25)

    # --- Phase 3: Re-audit fixed products to confirm ---
    print("\n=== RE-AUDIT FIXED ===")
    final_products = {}
    for r in audit_rows:
        final_products[r["id"]] = dict(r)

    # Re-fetch products that were fixed for final verification
    recheck_ids = [r["id"] for r in fix_results if r.get("ok")]
    for pid in recheck_ids:
        try:
            data = api("GET", f"/products/{pid}.json?fields=id,title,handle,images")
            p = data.get("product") or {}
            if p:
                final_products[pid] = audit_product(p)
                final_products[pid]["action"] = "fixed"
            time.sleep(0.1)
        except Exception as ex:
            print(f"  re-audit warn {pid}: {ex}")

    # Mark failed fixes
    for r in fix_results:
        if not r.get("ok"):
            row = final_products.get(r["id"], {})
            row["status"] = "failed"
            row["error"] = r.get("error") or "verify_failed"
            row["action"] = "failed"
            final_products[r["id"]] = row

    final_list = list(final_products.values())
    final_ok = sum(1 for r in final_list if r.get("status") == "ok")
    final_failed = sum(1 for r in final_list if r.get("status") == "failed")
    final_still = sum(1 for r in final_list if r.get("status") == "needs_fix")

    audit_out = {
        "total": len(final_list),
        "ok": final_ok,
        "fixed": fixed,
        "failed": final_failed + final_still,
        "needs_fix_remaining": final_still,
        "products": sorted(final_list, key=lambda x: x.get("handle") or ""),
        "fix_log": fix_results,
    }

    AUDIT_FINAL.write_text(json.dumps(audit_out, indent=2), encoding="utf-8")
    LOG.write_text(json.dumps(fix_results, indent=2), encoding="utf-8")

    print(f"\n=== SUMMARY ===")
    print(f"total={len(final_list)} ok={final_ok} fixed={fixed} failed={failed}")
    print(f"Audit written: {AUDIT_FINAL}")
    print(f"Fix log: {LOG}")
    if failed:
        for b in fix_results:
            if not b.get("ok"):
                print(" FAIL", b)


if __name__ == "__main__":
    main()
