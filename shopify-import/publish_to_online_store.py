#!/usr/bin/env python3
"""Publish all active products and collections to Online Store sales channel."""

from __future__ import annotations

import json
from pathlib import Path

from shopify_client import api, graphql

OUT = Path(__file__).parent / "publish_result.json"

PUBLISH_MUTATION = """
mutation publishablePublish($id: ID!, $input: [PublicationInput!]!) {
  publishablePublish(id: $id, input: $input) {
    publishable {
      availablePublicationsCount {
        count
      }
    }
    userErrors {
      field
      message
    }
  }
}
"""


def list_publications() -> list[dict]:
    data = graphql(
        """
        {
          publications(first: 25) {
            edges {
              node {
                id
                name
                supportsFuturePublishing
              }
            }
          }
        }
        """
    )
    return [e["node"] for e in data["data"]["publications"]["edges"]]


def find_online_store(publications: list[dict]) -> dict:
    online = next((p for p in publications if p["name"].lower() == "online store"), None)
    if not online:
        online = next((p for p in publications if "online" in p["name"].lower()), None)
    if not online:
        raise RuntimeError("Online Store publication not found")
    return online


def publish_resource(gid: str, publication_id: str) -> dict:
    data = graphql(
        PUBLISH_MUTATION,
        {"id": gid, "input": [{"publicationId": publication_id}]},
    )
    result = data["data"]["publishablePublish"]
    errors = result.get("userErrors") or []
    return {"ok": len(errors) == 0, "errors": errors, "raw": result}


def fetch_all_active_products() -> list[dict]:
    products: list[dict] = []
    since_id = 0
    while True:
        path = (
            f"/products.json?limit=250&status=active"
            f"&fields=id,title,handle,status&since_id={since_id}"
        )
        batch = api("GET", path).get("products", [])
        if not batch:
            break
        products.extend(batch)
        since_id = batch[-1]["id"]
        print(f"  fetched {len(products)} so far (last id={since_id})")
        if len(batch) < 250:
            break
    return products


def fetch_all_collections() -> list[dict]:
    collections: list[dict] = []
    for c in api("GET", "/custom_collections.json?limit=250").get(
        "custom_collections", []
    ):
        collections.append({**c, "kind": "custom"})
    for c in api("GET", "/smart_collections.json?limit=250").get(
        "smart_collections", []
    ):
        collections.append({**c, "kind": "smart"})
    return collections


def rest_publish_collection(c: dict) -> None:
    if c["kind"] == "custom":
        api(
            "PUT",
            f"/custom_collections/{c['id']}.json",
            {"custom_collection": {"id": c["id"], "published": True}},
        )
    else:
        api(
            "PUT",
            f"/smart_collections/{c['id']}.json",
            {"smart_collection": {"id": c["id"], "published": True}},
        )


def main() -> None:
    print("=== 1. LIST PUBLICATIONS ===")
    publications = list_publications()
    for p in publications:
        print(f"  {p['name']}: {p['id']}")

    print("\n=== 2. ONLINE STORE PUBLICATION ===")
    online = find_online_store(publications)
    publication_id = online["id"]
    print(f"  id={publication_id}")

    print("\n=== 3. FETCH ACTIVE PRODUCTS ===")
    products = fetch_all_active_products()
    print(f"  TOTAL active products: {len(products)}")

    print("\n=== 3b. PUBLISH PRODUCTS TO ONLINE STORE ===")
    product_results: list[dict] = []
    product_ok = 0
    product_fail = 0
    product_already = 0

    for i, p in enumerate(products, 1):
        gid = f"gid://shopify/Product/{p['id']}"
        try:
            r = publish_resource(gid, publication_id)
            if r["ok"]:
                product_ok += 1
                status = "published"
            else:
                msgs = [e.get("message", "") for e in r["errors"]]
                if any("already" in m.lower() for m in msgs):
                    product_already += 1
                    product_ok += 1
                    status = "already_published"
                else:
                    product_fail += 1
                    status = "error"
            product_results.append(
                {
                    "id": p["id"],
                    "handle": p["handle"],
                    "title": p["title"],
                    "gid": gid,
                    "status": status,
                    "errors": r.get("errors") or [],
                }
            )
        except Exception as ex:
            product_fail += 1
            product_results.append(
                {
                    "id": p["id"],
                    "handle": p["handle"],
                    "title": p["title"],
                    "gid": gid,
                    "status": "exception",
                    "errors": [str(ex)],
                }
            )
        if i % 25 == 0 or i == len(products):
            print(f"  products {i}/{len(products)} ok={product_ok} fail={product_fail}")

    print("\n=== 4. FETCH & PUBLISH COLLECTIONS ===")
    collections = fetch_all_collections()
    print(f"  TOTAL collections: {len(collections)}")

    collection_results: list[dict] = []
    collection_ok = 0
    collection_fail = 0

    for c in collections:
        gid = f"gid://shopify/Collection/{c['id']}"
        entry: dict = {
            "id": c["id"],
            "handle": c["handle"],
            "title": c.get("title"),
            "kind": c["kind"],
            "gid": gid,
            "published_at_before": c.get("published_at"),
        }
        try:
            r = publish_resource(gid, publication_id)
            if r["ok"]:
                collection_ok += 1
                entry["status"] = "published"
                entry["errors"] = []
            else:
                msgs = [e.get("message", "") for e in r["errors"]]
                if any("already" in m.lower() for m in msgs):
                    collection_ok += 1
                    entry["status"] = "already_published"
                    entry["errors"] = r["errors"]
                else:
                    try:
                        rest_publish_collection(c)
                        collection_ok += 1
                        entry["status"] = "published_via_rest"
                        entry["errors"] = r["errors"]
                    except Exception as ex2:
                        collection_fail += 1
                        entry["status"] = "error"
                        entry["errors"] = list(r["errors"]) + [{"message": str(ex2)}]
            print(f"  collection {c['handle']}: {entry['status']}")
        except Exception as ex:
            try:
                rest_publish_collection(c)
                collection_ok += 1
                entry["status"] = "published_via_rest"
                entry["errors"] = [{"message": f"graphql_failed: {ex}"}]
                print(f"  collection {c['handle']}: published_via_rest (gql: {ex})")
            except Exception as ex2:
                collection_fail += 1
                entry["status"] = "exception"
                entry["errors"] = [str(ex), str(ex2)]
                print(f"  collection {c['handle']}: FAIL {ex2}")
        collection_results.append(entry)

    result = {
        "store": "icomply-supplys.myshopify.com",
        "online_store_publication_id": publication_id,
        "publications": publications,
        "products": {
            "total_active": len(products),
            "published_ok": product_ok,
            "failed": product_fail,
            "already_note": product_already,
            "items": product_results,
        },
        "collections": {
            "total": len(collections),
            "published_ok": collection_ok,
            "failed": collection_fail,
            "items": collection_results,
        },
        "summary": {
            "products_total_active": len(products),
            "products_published": product_ok,
            "products_failed": product_fail,
            "collections_total": len(collections),
            "collections_published": collection_ok,
            "collections_failed": collection_fail,
            "online_store_publication_id": publication_id,
            "online_store_publication_name": online["name"],
        },
    }

    OUT.write_text(json.dumps(result, indent=2), encoding="utf-8")
    print("\n=== DONE ===")
    print(json.dumps(result["summary"], indent=2))
    print(f"Wrote {OUT.resolve()}")


if __name__ == "__main__":
    main()
