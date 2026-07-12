#!/usr/bin/env python3
"""Regenerate distinct orange-branded images for all Service Package products.

Finds products with product_type "Service Package" or tag service-package,
creates a branded trade card (title + SERVICE PACKAGE + SKU), replaces all
product images, sets alt = title, and writes service_images_result.json.
"""

from __future__ import annotations

import base64
import json
import time
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

from shopify_client import api, get_token

ROOT = Path(__file__).resolve().parent
IMG_DIR = ROOT / "images" / "service_packages_regen"
RESULT = ROOT / "service_images_result.json"

NAVY = (10, 37, 64)       # #0a2540
ORANGE = (255, 107, 0)    # #ff6b00
WHITE = (255, 255, 255)
LIGHT = (248, 250, 252)
MUTED = (100, 116, 139)
SLATE = (226, 232, 240)

# Per-handle accent variations so cards are visually distinct while staying orange-branded
HANDLE_ACCENTS = {
    "electrical-compliance-package": (255, 107, 0),
    "fire-alarm-service-package": (234, 88, 12),
    "emergency-lighting-package": (249, 115, 22),
    "aov-air-handling-package": (251, 146, 60),
    "nurse-call-systems-package": (255, 122, 24),
    "gas-safety-package": (194, 65, 12),
    "intruder-alarm-package": (255, 90, 0),
    "cctv-systems-package": (234, 88, 12),
    "access-control-package": (255, 140, 30),
    "door-entry-package": (249, 115, 22),
    "intercoms-package": (255, 107, 0),
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


def wrap(draw, text: str, font, max_w: int) -> list[str]:
    words = text.split()
    lines: list[str] = []
    cur = ""
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


def is_service_package(product: dict) -> bool:
    ptype = (product.get("product_type") or "").strip()
    if ptype == "Service Package":
        return True
    tags = product.get("tags") or ""
    if isinstance(tags, list):
        tag_list = [str(t).strip().lower() for t in tags]
    else:
        tag_list = [t.strip().lower() for t in str(tags).split(",")]
    return "service-package" in tag_list


def make_image(title: str, sku: str, handle: str, out: Path) -> Path:
    w, h = 1200, 1200
    accent = HANDLE_ACCENTS.get(handle, ORANGE)

    img = Image.new("RGB", (w, h), LIGHT)
    draw = ImageDraw.Draw(img)

    # Left orange accent bar
    draw.rectangle([0, 0, 28, h], fill=accent)
    # Top navy header
    draw.rectangle([28, 0, w, 200], fill=NAVY)
    draw.rectangle([28, 200, w, 214], fill=accent)
    # Bottom navy footer
    draw.rectangle([28, h - 140, w, h], fill=NAVY)

    font_brand = load_font(36)
    font_cat = load_font(28)
    font_title = load_font(52)
    font_sku = load_font(34)
    font_small = load_font(26)

    draw.text((60, 50), "iCOMPLY SUPPLYS", fill=WHITE, font=font_brand)
    draw.text((60, 110), "TRADE FIRE & SECURITY", fill=accent, font=font_small)

    # SERVICE PACKAGE pill
    pill = "SERVICE PACKAGE"
    pb = draw.textbbox((0, 0), f"  {pill}  ", font=font_cat)
    pw, ph = pb[2] - pb[0] + 24, pb[3] - pb[1] + 16
    draw.rounded_rectangle([60, 250, 60 + pw, 250 + ph], radius=12, fill=accent)
    draw.text((72, 258), pill, fill=WHITE, font=font_cat)

    # Title
    lines = wrap(draw, title, font_title, w - 140)
    y = 340
    for line in lines[:5]:
        draw.text((60, y), line, fill=NAVY, font=font_title)
        y += 68

    # SKU box
    y += 20
    draw.rounded_rectangle([60, y, w - 60, y + 90], radius=16, fill=SLATE)
    draw.text((90, y + 26), f"SKU: {sku or 'N/A'}", fill=NAVY, font=font_sku)

    # Handle fingerprint for uniqueness
    draw.text((60, h - 95), f"ID: {handle}", fill=(148, 163, 184), font=font_small)
    draw.text(
        (60, h - 55),
        "Service package image card · Match title before install",
        fill=WHITE,
        font=font_small,
    )

    # Decorative icon — gear/package style circle unique per handle via offset
    # Use handle hash so each product gets a slightly different icon placement/style
    seed = sum(ord(c) for c in handle) % 5
    cx, cy = w - 220, 520
    draw.ellipse([cx - 90, cy - 90, cx + 90, cy + 90], outline=accent, width=10)
    if seed == 0:
        # package box
        draw.rectangle([cx - 45, cy - 40, cx + 45, cy + 40], outline=accent, width=6)
        draw.line([(cx - 45, cy), (cx + 45, cy)], fill=accent, width=4)
        draw.line([(cx, cy - 40), (cx, cy + 40)], fill=accent, width=4)
    elif seed == 1:
        # shield
        draw.polygon(
            [(cx, cy - 55), (cx + 50, cy - 25), (cx + 40, cy + 40), (cx, cy + 60), (cx - 40, cy + 40), (cx - 50, cy - 25)],
            outline=accent,
        )
    elif seed == 2:
        # wrench/tool cross
        draw.ellipse([cx - 35, cy - 35, cx + 35, cy + 35], outline=accent, width=6)
        draw.ellipse([cx - 12, cy - 12, cx + 12, cy + 12], fill=accent)
    elif seed == 3:
        # checklist bars
        for i, dy in enumerate((-40, -10, 20)):
            draw.rectangle([cx - 40, cy + dy, cx + 40, cy + dy + 16], outline=accent, width=4)
            draw.ellipse([cx - 55, cy + dy + 2, cx - 43, cy + dy + 14], fill=accent)
    else:
        # triangle mark
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
        time.sleep(0.1)


def replace_image(product: dict) -> dict:
    pid = product["id"]
    handle = product.get("handle") or ""
    title = product.get("title") or ""
    sku = ""
    if product.get("variants"):
        sku = product["variants"][0].get("sku") or ""

    out = IMG_DIR / f"{handle}.png"
    make_image(title, sku, handle, out)

    # Delete all existing images
    imgs = product.get("images") or []
    if not imgs:
        imgs = api("GET", f"/products/{pid}/images.json").get("images") or []
    for im in imgs:
        try:
            api("DELETE", f"/products/{pid}/images/{im['id']}.json")
        except Exception as ex:
            print(f"  warn delete image {im.get('id')}: {ex}")

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
        "title": title,
        "sku": sku,
        "src": src,
        "alt": alt,
        "local_path": str(out),
        "ok": ok,
        "action": "regenerated",
    }


def main() -> int:
    print("Refreshing token...")
    get_token(force=True)

    print("Fetching products...")
    all_products = list(iter_products())
    services = [p for p in all_products if is_service_package(p)]
    print(f"Total products: {len(all_products)}")
    print(f"Service packages found: {len(services)}")

    results = []
    ok_count = 0
    fail_count = 0

    for i, p in enumerate(services, 1):
        handle = p.get("handle") or "?"
        print(f"[{i}/{len(services)}] {handle}")
        try:
            row = replace_image(p)
            results.append(row)
            if row.get("ok"):
                ok_count += 1
                print(f"  OK alt={row.get('alt')!r} src=...{(row.get('src') or '')[-50:]}")
            else:
                fail_count += 1
                print(f"  FAIL verify alt={row.get('alt')!r} src={row.get('src')!r}")
        except Exception as ex:
            fail_count += 1
            print(f"  ERROR: {ex}")
            results.append(
                {
                    "id": p.get("id"),
                    "handle": handle,
                    "title": p.get("title"),
                    "error": str(ex),
                    "ok": False,
                    "action": "failed",
                }
            )
        time.sleep(0.25)

    # Re-fetch to confirm final state
    print("\n=== RE-VERIFY ===")
    verified = []
    for row in results:
        pid = row.get("id")
        if not pid or not row.get("ok"):
            verified.append(row)
            continue
        try:
            data = api("GET", f"/products/{pid}.json?fields=id,title,handle,images,variants,tags,product_type")
            p = data.get("product") or {}
            images = p.get("images") or []
            title = p.get("title") or ""
            src = images[0].get("src", "") if images else ""
            alt = images[0].get("alt", "") if images else ""
            n = len(images)
            ok = n == 1 and bool(src) and "cdn.shopify.com" in src and alt == title
            row["verified"] = {
                "image_count": n,
                "src": src,
                "alt": alt,
                "ok": ok,
            }
            if not ok:
                row["ok"] = False
                fail_count += 1
                ok_count = max(0, ok_count - 1)
                print(f"  RECHECK FAIL {row.get('handle')}: n={n} alt={alt!r}")
            else:
                print(f"  RECHECK OK {row.get('handle')}")
            verified.append(row)
            time.sleep(0.1)
        except Exception as ex:
            row["verified"] = {"error": str(ex), "ok": False}
            row["ok"] = False
            verified.append(row)
            print(f"  RECHECK ERROR {row.get('handle')}: {ex}")

    final_ok = sum(1 for r in verified if r.get("ok"))
    final_fail = len(verified) - final_ok

    out = {
        "generated_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
        "total_service_packages": len(services),
        "ok": final_ok,
        "failed": final_fail,
        "products": verified,
    }
    RESULT.write_text(json.dumps(out, indent=2), encoding="utf-8")
    print(f"\n=== SUMMARY ===")
    print(f"service_packages={len(services)} ok={final_ok} failed={final_fail}")
    print(f"Result written: {RESULT}")
    return 0 if final_fail == 0 else 1


if __name__ == "__main__":
    raise SystemExit(main())
