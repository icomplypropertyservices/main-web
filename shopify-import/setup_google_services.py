#!/usr/bin/env python3
"""Google Services prep for iComply Supplys Shopify store.

- Inspect sales channels / publications (REST + GraphQL)
- Publish physical products to Online Store publication
- Tag non-POA physical products with google-shopping
- Ensure vendor + product_type present
- Write catalog/google_product_feed_sample.json (first 50)
- Write google_setup_result.json
"""

from __future__ import annotations

import json
import re
import time
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from shopify_client import api, get_store, graphql

ROOT = Path(__file__).parent
CATALOG = ROOT / "catalog"
RESULT_PATH = ROOT / "google_setup_result.json"
FEED_PATH = CATALOG / "google_product_feed_sample.json"
STOREFRONT_URL = "https://icomply-supplys.myshopify.com"

ONLINE_STORE_PUB_ID = None  # resolved at runtime
ONLINE_STORE_GID = None


def strip_html(html: str | None) -> str:
    if not html:
        return ""
    text = re.sub(r"<br\s*/?>", " ", html, flags=re.I)
    text = re.sub(r"</p\s*>", " ", text, flags=re.I)
    text = re.sub(r"<[^>]+>", "", text)
    text = re.sub(r"\s+", " ", text).strip()
    # decode a few common entities
    for a, b in (
        ("&amp;", "&"),
        ("&lt;", "<"),
        ("&gt;", ">"),
        ("&quot;", '"'),
        ("&#39;", "'"),
        ("&nbsp;", " "),
    ):
        text = text.replace(a, b)
    return text[:5000]


def is_poa(product: dict) -> bool:
    tags = [t.strip().lower() for t in (product.get("tags") or "").split(",") if t.strip()]
    tag_blob = " ".join(tags)
    if "poa" in tags or "price-on-application" in tags or "price on application" in tag_blob:
        return True
    title = (product.get("title") or "").lower()
    if "poa" in title or "price on application" in title:
        return True
    for v in product.get("variants") or []:
        try:
            if float(v.get("price") or 0) == 0:
                return True
        except (TypeError, ValueError):
            return True
    return False


def is_physical(product: dict) -> bool:
    variants = product.get("variants") or []
    if not variants:
        return True
    return any(v.get("requires_shipping", True) for v in variants)


def fetch_all_products() -> list[dict]:
    products: list[dict] = []
    since_id = 0
    fields = (
        "id,title,handle,tags,vendor,product_type,status,variants,"
        "images,body_html,published_at,admin_graphql_api_id"
    )
    while True:
        path = f"/products.json?limit=250&fields={fields}"
        if since_id:
            path += f"&since_id={since_id}"
        data = api("GET", path)
        batch = data.get("products") or []
        if not batch:
            break
        products.extend(batch)
        since_id = batch[-1]["id"]
        if len(batch) < 250:
            break
    return products


def fetch_publications() -> dict[str, Any]:
    rest: dict[str, Any] = {}
    gql: dict[str, Any] = {}
    rest_error = None
    gql_error = None
    try:
        rest = api("GET", "/publications.json")
    except Exception as e:
        rest_error = str(e)
    try:
        gql = graphql(
            """
            query {
              publications(first: 25) {
                edges {
                  node {
                    id
                    name
                    supportsFuturePublishing
                    app { id title }
                  }
                }
              }
            }
            """
        )
    except Exception as e:
        gql_error = str(e)
    return {
        "rest": rest,
        "graphql": gql,
        "rest_error": rest_error,
        "graphql_error": gql_error,
    }


def resolve_online_store(pubs: dict) -> tuple[int | None, str | None]:
    pub_id = None
    gid = None
    for p in (pubs.get("rest") or {}).get("publications") or []:
        if (p.get("name") or "").lower() == "online store":
            pub_id = p.get("id")
            break
    for edge in (
        ((pubs.get("graphql") or {}).get("data") or {})
        .get("publications", {})
        .get("edges")
        or []
    ):
        node = edge.get("node") or {}
        if (node.get("name") or "").lower() == "online store":
            gid = node.get("id")
            if not pub_id and gid and "/" in gid:
                try:
                    pub_id = int(gid.rsplit("/", 1)[-1])
                except ValueError:
                    pass
            break
    return pub_id, gid


def product_published_to_online(product_gid: str, online_gid: str) -> bool:
    q = """
    query($id: ID!) {
      product(id: $id) {
        id
        resourcePublications(first: 20) {
          edges {
            node {
              isPublished
              publication { id name }
            }
          }
        }
      }
    }
    """
    data = graphql(q, {"id": product_gid})
    edges = (
        ((data.get("data") or {}).get("product") or {})
        .get("resourcePublications", {})
        .get("edges")
        or []
    )
    for e in edges:
        node = e.get("node") or {}
        pub = node.get("publication") or {}
        if pub.get("id") == online_gid and node.get("isPublished"):
            return True
        if (pub.get("name") or "").lower() == "online store" and node.get("isPublished"):
            return True
    return False


def publish_to_online(product_gid: str, online_gid: str) -> dict:
    mutation = """
    mutation publish($id: ID!, $input: [PublicationInput!]!) {
      publishablePublish(id: $id, input: $input) {
        userErrors { field message }
        publishable {
          ... on Product {
            id
            resourcePublications(first: 5) {
              edges {
                node {
                  isPublished
                  publication { id name }
                }
              }
            }
          }
        }
      }
    }
    """
    return graphql(
        mutation,
        {"id": product_gid, "input": [{"publicationId": online_gid}]},
    )


def update_product_tags_and_meta(product: dict, new_tags: str, vendor: str, product_type: str) -> dict:
    body = {
        "product": {
            "id": product["id"],
            "tags": new_tags,
            "vendor": vendor,
            "product_type": product_type,
        }
    }
    return api("PUT", f"/products/{product['id']}.json", body)


def build_feed_item(product: dict) -> dict:
    variant = (product.get("variants") or [{}])[0]
    images = product.get("images") or []
    image_link = images[0].get("src") if images else ""
    price = variant.get("price") or "0.00"
    # inventory_quantity 0 + inventory_management null typically means not tracked; treat as in stock for feed sample
    inv_mgmt = variant.get("inventory_management")
    inv_qty = variant.get("inventory_quantity")
    if inv_mgmt and inv_qty is not None and inv_qty <= 0:
        availability = "out of stock"
    else:
        availability = "in stock"
    if is_poa(product):
        availability = "out of stock"  # no sellable price for Google Shopping
    desc = strip_html(product.get("body_html")) or product.get("title") or ""
    return {
        "id": str(variant.get("id") or product["id"]),
        "item_group_id": str(product["id"]),
        "title": product.get("title") or "",
        "description": desc,
        "link": f"{STOREFRONT_URL}/products/{product.get('handle')}",
        "image_link": image_link,
        "price": f"{price} GBP",
        "availability": availability,
        "brand": product.get("vendor") or "iComply Supplys",
        "condition": "new",
        "product_type": product.get("product_type") or "",
        "mpn": variant.get("sku") or "",
        "gtin": variant.get("barcode") or "",
        "handle": product.get("handle") or "",
        "requires_shipping": bool(variant.get("requires_shipping", True)),
    }


def main() -> None:
    started = datetime.now(timezone.utc).isoformat()
    store = get_store()
    print(f"Store: {store}")

    result: dict[str, Any] = {
        "started_at": started,
        "store": store,
        "storefront_url": STOREFRONT_URL,
        "tasks": {},
        "errors": [],
        "notes": [],
    }

    # --- 1. Publications ---
    print("Fetching publications...")
    pubs = fetch_publications()
    online_id, online_gid = resolve_online_store(pubs)
    result["tasks"]["publications"] = {
        "rest_publications": (pubs.get("rest") or {}).get("publications") or [],
        "graphql_publications": [
            e.get("node")
            for e in (
                ((pubs.get("graphql") or {}).get("data") or {})
                .get("publications", {})
                .get("edges")
                or []
            )
        ],
        "online_store_publication_id": online_id,
        "online_store_publication_gid": online_gid,
        "rest_error": pubs.get("rest_error"),
        "graphql_error": pubs.get("graphql_error"),
        "google_channel_present": False,
    }
    # Detect Google channel if already installed
    all_names = [
        (p.get("name") or "").lower()
        for p in result["tasks"]["publications"]["rest_publications"]
    ]
    all_names += [
        (p.get("name") or "").lower()
        for p in result["tasks"]["publications"]["graphql_publications"]
        if p
    ]
    google_names = [n for n in all_names if "google" in n or "youtube" in n]
    result["tasks"]["publications"]["google_channel_present"] = bool(google_names)
    result["tasks"]["publications"]["google_related_names"] = google_names
    if not online_gid:
        msg = "Online Store publication not found — cannot publish via API"
        result["errors"].append(msg)
        print(msg)
    else:
        print(f"Online Store: id={online_id} gid={online_gid}")

    # Shop snapshot
    try:
        shop = api("GET", "/shop.json").get("shop") or {}
        result["shop"] = {
            "name": shop.get("name"),
            "domain": shop.get("domain"),
            "myshopify_domain": shop.get("myshopify_domain"),
            "currency": shop.get("currency"),
            "plan_name": shop.get("plan_name"),
            "country_name": shop.get("country_name"),
            "has_storefront": shop.get("has_storefront"),
            "taxes_included": shop.get("taxes_included"),
        }
    except Exception as e:
        result["errors"].append(f"shop.json: {e}")

    # --- 2. Products: publish + tag ---
    print("Fetching products...")
    products = fetch_all_products()
    print(f"Total products: {len(products)}")

    tagged = []
    already_tagged = []
    skipped_non_physical = []
    skipped_poa = []
    vendor_fixed = []
    type_fixed = []
    published = []
    already_published = []
    publish_failed = []
    tag_failed = []

    # Spot-check Online Store publication on a sample (avoid N GraphQL calls)
    sample_pub_checked = 0
    sample_pub_ok = 0
    if online_gid:
        print("Spot-checking Online Store publication (up to 10 products)...")
        for p in products[:10]:
            product_gid = p.get("admin_graphql_api_id") or f"gid://shopify/Product/{p['id']}"
            sample_pub_checked += 1
            try:
                if product_published_to_online(product_gid, online_gid):
                    sample_pub_ok += 1
            except Exception as e:
                result["errors"].append(f"pub check {p.get('handle')}: {e}")

    for i, p in enumerate(products, 1):
        handle = p.get("handle") or str(p.get("id"))
        product_gid = p.get("admin_graphql_api_id") or f"gid://shopify/Product/{p['id']}"
        physical = is_physical(p)
        poa = is_poa(p)

        # Ensure vendor / product_type
        vendor = (p.get("vendor") or "").strip() or "iComply Supplys"
        ptype = (p.get("product_type") or "").strip() or "Trade Supply"
        need_vendor = not (p.get("vendor") or "").strip()
        need_type = not (p.get("product_type") or "").strip()

        tags_list = [t.strip() for t in (p.get("tags") or "").split(",") if t.strip()]
        tags_lower = {t.lower() for t in tags_list}
        need_google_tag = physical and not poa and "google-shopping" not in tags_lower

        if not physical:
            skipped_non_physical.append(handle)
        elif poa:
            skipped_poa.append(handle)
        elif "google-shopping" in tags_lower:
            already_tagged.append(handle)
        else:
            tags_list.append("google-shopping")

        # Fast publish path: published_at means listed on Online Store; only publish missing
        if online_gid:
            if p.get("published_at"):
                already_published.append(handle)
            else:
                try:
                    resp = publish_to_online(product_gid, online_gid)
                    errs = (
                        ((resp.get("data") or {}).get("publishablePublish") or {}).get(
                            "userErrors"
                        )
                        or []
                    )
                    if errs:
                        publish_failed.append({"handle": handle, "errors": errs})
                    else:
                        published.append(handle)
                        p["published_at"] = "now"
                except Exception as e:
                    publish_failed.append({"handle": handle, "errors": str(e)})

        # Apply tag / vendor / type updates
        if need_google_tag or need_vendor or need_type:
            new_tags = ", ".join(tags_list)
            try:
                update_product_tags_and_meta(p, new_tags, vendor, ptype)
                if need_google_tag:
                    tagged.append(handle)
                if need_vendor:
                    vendor_fixed.append(handle)
                if need_type:
                    type_fixed.append(handle)
                p["tags"] = new_tags
                p["vendor"] = vendor
                p["product_type"] = ptype
            except Exception as e:
                tag_failed.append({"handle": handle, "error": str(e)})
                result["errors"].append(f"update {handle}: {e}")

        if i % 50 == 0:
            print(f"  processed {i}/{len(products)}... tagged_this_run={len(tagged)}")

    result["tasks"]["online_store_sample_check"] = {
        "checked": sample_pub_checked,
        "published_ok": sample_pub_ok,
    }
    result["tasks"]["product_tagging"] = {
        "total_products": len(products),
        "physical_non_poa_eligible": len(products)
        - len(skipped_non_physical)
        - len(skipped_poa),
        "tagged_with_google_shopping": len(tagged),
        "already_had_tag": len(already_tagged),
        "skipped_non_physical": len(skipped_non_physical),
        "skipped_non_physical_handles": skipped_non_physical,
        "skipped_poa": len(skipped_poa),
        "skipped_poa_handles": skipped_poa,
        "vendor_fixed": vendor_fixed,
        "product_type_fixed": type_fixed,
        "tag_failures": tag_failed,
        "sample_tagged": tagged[:20],
    }

    result["tasks"]["online_store_publish"] = {
        "publication_id": online_id,
        "publication_gid": online_gid,
        "newly_published": len(published),
        "already_published_or_assumed": len(already_published),
        "publish_failures": publish_failed,
        "newly_published_handles_sample": published[:30],
    }

    # --- 3. Product feed sample (first 50) ---
    print("Building Google product feed sample (first 50)...")
    # Stable order by id
    ordered = sorted(products, key=lambda x: x.get("id") or 0)
    feed_items = [build_feed_item(p) for p in ordered[:50]]
    feed_doc = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "store": store,
        "storefront_base": STOREFRONT_URL,
        "note": (
            "Sample product feed snapshot for Google Merchant Center / free listings prep. "
            "Not a live feed URL. Fields map to Google product data specification basics. "
            "Full catalog should sync via Google & YouTube sales channel after OAuth connect."
        ),
        "count": len(feed_items),
        "products": feed_items,
    }
    CATALOG.mkdir(parents=True, exist_ok=True)
    FEED_PATH.write_text(json.dumps(feed_doc, indent=2), encoding="utf-8")
    result["tasks"]["product_feed_sample"] = {
        "path": str(FEED_PATH),
        "count": len(feed_items),
        "sample_ids": [x["id"] for x in feed_items[:5]],
    }
    print(f"Wrote {FEED_PATH} ({len(feed_items)} items)")

    # --- 4. Connection status notes ---
    result["tasks"]["connection_status"] = {
        "google_youtube_sales_channel": {
            "status": "not_connected_via_api",
            "reason": "Requires merchant Google login / OAuth in Shopify Admin. Cannot complete programmatically.",
            "admin_path": "Shopify Admin → Sales channels → Google & YouTube",
            "detected_in_publications": result["tasks"]["publications"]["google_channel_present"],
        },
        "google_analytics_4": {
            "status": "manual_admin_setup",
            "reason": "GA4 Measurement ID must be entered in Online Store preferences or via Google & YouTube channel.",
            "admin_path": "Shopify Admin → Online Store → Preferences → Google Analytics",
        },
        "google_merchant_center": {
            "status": "manual_connect",
            "reason": "Connect via Google & YouTube channel after Google account OAuth; free listings enabled in Merchant Center.",
            "admin_path": "Shopify Admin → Sales channels → Google & YouTube → Settings",
        },
        "google_pay": {
            "status": "depends_on_shopify_payments",
            "reason": "Google Pay is offered through Shopify Payments (or compatible providers). Enable in payment settings after Shopify Payments is active.",
            "admin_path": "Shopify Admin → Settings → Payments",
            "shop_plan": (result.get("shop") or {}).get("plan_name"),
            "currency": (result.get("shop") or {}).get("currency"),
        },
    }

    result["notes"].append(
        "OAuth-based Google connections (Google & YouTube, Merchant Center, GA4 linking) "
        "require the merchant's Google account and cannot be completed with Admin API alone."
    )
    result["notes"].append(
        "Products tagged google-shopping are non-POA physical (requires_shipping) items. "
        "Service packages (digital / no shipping) were excluded."
    )
    result["notes"].append(
        "See GOOGLE_SERVICES_SETUP.md for full Admin step-by-step instructions."
    )

    result["finished_at"] = datetime.now(timezone.utc).isoformat()
    result["success"] = len(result["errors"]) == 0 and len(publish_failed) == 0 and len(tag_failed) == 0

    RESULT_PATH.write_text(json.dumps(result, indent=2), encoding="utf-8")
    print(f"Wrote {RESULT_PATH}")
    print(
        f"Tagged: {len(tagged)} | Already tagged: {len(already_tagged)} | "
        f"Skipped non-physical: {len(skipped_non_physical)} | "
        f"Published newly: {len(published)} | Failures: {len(tag_failed)+len(publish_failed)}"
    )


if __name__ == "__main__":
    main()
