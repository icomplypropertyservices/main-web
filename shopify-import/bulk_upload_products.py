#!/usr/bin/env python3
"""Bulk-upload catalog products to Shopify (iComply Supplys).

Loads products_*.json from catalog/, generates branded PNG images,
creates/updates products via REST, attaches images, and collects into
collections. Writes upload_log.json with results.

Does not run automatically — invoke explicitly when catalog JSON exists.
"""

from __future__ import annotations

import base64
import json
import sys
import time
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

from shopify_client import api, get_token

ROOT = Path(__file__).resolve().parent
CATALOG_DIR = ROOT / "catalog"
IMG_DIR = ROOT / "images" / "products"
LOG_PATH = ROOT / "upload_log.json"
COLLECTIONS_RESULT = CATALOG_DIR / "collections_result.json"

# Brand colours
NAVY = (10, 37, 64)       # #0a2540
ORANGE = (255, 107, 0)    # #ff6b00
WHITE = (255, 255, 255)
LIGHT = (245, 247, 250)
MUTED = (100, 116, 139)

RATE_SLEEP = 0.4


def load_font(size: int) -> ImageFont.ImageFont:
    candidates = [
        r"C:\Windows\Fonts\segoeui.ttf",
        r"C:\Windows\Fonts\arial.ttf",
        r"C:\Windows\Fonts\calibri.ttf",
    ]
    for path in candidates:
        if Path(path).exists():
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def wrap_text(draw: ImageDraw.ImageDraw, text: str, font, max_width: int) -> list[str]:
    words = text.split()
    lines: list[str] = []
    cur = ""
    for word in words:
        test = f"{cur} {word}".strip()
        bbox = draw.textbbox((0, 0), test, font=font)
        if bbox[2] - bbox[0] > max_width and cur:
            lines.append(cur)
            cur = word
        else:
            cur = test
    if cur:
        lines.append(cur)
    return lines


def make_product_image(label: str, sku: str, out_path: Path) -> Path:
    """Generate a simple branded PNG labeled with image_label/title."""
    w, h = 1200, 1200
    img = Image.new("RGB", (w, h), LIGHT)
    draw = ImageDraw.Draw(img)

    draw.rectangle([0, 0, w, 220], fill=NAVY)
    draw.rectangle([0, 220, w, 232], fill=ORANGE)
    draw.rectangle([0, h - 120, w, h], fill=NAVY)

    font_brand = load_font(42)
    font_title = load_font(56)
    font_small = load_font(28)

    draw.text((60, 70), "iCOMPLY SUPPLYS", fill=WHITE, font=font_brand)
    draw.text((60, 130), "Trade Supply", fill=ORANGE, font=font_small)

    lines = wrap_text(draw, label, font_title, w - 120)
    y = 360
    for line in lines[:5]:
        draw.text((60, y), line, fill=NAVY, font=font_title)
        y += 72

    if sku:
        draw.text((60, h - 80), f"SKU {sku}  ·  North West UK", fill=WHITE, font=font_small)
    else:
        draw.text((60, h - 80), "North West UK", fill=WHITE, font=font_small)

    draw.ellipse([w - 320, 280, w - 80, 520], outline=ORANGE, width=8)

    out_path.parent.mkdir(parents=True, exist_ok=True)
    img.save(out_path, "PNG", optimize=True)
    return out_path


def normalize_tags(tags) -> str:
    if tags is None:
        return ""
    if isinstance(tags, list):
        return ", ".join(str(t) for t in tags)
    return str(tags)


def load_catalog_products() -> list[dict]:
    """Load deduped all_products.json if present, else merge products_*.json."""
    all_path = CATALOG_DIR / "all_products.json"
    if all_path.exists():
        data = json.loads(all_path.read_text(encoding="utf-8"))
        if not isinstance(data, list):
            raise ValueError("all_products.json: expected JSON array")
        # de-dupe by handle just in case
        seen: set[str] = set()
        products: list[dict] = []
        for item in data:
            h = (item.get("handle") or "").strip()
            if not h or h in seen:
                continue
            seen.add(h)
            products.append(item)
        print(f"  Loaded {len(products)} products from all_products.json")
        return products

    files = sorted(CATALOG_DIR.glob("products_*.json"))
    products = []
    seen = set()
    for f in files:
        data = json.loads(f.read_text(encoding="utf-8"))
        if not isinstance(data, list):
            raise ValueError(f"{f.name}: expected JSON array of product objects")
        print(f"  Loaded {len(data)} products from {f.name}")
        for item in data:
            h = (item.get("handle") or "").strip()
            if not h or h in seen:
                continue
            seen.add(h)
            products.append(item)
    return products


def load_collection_map() -> dict[str, int]:
    """Map collection handle -> collection_id from file or live API."""
    handle_to_id: dict[str, int] = {}

    if COLLECTIONS_RESULT.exists():
        data = json.loads(COLLECTIONS_RESULT.read_text(encoding="utf-8"))
        for row in data:
            h = row.get("handle")
            cid = row.get("id")
            if h and cid:
                handle_to_id[h] = int(cid)
        if handle_to_id:
            print(f"  Collections from {COLLECTIONS_RESULT.name}: {len(handle_to_id)}")
            return handle_to_id

    print("  collections_result.json missing/empty — fetching custom collections...")
    data = api("GET", "/custom_collections.json?limit=250")
    for c in data.get("custom_collections", []):
        handle_to_id[c["handle"]] = int(c["id"])
    # also smart collections if needed for mapping
    try:
        smart = api("GET", "/smart_collections.json?limit=250")
        for c in smart.get("smart_collections", []):
            handle_to_id.setdefault(c["handle"], int(c["id"]))
    except Exception as ex:
        print(f"  smart collections warn: {ex}")
    print(f"  Collections from API: {len(handle_to_id)}")
    return handle_to_id


def find_existing_products() -> dict[str, dict]:
    """Return handle -> product (id, handle, title) for all products."""
    existing: dict[str, dict] = {}
    # Shopify REST pagination via page_info is link-header based; use since_id walk
    since_id = 0
    while True:
        path = f"/products.json?limit=250&fields=id,title,handle,variants&since_id={since_id}"
        data = api("GET", path)
        batch = data.get("products", [])
        if not batch:
            break
        for p in batch:
            existing[p["handle"]] = p
            since_id = max(since_id, int(p["id"]))
        if len(batch) < 250:
            break
    return existing


def upload_image(product_id: int, image_path: Path, alt: str) -> None:
    b64 = base64.b64encode(image_path.read_bytes()).decode()
    api(
        "POST",
        f"/products/{product_id}/images.json",
        {
            "image": {
                "attachment": b64,
                "filename": image_path.name,
                "alt": alt,
            }
        },
    )


def build_variant(spec: dict) -> dict:
    variant: dict = {
        "price": str(spec.get("price", "0.00")),
        "sku": spec.get("sku") or "",
        "requires_shipping": bool(spec.get("requires_shipping", True)),
        "taxable": True,
        "option1": "Default Title",
    }
    weight_grams = spec.get("weight_grams")
    if weight_grams is not None:
        try:
            grams = float(weight_grams)
            variant["weight"] = grams / 1000.0
            variant["weight_unit"] = "kg"
            variant["grams"] = int(round(grams))
        except (TypeError, ValueError):
            pass
    compare = spec.get("compare_at_price")
    if compare not in (None, ""):
        variant["compare_at_price"] = str(compare)
    return variant


def create_product(spec: dict, image_path: Path) -> dict:
    payload = {
        "product": {
            "title": spec["title"],
            "body_html": (spec.get("body_html") or "").strip(),
            "vendor": spec.get("vendor") or "iComply Supplys",
            "product_type": spec.get("product_type") or "",
            "tags": normalize_tags(spec.get("tags")),
            "status": "active",
            "handle": spec["handle"],
            "variants": [build_variant(spec)],
            "options": [{"name": "Title", "values": ["Default Title"]}],
        }
    }
    created = api("POST", "/products.json", payload)["product"]
    upload_image(created["id"], image_path, spec.get("image_label") or spec["title"])
    return created


def update_product(product_id: int, spec: dict, image_path: Path | None = None) -> dict:
    """Update existing product: price, body, and optionally re-upload image."""
    api(
        "PUT",
        f"/products/{product_id}.json",
        {
            "product": {
                "id": product_id,
                "title": spec["title"],
                "body_html": (spec.get("body_html") or "").strip(),
                "vendor": spec.get("vendor") or "iComply Supplys",
                "product_type": spec.get("product_type") or "",
                "tags": normalize_tags(spec.get("tags")),
                "status": "active",
            }
        },
    )
    full = api("GET", f"/products/{product_id}.json")["product"]
    if full.get("variants"):
        vid = full["variants"][0]["id"]
        vbody = build_variant(spec)
        vbody["id"] = vid
        api("PUT", f"/variants/{vid}.json", {"variant": vbody})
    if image_path and image_path.exists():
        imgs = api("GET", f"/products/{product_id}/images.json").get("images", [])
        for im in imgs:
            try:
                api("DELETE", f"/products/{product_id}/images/{im['id']}.json")
            except Exception as ex:
                print(f"    warn delete image: {ex}")
        upload_image(product_id, image_path, spec.get("image_label") or spec["title"])
    return full


def collect_into(product_id: int, collection_id: int) -> bool:
    """Add product to collection via collects.json. Returns True if linked."""
    try:
        api(
            "POST",
            "/collects.json",
            {
                "collect": {
                    "product_id": product_id,
                    "collection_id": collection_id,
                }
            },
        )
        return True
    except Exception as ex:
        msg = str(ex).lower()
        if "already" in msg or "422" in msg:
            return True  # already in collection
        print(f"    collect warn: {ex}")
        return False


def main() -> int:
    print("=== iComply Supplys bulk product upload ===")
    print(f"Catalog dir: {CATALOG_DIR}")

    print("\nLoading catalog products...")
    products = load_catalog_products()
    if not products:
        print("No products_*.json files found (or all empty). Nothing to upload.")
        print(f"Place catalog files under: {CATALOG_DIR}")
        LOG_PATH.write_text(
            json.dumps(
                {
                    "status": "no_products",
                    "results": [],
                    "timestamp": time.strftime("%Y-%m-%dT%H:%M:%S"),
                },
                indent=2,
            ),
            encoding="utf-8",
        )
        return 0

    print(f"Total products to process: {len(products)}")

    print("\nGetting access token...")
    get_token()
    print("Token OK")

    print("\nLoading collections map...")
    coll_map = load_collection_map()

    print("\nFetching existing products...")
    existing = find_existing_products()
    print(f"Existing products in store: {len(existing)}")

    IMG_DIR.mkdir(parents=True, exist_ok=True)
    results: list[dict] = []
    total = len(products)

    for i, spec in enumerate(products, start=1):
        handle = (spec.get("handle") or "").strip()
        title = spec.get("title") or handle or f"product-{i}"
        sku = spec.get("sku") or ""
        label = (spec.get("image_label") or title).strip()
        collection_handle = (spec.get("collection") or "").strip()

        print(f"\n[{i}/{total}] {title}")
        print(f"  handle={handle}  sku={sku}  collection={collection_handle or '-'}")

        if not handle:
            print("  SKIP: missing handle")
            results.append(
                {
                    "action": "skipped",
                    "reason": "missing_handle",
                    "title": title,
                    "sku": sku,
                }
            )
            time.sleep(RATE_SLEEP)
            continue

        img_path = IMG_DIR / f"{handle}.png"
        try:
            print(f"  Generating image -> {img_path.relative_to(ROOT)}")
            make_product_image(label, sku, img_path)
        except Exception as ex:
            print(f"  ERROR generating image: {ex}")
            results.append(
                {
                    "action": "error",
                    "stage": "image",
                    "handle": handle,
                    "title": title,
                    "error": str(ex),
                }
            )
            time.sleep(RATE_SLEEP)
            continue

        try:
            if handle in existing:
                pid = int(existing[handle]["id"])
                print(f"  Handle exists id={pid} — updating price/body/image")
                update_product(pid, spec, img_path)
                action = "updated"
                product_id = pid
            else:
                created = create_product(spec, img_path)
                product_id = int(created["id"])
                existing[handle] = created
                print(f"  Created id={product_id}")
                action = "created"

            collected = False
            if collection_handle:
                cid = coll_map.get(collection_handle)
                if cid:
                    collected = collect_into(product_id, cid)
                    print(f"  Collected into {collection_handle} ({cid}): {collected}")
                else:
                    print(f"  WARN: collection handle '{collection_handle}' not found")

            results.append(
                {
                    "action": action,
                    "id": product_id,
                    "handle": handle,
                    "title": title,
                    "sku": sku,
                    "price": str(spec.get("price", "")),
                    "collection": collection_handle or None,
                    "collected": collected,
                    "image": str(img_path),
                }
            )
        except Exception as ex:
            print(f"  ERROR: {ex}")
            results.append(
                {
                    "action": "error",
                    "stage": "upload",
                    "handle": handle,
                    "title": title,
                    "sku": sku,
                    "error": str(ex),
                }
            )

        time.sleep(RATE_SLEEP)

    created_n = sum(1 for r in results if r.get("action") == "created")
    updated_n = sum(1 for r in results if r.get("action") == "updated")
    error_n = sum(1 for r in results if r.get("action") == "error")
    skipped_n = sum(1 for r in results if r.get("action") == "skipped")

    log = {
        "status": "complete",
        "timestamp": time.strftime("%Y-%m-%dT%H:%M:%S"),
        "totals": {
            "processed": total,
            "created": created_n,
            "updated": updated_n,
            "errors": error_n,
            "skipped": skipped_n,
        },
        "results": results,
    }
    LOG_PATH.write_text(json.dumps(log, indent=2), encoding="utf-8")

    print("\n=== Done ===")
    print(f"  created={created_n}  updated={updated_n}  errors={error_n}  skipped={skipped_n}")
    print(f"  Log: {LOG_PATH}")
    return 1 if error_n else 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        raise
