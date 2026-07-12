#!/usr/bin/env python3
"""Merge all catalog/products_*.json into catalog/all_products.json.

Validates that handles and SKUs are unique across the full catalog.
Exits non-zero if duplicates or missing required fields are found.
"""

from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CATALOG_DIR = ROOT / "catalog"
OUT_PATH = CATALOG_DIR / "all_products.json"

REQUIRED_FIELDS = ("handle", "title", "sku", "price")


def load_product_files() -> list[tuple[Path, list[dict]]]:
    files = sorted(CATALOG_DIR.glob("products_*.json"))
    # Exclude the merged output if someone names it products_all.json etc. — only products_*.json
    loaded: list[tuple[Path, list[dict]]] = []
    for f in files:
        data = json.loads(f.read_text(encoding="utf-8"))
        if not isinstance(data, list):
            raise ValueError(f"{f.name}: expected a JSON array of product objects")
        loaded.append((f, data))
    return loaded


def main() -> int:
    print("=== Merge catalog products ===")
    print(f"Source: {CATALOG_DIR / 'products_*.json'}")

    if not CATALOG_DIR.exists():
        print(f"ERROR: catalog dir missing: {CATALOG_DIR}", file=sys.stderr)
        return 1

    try:
        loaded = load_product_files()
    except Exception as ex:
        print(f"ERROR loading files: {ex}", file=sys.stderr)
        return 1

    if not loaded:
        print("No products_*.json files found.")
        OUT_PATH.write_text("[]\n", encoding="utf-8")
        print(f"Wrote empty {OUT_PATH}")
        return 0

    all_products: list[dict] = []
    handle_sources: dict[str, list[str]] = {}
    sku_sources: dict[str, list[str]] = {}
    field_errors: list[str] = []

    for path, products in loaded:
        print(f"  {path.name}: {len(products)} products")
        for idx, p in enumerate(products):
            if not isinstance(p, dict):
                field_errors.append(f"{path.name}[{idx}]: not an object")
                continue

            for field in REQUIRED_FIELDS:
                if p.get(field) in (None, ""):
                    field_errors.append(
                        f"{path.name}[{idx}]: missing required field '{field}'"
                    )

            handle = str(p.get("handle") or "").strip()
            sku = str(p.get("sku") or "").strip()
            src = f"{path.name}[{idx}]"

            if handle:
                handle_sources.setdefault(handle, []).append(src)
            if sku:
                sku_sources.setdefault(sku, []).append(src)

            all_products.append(p)

    dup_handles = {h: srcs for h, srcs in handle_sources.items() if len(srcs) > 1}
    dup_skus = {s: srcs for s, srcs in sku_sources.items() if len(srcs) > 1}

    ok = True
    if field_errors:
        ok = False
        print(f"\nField errors ({len(field_errors)}):")
        for e in field_errors[:50]:
            print(f"  - {e}")
        if len(field_errors) > 50:
            print(f"  ... and {len(field_errors) - 50} more")

    if dup_handles:
        ok = False
        print(f"\nDuplicate handles ({len(dup_handles)}):")
        for h, srcs in sorted(dup_handles.items()):
            print(f"  - {h}: {', '.join(srcs)}")

    if dup_skus:
        ok = False
        print(f"\nDuplicate SKUs ({len(dup_skus)}):")
        for s, srcs in sorted(dup_skus.items()):
            print(f"  - {s}: {', '.join(srcs)}")

    OUT_PATH.write_text(
        json.dumps(all_products, indent=2, ensure_ascii=False) + "\n",
        encoding="utf-8",
    )
    print(f"\nWrote {len(all_products)} products -> {OUT_PATH}")

    if not ok:
        print("\nVALIDATION FAILED — fix duplicates/fields before upload.")
        return 1

    print("Validation OK: all handles and SKUs unique.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
