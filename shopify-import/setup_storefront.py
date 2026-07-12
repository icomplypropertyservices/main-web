#!/usr/bin/env python3
"""Configure Shopify storefront content for iComply Supplys.

Reads optional JSON from storefront/ when present:
  - pages.json
  - policies.json
  - navigation.json
  - shipping_notes.json

Missing files are skipped gracefully. Results written to setup_storefront_result.json.
"""

from __future__ import annotations

import json
import sys
import traceback
from pathlib import Path
from typing import Any

from shopify_client import api, graphql

ROOT = Path(__file__).parent
STOREFRONT = ROOT / "storefront"
RESULT_PATH = ROOT / "setup_storefront_result.json"
MENU_MANUAL_PATH = ROOT / "MENU_MANUAL.md"

# Shop contact defaults (Stockport, UK)
SHOP_PHONE = "07517806082"
SHOP_EMAIL = "sales@icomplysupplys.co.uk"  # best-effort; only set if API allows
SHOP_ADDRESS = {
    "city": "Stockport",
    "province": "Greater Manchester",
    "country": "GB",
    "country_code": "GB",
    "zip": "SK1",
}

POLICY_HANDLES = {
    "privacyPolicy": "privacy-policy",
    "refundPolicy": "refund-policy",
    "shippingPolicy": "shipping-policy",
    "termsOfService": "terms-of-service",
}

POLICY_TITLES = {
    "privacy-policy": "Privacy Policy",
    "refund-policy": "Refund Policy",
    "shipping-policy": "Shipping Policy",
    "terms-of-service": "Terms of Service",
}

# GraphQL shopPolicyUpdate type enum values
POLICY_GQL_TYPES = {
    "privacyPolicy": "PRIVACY_POLICY",
    "refundPolicy": "REFUND_POLICY",
    "shippingPolicy": "SHIPPING_POLICY",
    "termsOfService": "TERMS_OF_SERVICE",
}


def load_json(path: Path) -> Any | None:
    if not path.exists():
        print(f"  skip (missing): {path.name}")
        return None
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
        print(f"  loaded: {path.name}")
        return data
    except Exception as ex:
        print(f"  warn: failed to parse {path.name}: {ex}")
        return None


def existing_pages_by_handle() -> dict[str, dict]:
    out: dict[str, dict] = {}
    try:
        data = api("GET", "/pages.json?limit=250")
        for p in data.get("pages", []):
            out[p["handle"]] = p
    except Exception as ex:
        print(f"  warn: could not list pages: {ex}")
    return out


def upsert_page(
    handle: str,
    title: str,
    body_html: str,
    existing: dict[str, dict],
    published: bool = True,
) -> dict:
    """Create or update a page by handle. Returns result dict."""
    payload = {
        "title": title,
        "handle": handle,
        "body_html": body_html,
        "published": published,
    }
    if handle in existing:
        pid = existing[handle]["id"]
        updated = api(
            "PUT",
            f"/pages/{pid}.json",
            {"page": {"id": pid, **payload}},
        ).get("page", {})
        print(f"  updated page handle={handle} id={pid}")
        return {
            "action": "updated",
            "handle": handle,
            "id": updated.get("id", pid),
            "title": title,
        }
    created = api("POST", "/pages.json", {"page": payload}).get("page", {})
    pid = created.get("id")
    if pid:
        existing[handle] = created
    print(f"  created page handle={handle} id={pid}")
    return {
        "action": "created",
        "handle": handle,
        "id": pid,
        "title": title,
    }


def setup_pages(result: dict) -> None:
    print("\n== Pages ==")
    pages_data = load_json(STOREFRONT / "pages.json")
    if pages_data is None:
        result["pages"] = {"status": "skipped", "reason": "pages.json not found"}
        return

    items = pages_data if isinstance(pages_data, list) else pages_data.get("pages", [])
    existing = existing_pages_by_handle()
    page_results: list[dict] = []
    for item in items:
        handle = item.get("handle") or _slug(item.get("title", "page"))
        title = item.get("title") or handle
        body = item.get("body_html") or item.get("body") or item.get("content") or ""
        try:
            page_results.append(
                upsert_page(handle, title, body, existing, item.get("published", True))
            )
        except Exception as ex:
            print(f"  error page {handle}: {ex}")
            page_results.append(
                {"action": "error", "handle": handle, "title": title, "error": str(ex)}
            )
    result["pages"] = {"status": "ok", "items": page_results}


def _slug(text: str) -> str:
    s = text.lower().strip()
    out = []
    for ch in s:
        if ch.isalnum():
            out.append(ch)
        elif ch in (" ", "-", "_", "/"):
            out.append("-")
    slug = "".join(out)
    while "--" in slug:
        slug = slug.replace("--", "-")
    return slug.strip("-") or "page"


def setup_policies(result: dict) -> None:
    print("\n== Policies ==")
    policies_data = load_json(STOREFRONT / "policies.json")
    if policies_data is None:
        result["policies"] = {"status": "skipped", "reason": "policies.json not found"}
        return

    # Normalise: accept {"privacyPolicy": {...}} or list of {type,title,body}
    policies: dict[str, dict] = {}
    if isinstance(policies_data, list):
        for item in policies_data:
            key = item.get("type") or item.get("key") or item.get("handle")
            if key:
                policies[key] = item
    elif isinstance(policies_data, dict):
        if "policies" in policies_data and isinstance(policies_data["policies"], list):
            for item in policies_data["policies"]:
                key = item.get("type") or item.get("key") or item.get("handle")
                if key:
                    policies[key] = item
        else:
            policies = policies_data
    else:
        result["policies"] = {"status": "error", "reason": "unrecognised policies.json shape"}
        return

    policy_results: list[dict] = []
    existing = existing_pages_by_handle()
    shop_policy_ok = False

    for key, handle in POLICY_HANDLES.items():
        # Resolve content under several possible keys
        item = (
            policies.get(key)
            or policies.get(handle)
            or policies.get(key.replace("Policy", "_policy").lower())
            or policies.get(handle.replace("-", "_"))
        )
        if not item:
            # Also try camelCase / snake variants from list normalisation
            for k, v in policies.items():
                kl = k.lower().replace("_", "").replace("-", "")
                if kl in (key.lower(), handle.replace("-", "")):
                    item = v
                    break
        if not item:
            policy_results.append(
                {"key": key, "handle": handle, "action": "skipped", "reason": "not in policies.json"}
            )
            continue

        # policies.json may store plain HTML strings or {title, body_html} objects
        if isinstance(item, str):
            title = POLICY_TITLES.get(handle, handle.replace("-", " ").title())
            body = item
        else:
            title = (
                item.get("title")
                or POLICY_TITLES.get(handle, handle.replace("-", " ").title())
            )
            body = item.get("body_html") or item.get("body") or item.get("content") or ""
        if not body:
            policy_results.append(
                {"key": key, "handle": handle, "action": "skipped", "reason": "empty body"}
            )
            continue

        updated_via = None
        err_msgs: list[str] = []

        # 1) GraphQL shopPolicyUpdate
        gql_type = POLICY_GQL_TYPES.get(key)
        if gql_type:
            try:
                g = graphql(
                    """
                    mutation shopPolicyUpdate($shopPolicy: ShopPolicyInput!) {
                      shopPolicyUpdate(shopPolicy: $shopPolicy) {
                        shopPolicy { type body title url }
                        userErrors { field message }
                      }
                    }
                    """,
                    {
                        "shopPolicy": {
                            "type": gql_type,
                            "body": body,
                        }
                    },
                )
                payload = (g.get("data") or {}).get("shopPolicyUpdate") or {}
                user_errors = payload.get("userErrors") or []
                if user_errors:
                    err_msgs.append(f"GQL userErrors: {user_errors}")
                else:
                    updated_via = "graphql_shopPolicyUpdate"
                    shop_policy_ok = True
                    print(f"  updated shop policy via GraphQL: {gql_type}")
            except Exception as ex:
                err_msgs.append(f"GQL: {ex}")

        # 2) REST policies endpoint (legacy / limited)
        if not updated_via:
            try:
                # Shopify REST: GET /admin/api/.../policies.json (read-only on many plans)
                # Attempt PUT if supported
                rest_key = {
                    "privacyPolicy": "privacy_policy",
                    "refundPolicy": "refund_policy",
                    "shippingPolicy": "shipping_policy",
                    "termsOfService": "terms_of_service",
                }.get(key, handle.replace("-", "_"))
                api(
                    "PUT",
                    f"/policies/{rest_key}.json",
                    {"policy": {"title": title, "body": body}},
                )
                updated_via = "rest_policies"
                shop_policy_ok = True
                print(f"  updated shop policy via REST: {rest_key}")
            except Exception as ex:
                err_msgs.append(f"REST: {ex}")

        # 3) Fallback: create/update as regular pages
        if not updated_via:
            try:
                pr = upsert_page(handle, title, body, existing)
                updated_via = f"page_fallback:{pr['action']}"
                print(f"  fallback page for policy {handle}")
            except Exception as ex:
                err_msgs.append(f"page: {ex}")
                policy_results.append(
                    {
                        "key": key,
                        "handle": handle,
                        "action": "error",
                        "errors": err_msgs,
                    }
                )
                continue

        policy_results.append(
            {
                "key": key,
                "handle": handle,
                "title": title,
                "action": "updated",
                "via": updated_via,
                "notes": err_msgs or None,
            }
        )

    result["policies"] = {
        "status": "ok",
        "shop_policy_api": shop_policy_ok,
        "items": policy_results,
    }


def setup_metafields_and_shipping(result: dict) -> None:
    print("\n== Metafields / shipping notes ==")
    shipping = load_json(STOREFRONT / "shipping_notes.json")
    meta_src = load_json(STOREFRONT / "metafields.json")

    meta_results: list[dict] = []

    # Print shipping notes always when present
    if shipping is not None:
        print("  shipping_notes.json contents:")
        print(json.dumps(shipping, indent=2)[:4000])
        result["shipping_notes"] = {
            "status": "printed",
            "data": shipping,
        }
    else:
        result["shipping_notes"] = {"status": "skipped", "reason": "shipping_notes.json not found"}

    # Build metafield list from metafields.json and/or shipping_notes
    candidates: list[dict] = []
    if meta_src is not None:
        if isinstance(meta_src, list):
            candidates.extend(meta_src)
        elif isinstance(meta_src, dict):
            candidates.extend(meta_src.get("metafields") or [])
            # Also accept flat namespace.key maps under shop
            shop_map = meta_src.get("shop")
            if isinstance(shop_map, dict):
                for k, v in shop_map.items():
                    if "." in k:
                        ns, key = k.split(".", 1)
                    else:
                        ns, key = "icomply", k
                    candidates.append(
                        {
                            "namespace": ns,
                            "key": key,
                            "value": v if isinstance(v, str) else json.dumps(v),
                            "type": "single_line_text_field"
                            if isinstance(v, str)
                            else "json",
                        }
                    )

    if shipping is not None:
        # Always try to store shipping notes as a shop metafield
        candidates.append(
            {
                "namespace": "icomply",
                "key": "shipping_notes",
                "value": json.dumps(shipping)
                if not isinstance(shipping, str)
                else shipping,
                "type": "json",
            }
        )
        # Common free-text keys
        if isinstance(shipping, dict):
            for k in ("summary", "notes", "delivery", "cutoff"):
                if k in shipping and shipping[k]:
                    candidates.append(
                        {
                            "namespace": "icomply",
                            "key": f"shipping_{k}",
                            "value": str(shipping[k]),
                            "type": "multi_line_text_field"
                            if "\n" in str(shipping[k])
                            else "single_line_text_field",
                        }
                    )

    if not candidates:
        result["metafields"] = {
            "status": "skipped",
            "reason": "no metafield sources",
        }
        return

    # Resolve shop GID / id for metafield owner
    shop_id = None
    try:
        shop = api("GET", "/shop.json").get("shop", {})
        shop_id = shop.get("id")
    except Exception as ex:
        print(f"  warn: could not fetch shop: {ex}")

    for mf in candidates:
        ns = mf.get("namespace", "icomply")
        key = mf.get("key")
        value = mf.get("value", "")
        mtype = mf.get("type", "single_line_text_field")
        if not key:
            continue
        if value is not None and not isinstance(value, str):
            value = json.dumps(value)
            if mtype == "single_line_text_field":
                mtype = "json"

        ok = False
        err = None

        # GraphQL metafieldsSet
        if shop_id:
            try:
                g = graphql(
                    """
                    mutation metafieldsSet($metafields: [MetafieldsSetInput!]!) {
                      metafieldsSet(metafields: $metafields) {
                        metafields { id namespace key value }
                        userErrors { field message }
                      }
                    }
                    """,
                    {
                        "metafields": [
                            {
                                "ownerId": f"gid://shopify/Shop/{shop_id}",
                                "namespace": ns,
                                "key": key,
                                "type": mtype,
                                "value": value,
                            }
                        ]
                    },
                )
                payload = (g.get("data") or {}).get("metafieldsSet") or {}
                uerr = payload.get("userErrors") or []
                if uerr:
                    err = str(uerr)
                else:
                    ok = True
                    print(f"  metafield set (GQL): {ns}.{key}")
            except Exception as ex:
                err = f"GQL: {ex}"

        # REST fallback
        if not ok and shop_id:
            try:
                api(
                    "POST",
                    f"/metafields.json",
                    {
                        "metafield": {
                            "namespace": ns,
                            "key": key,
                            "type": mtype,
                            "value": value,
                            "owner_resource": "shop",
                            "owner_id": shop_id,
                        }
                    },
                )
                ok = True
                err = None
                print(f"  metafield set (REST): {ns}.{key}")
            except Exception as ex:
                err = f"{err}; REST: {ex}" if err else f"REST: {ex}"

        meta_results.append(
            {
                "namespace": ns,
                "key": key,
                "action": "set" if ok else "error",
                "error": err,
            }
        )
        if not ok:
            print(f"  metafield fail {ns}.{key}: {err}")

    result["metafields"] = {"status": "ok", "items": meta_results}


def setup_shop_details(result: dict) -> None:
    print("\n== Shop details ==")
    shop_info: dict[str, Any] = {"status": "unknown"}
    try:
        shop = api("GET", "/shop.json").get("shop", {})
        shop_info["before"] = {
            "name": shop.get("name"),
            "email": shop.get("email"),
            "phone": shop.get("phone"),
            "city": shop.get("city"),
            "province": shop.get("province"),
            "country": shop.get("country"),
            "address1": shop.get("address1"),
            "zip": shop.get("zip"),
        }
        print(
            f"  current: email={shop.get('email')} phone={shop.get('phone')} "
            f"city={shop.get('city')} country={shop.get('country')}"
        )
    except Exception as ex:
        result["shop_details"] = {"status": "error", "error": f"GET shop: {ex}"}
        print(f"  error fetching shop: {ex}")
        return

    # Carefully update only contact fields that are empty or clearly placeholder
    update: dict[str, Any] = {}
    current_phone = (shop.get("phone") or "").strip()
    current_email = (shop.get("email") or "").strip()
    current_city = (shop.get("city") or "").strip()

    # Phone: set if missing or not already our number
    norm_current = "".join(ch for ch in current_phone if ch.isdigit())
    norm_target = "".join(ch for ch in SHOP_PHONE if ch.isdigit())
    if not norm_current or norm_current != norm_target:
        # Prefer +44 format for UK storefronts when setting
        update["phone"] = SHOP_PHONE

    # Address: only fill city/province/country if city empty or not Stockport
    if not current_city or current_city.lower() != "stockport":
        update["city"] = SHOP_ADDRESS["city"]
        update["province"] = SHOP_ADDRESS["province"]
        update["country"] = SHOP_ADDRESS["country"]
        update["country_code"] = SHOP_ADDRESS["country_code"]
        # Do not overwrite street/zip aggressively if already set
        if not (shop.get("zip") or "").strip():
            update["zip"] = SHOP_ADDRESS["zip"]

    # Email: only set if empty (avoid breaking merchant login/notifications)
    if not current_email and SHOP_EMAIL:
        update["email"] = SHOP_EMAIL

    if not update:
        shop_info["status"] = "unchanged"
        shop_info["message"] = "Shop contact already set; no careful updates needed"
        print("  no shop field updates required")
        result["shop_details"] = shop_info
        return

    shop_info["attempted"] = update
    try:
        updated = api(
            "PUT",
            "/shop.json",
            {"shop": update},
        ).get("shop", {})
        shop_info["status"] = "updated"
        shop_info["after"] = {
            "email": updated.get("email"),
            "phone": updated.get("phone"),
            "city": updated.get("city"),
            "province": updated.get("province"),
            "country": updated.get("country"),
            "zip": updated.get("zip"),
        }
        print(f"  updated shop fields: {list(update.keys())}")
    except Exception as ex:
        # Many apps cannot write shop settings — record and continue
        shop_info["status"] = "failed"
        shop_info["error"] = str(ex)
        shop_info["manual"] = {
            "phone": SHOP_PHONE,
            "city": "Stockport",
            "province": "Greater Manchester",
            "country": "United Kingdom",
            "email_hint": SHOP_EMAIL,
            "admin_path": "Settings → Store details",
        }
        print(f"  shop update not permitted or failed: {ex}")
        print("  set manually: Settings → Store details (phone 07517806082, Stockport)")

    result["shop_details"] = shop_info


def _resolve_menu_item_resource(item: dict) -> dict | None:
    """Build a MenuItemCreateInput resource fragment if possible."""
    itype = (item.get("type") or item.get("resource_type") or "HTTP").upper()
    handle = item.get("handle") or item.get("resource_handle")
    url = item.get("url") or item.get("href")

    if itype in ("COLLECTION", "COLLECTIONS") and handle:
        try:
            data = api("GET", f"/custom_collections.json?handle={handle}")
            cols = data.get("custom_collections") or []
            if not cols:
                data = api("GET", f"/smart_collections.json?handle={handle}")
                cols = data.get("smart_collections") or []
            if cols:
                return {
                    "type": "COLLECTION",
                    "resourceId": f"gid://shopify/Collection/{cols[0]['id']}",
                }
        except Exception as ex:
            print(f"  warn resolve collection {handle}: {ex}")
        # Fallback HTTP path
        return {"type": "HTTP", "url": f"/collections/{handle}"}

    if itype in ("PAGE", "PAGES") and handle:
        try:
            pages = existing_pages_by_handle()
            if handle in pages:
                return {
                    "type": "PAGE",
                    "resourceId": f"gid://shopify/Page/{pages[handle]['id']}",
                }
        except Exception as ex:
            print(f"  warn resolve page {handle}: {ex}")
        return {"type": "HTTP", "url": f"/pages/{handle}"}

    if itype in ("PRODUCT", "PRODUCTS") and handle:
        try:
            data = api("GET", f"/products.json?handle={handle}")
            prods = data.get("products") or []
            if prods:
                return {
                    "type": "PRODUCT",
                    "resourceId": f"gid://shopify/Product/{prods[0]['id']}",
                }
        except Exception as ex:
            print(f"  warn resolve product {handle}: {ex}")
        return {"type": "HTTP", "url": f"/products/{handle}"}

    if itype in ("FRONTPAGE", "HOME", "SHOP"):
        return {"type": "FRONTPAGE"}

    if itype in ("CATALOG", "ALL_PRODUCTS"):
        return {"type": "CATALOG"}

    if itype in ("COLLECTIONS", "COLLECTION_LIST"):
        return {"type": "COLLECTIONS"}

    if url:
        return {"type": "HTTP", "url": url}

    if handle:
        return {"type": "HTTP", "url": f"/{handle}"}

    return None


def _default_navigation() -> list[dict]:
    """Sensible main-menu defaults when navigation.json is absent or empty."""
    return [
        {"title": "Home", "type": "FRONTPAGE"},
        {"title": "All Products", "type": "CATALOG"},
        {"title": "Service Packages", "type": "COLLECTION", "handle": "service-packages"},
        {"title": "Contact", "type": "PAGE", "handle": "contact"},
        {"title": "About", "type": "PAGE", "handle": "about"},
    ]


def setup_navigation(result: dict) -> None:
    print("\n== Navigation / menus ==")
    nav_data = load_json(STOREFRONT / "navigation.json")

    if nav_data is None:
        items_src = _default_navigation()
        menu_title = "Main menu"
        menu_handle = "main-menu"
        result_note = "navigation.json missing; using defaults"
        print(f"  {result_note}")
    else:
        if isinstance(nav_data, list):
            items_src = nav_data
            menu_title = "Main menu"
            menu_handle = "main-menu"
        else:
            items_src = (
                nav_data.get("items")
                or nav_data.get("links")
                or nav_data.get("main_menu")
                or nav_data.get("main-menu")
                or []
            )
            menu_title = nav_data.get("title") or "Main menu"
            menu_handle = nav_data.get("handle") or "main-menu"
            # nested menus map
            if not items_src and isinstance(nav_data.get("menus"), dict):
                main = nav_data["menus"].get("main-menu") or nav_data["menus"].get("main")
                if isinstance(main, dict):
                    items_src = main.get("items") or main.get("links") or []
                    menu_title = main.get("title") or menu_title
                    menu_handle = main.get("handle") or menu_handle
                elif isinstance(main, list):
                    items_src = main

    menu_items: list[dict] = []
    for raw in items_src:
        title = raw.get("title") or raw.get("name") or "Link"
        resource = _resolve_menu_item_resource(raw)
        if not resource:
            print(f"  skip menu item (unresolved): {title}")
            continue
        entry: dict[str, Any] = {"title": title, **resource}
        # Nested items (one level)
        children = raw.get("items") or raw.get("links") or raw.get("children") or []
        if children:
            nested = []
            for ch in children:
                ct = ch.get("title") or ch.get("name") or "Link"
                cr = _resolve_menu_item_resource(ch)
                if cr:
                    nested.append({"title": ct, **cr})
            if nested:
                entry["items"] = nested
        menu_items.append(entry)

    nav_result: dict[str, Any] = {
        "handle": menu_handle,
        "title": menu_title,
        "item_count": len(menu_items),
        "items_preview": [
            {"title": i.get("title"), "type": i.get("type"), "url": i.get("url")}
            for i in menu_items
        ],
    }

    if not menu_items:
        nav_result["status"] = "empty"
        write_menu_manual(menu_title, menu_handle, items_src or _default_navigation())
        nav_result["manual"] = str(MENU_MANUAL_PATH)
        result["navigation"] = nav_result
        return

    # List existing menus
    existing_menu_id = None
    try:
        g = graphql(
            """
            query {
              menus(first: 50) {
                nodes { id handle title items { title type url } }
              }
            }
            """
        )
        nodes = ((g.get("data") or {}).get("menus") or {}).get("nodes") or []
        for n in nodes:
            if n.get("handle") == menu_handle or n.get("handle") == "main-menu":
                existing_menu_id = n.get("id")
                print(f"  found existing menu {n.get('handle')} id={existing_menu_id}")
                break
        nav_result["existing_menus"] = [
            {"id": n.get("id"), "handle": n.get("handle"), "title": n.get("title")}
            for n in nodes
        ]
    except Exception as ex:
        print(f"  warn: menus query failed: {ex}")
        nav_result["menus_query_error"] = str(ex)

    # Try menuUpdate / menuCreate
    try:
        if existing_menu_id:
            g = graphql(
                """
                mutation menuUpdate($id: ID!, $title: String!, $items: [MenuItemUpdateInput!]!) {
                  menuUpdate(id: $id, title: $title, items: $items) {
                    menu { id handle title }
                    userErrors { field message }
                  }
                }
                """,
                {
                    "id": existing_menu_id,
                    "title": menu_title,
                    "items": _to_update_items(menu_items),
                },
            )
            payload = (g.get("data") or {}).get("menuUpdate") or {}
            uerr = payload.get("userErrors") or []
            if uerr:
                raise RuntimeError(f"menuUpdate userErrors: {uerr}")
            nav_result["status"] = "updated"
            nav_result["menu"] = payload.get("menu")
            print(f"  menuUpdate OK: {menu_handle}")
        else:
            g = graphql(
                """
                mutation menuCreate($title: String!, $handle: String!, $items: [MenuItemCreateInput!]!) {
                  menuCreate(title: $title, handle: $handle, items: $items) {
                    menu { id handle title }
                    userErrors { field message }
                  }
                }
                """,
                {
                    "title": menu_title,
                    "handle": menu_handle,
                    "items": _to_create_items(menu_items),
                },
            )
            payload = (g.get("data") or {}).get("menuCreate") or {}
            uerr = payload.get("userErrors") or []
            if uerr:
                raise RuntimeError(f"menuCreate userErrors: {uerr}")
            nav_result["status"] = "created"
            nav_result["menu"] = payload.get("menu")
            print(f"  menuCreate OK: {menu_handle}")
    except Exception as ex:
        print(f"  menu API insufficient or failed: {ex}")
        nav_result["status"] = "manual_required"
        nav_result["error"] = str(ex)
        write_menu_manual(menu_title, menu_handle, items_src or menu_items)
        nav_result["manual"] = str(MENU_MANUAL_PATH)
        print(f"  wrote {MENU_MANUAL_PATH}")

    result["navigation"] = nav_result


def _to_create_items(items: list[dict]) -> list[dict]:
    out = []
    for i in items:
        entry: dict[str, Any] = {"title": i["title"], "type": i["type"]}
        if i.get("url"):
            entry["url"] = i["url"]
        if i.get("resourceId"):
            entry["resourceId"] = i["resourceId"]
        if i.get("items"):
            entry["items"] = _to_create_items(i["items"])
        out.append(entry)
    return out


def _to_update_items(items: list[dict]) -> list[dict]:
    # MenuItemUpdateInput is same shape as create for full replace
    return _to_create_items(items)


def write_menu_manual(title: str, handle: str, items: list[dict]) -> None:
    lines = [
        "# Main menu — manual setup (Shopify Admin)",
        "",
        "The Admin API could not fully create/update the online store menu "
        "(scope, plan, or mutation limitations). Follow these exact clicks:",
        "",
        "## Path",
        "",
        "1. Open **Shopify Admin** → **Online Store** → **Navigation**",
        f"2. Click the menu named **{title}** (handle: `{handle}`), or **Add menu** if missing",
        "3. If creating new: Title = `Main menu`, then save",
        "4. Remove any placeholder links you do not need",
        "5. Add each item below with **Add menu item**",
        "6. Click **Save menu**",
        "7. Confirm your theme uses this menu: **Online Store** → **Themes** → **Customize** → Header → Menu = Main menu",
        "",
        "## Menu items (exact)",
        "",
    ]
    for idx, raw in enumerate(items, 1):
        item_title = raw.get("title") or raw.get("name") or f"Item {idx}"
        itype = (raw.get("type") or raw.get("resource_type") or "link").upper()
        handle_r = raw.get("handle") or raw.get("resource_handle") or ""
        url = raw.get("url") or raw.get("href") or ""

        lines.append(f"### {idx}. {item_title}")
        lines.append("")
        lines.append(f"- **Name:** `{item_title}`")
        if itype in ("COLLECTION", "COLLECTIONS") and handle_r:
            lines.append(
                f"- **Link:** start typing collection name, pick collection with handle `{handle_r}` "
                f"(or paste `/collections/{handle_r}`)"
            )
        elif itype in ("PAGE", "PAGES") and handle_r:
            lines.append(
                f"- **Link:** Pages → select page handle `{handle_r}` "
                f"(or paste `/pages/{handle_r}`)"
            )
        elif itype in ("PRODUCT", "PRODUCTS") and handle_r:
            lines.append(
                f"- **Link:** Products → select product handle `{handle_r}` "
                f"(or paste `/products/{handle_r}`)"
            )
        elif itype in ("FRONTPAGE", "HOME", "SHOP"):
            lines.append("- **Link:** Home page (Frontpage)")
        elif itype in ("CATALOG", "ALL_PRODUCTS"):
            lines.append("- **Link:** All products / Catalog")
        elif itype in ("COLLECTIONS", "COLLECTION_LIST"):
            lines.append("- **Link:** All collections")
        elif url:
            lines.append(f"- **Link:** paste URL `{url}`")
        elif handle_r:
            lines.append(f"- **Link:** paste path `/{handle_r}`")
        else:
            lines.append("- **Link:** (set destination in admin)")
        lines.append("")

        children = raw.get("items") or raw.get("links") or raw.get("children") or []
        for j, ch in enumerate(children, 1):
            ct = ch.get("title") or ch.get("name") or f"Nested {j}"
            ch_handle = ch.get("handle") or ""
            ch_type = (ch.get("type") or "HTTP").upper()
            lines.append(f"  - Nested **{ct}** ({ch_type}" + (f" `{ch_handle}`" if ch_handle else "") + ")")
        if children:
            lines.append("")

    lines.extend(
        [
            "## Footer menu (recommended)",
            "",
            "Also under **Online Store** → **Navigation** → **Footer menu**, add:",
            "",
            "- Privacy Policy → `/policies/privacy-policy` or page `privacy-policy`",
            "- Refund Policy → `/policies/refund-policy` or page `refund-policy`",
            "- Shipping Policy → `/policies/shipping-policy` or page `shipping-policy`",
            "- Terms of Service → `/policies/terms-of-service` or page `terms-of-service`",
            "- Contact → page `contact` (if created)",
            "",
            "## Theme assignment",
            "",
            "1. **Online Store** → **Themes** → **Customize**",
            "2. Open **Header** section → set **Menu** to **Main menu**",
            "3. Open **Footer** section → set **Menu** to **Footer menu**",
            "4. **Save**",
            "",
        ]
    )
    MENU_MANUAL_PATH.write_text("\n".join(lines), encoding="utf-8")


def main() -> int:
    print("iComply Supplys — storefront setup")
    print(f"storefront dir: {STOREFRONT} (exists={STOREFRONT.exists()})")

    result: dict[str, Any] = {
        "store": "icomply-supplys.myshopify.com",
        "storefront_dir": str(STOREFRONT),
        "storefront_dir_exists": STOREFRONT.exists(),
        "files_present": {},
    }

    for name in (
        "pages.json",
        "policies.json",
        "navigation.json",
        "shipping_notes.json",
        "metafields.json",
    ):
        result["files_present"][name] = (STOREFRONT / name).exists()

    try:
        setup_pages(result)
        setup_policies(result)
        setup_metafields_and_shipping(result)
        setup_shop_details(result)
        setup_navigation(result)
        result["status"] = "completed"
    except Exception as ex:
        result["status"] = "failed"
        result["fatal_error"] = str(ex)
        result["traceback"] = traceback.format_exc()
        print(f"\nFATAL: {ex}")
        traceback.print_exc()

    RESULT_PATH.write_text(json.dumps(result, indent=2, default=str), encoding="utf-8")
    print(f"\nWrote {RESULT_PATH}")
    return 0 if result.get("status") == "completed" else 1


if __name__ == "__main__":
    sys.exit(main())
