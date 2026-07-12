#!/usr/bin/env python3
"""Regenerate unique product images and re-upload so each Shopify product
has the correct matching image (title + SKU + category), not a generic card.

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

NAVY = (10, 37, 64)
ORANGE = (255, 107, 0)
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
    # Filename MUST be unique per product handle
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


def main():
    print("Refreshing token...")
    get_token(force=True)
    results = []
    products = list(iter_products())
    print(f"Products to fix: {len(products)}")

    for i, p in enumerate(products, 1):
        pid = p["id"]
        handle = p["handle"]
        title = p["title"]
        sku = ""
        if p.get("variants"):
            sku = p["variants"][0].get("sku") or ""
        coll = infer_collection(p)
        out = IMG_DIR / f"{handle}.png"
        print(f"[{i}/{len(products)}] {handle}")
        try:
            make_image(title, sku, coll, handle, out)

            # Delete ALL existing images so wrong/stale ones cannot remain
            imgs = p.get("images") or []
            # re-fetch images in case list incomplete
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
            # Verify filename embeds handle
            ok = handle in src or handle.replace("-", "_") in src
            results.append(
                {
                    "id": pid,
                    "handle": handle,
                    "sku": sku,
                    "collection": coll,
                    "src": src,
                    "alt": uploaded.get("alt"),
                    "ok": ok,
                }
            )
            print(f"  OK src=...{src[-60:] if src else 'NONE'}")
        except Exception as ex:
            print(f"  ERROR: {ex}")
            results.append({"id": pid, "handle": handle, "error": str(ex), "ok": False})
        time.sleep(0.25)

    LOG.write_text(json.dumps(results, indent=2), encoding="utf-8")
    bad = [r for r in results if not r.get("ok")]
    print(f"\nDone. fixed={len(results)-len(bad)} failed={len(bad)}")
    print(f"Log: {LOG}")
    if bad:
        for b in bad[:20]:
            print(" FAIL", b)


if __name__ == "__main__":
    main()
