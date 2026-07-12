#!/usr/bin/env python3
"""Create fire trade collections on Shopify."""

from __future__ import annotations

import json
from pathlib import Path

from shopify_client import api

PLAN = json.loads(
    (Path(__file__).parent / "catalog" / "master_plan.json").read_text(encoding="utf-8")
)


def existing_handles() -> dict[str, dict]:
    out: dict[str, dict] = {}
    data = api("GET", "/custom_collections.json?limit=250")
    for c in data.get("custom_collections", []):
        out[c["handle"]] = c
    return out


def main() -> None:
    existing = existing_handles()
    results = []
    for col in PLAN["collections"]:
        if col["handle"] in existing:
            cid = existing[col["handle"]]["id"]
            api(
                "PUT",
                f"/custom_collections/{cid}.json",
                {
                    "custom_collection": {
                        "id": cid,
                        "title": col["title"],
                        "body_html": f"<p>{col['body']}</p>",
                        "published": True,
                    }
                },
            )
            results.append({"action": "updated", "handle": col["handle"], "id": cid})
            print(f"updated {col['handle']} id={cid}")
        else:
            created = api(
                "POST",
                "/custom_collections.json",
                {
                    "custom_collection": {
                        "title": col["title"],
                        "handle": col["handle"],
                        "body_html": f"<p>{col['body']}</p>",
                        "published": True,
                        "sort_order": "best-selling",
                    }
                },
            )["custom_collection"]
            results.append(
                {"action": "created", "handle": col["handle"], "id": created["id"]}
            )
            print(f"created {col['handle']} id={created['id']}")

    # Tag existing service packages into service-packages collection
    svc = next((r for r in results if r["handle"] == "service-packages"), None)
    if svc:
        prods = api("GET", "/products.json?limit=250&fields=id,tags,product_type,handle")
        for p in prods.get("products", []):
            tags = p.get("tags") or ""
            if "service-package" in tags or p.get("product_type") == "Service Package":
                try:
                    api(
                        "POST",
                        f"/collects.json",
                        {
                            "collect": {
                                "product_id": p["id"],
                                "collection_id": svc["id"],
                            }
                        },
                    )
                    print(f"  linked service product {p['handle']}")
                except Exception as ex:
                    if "already" not in str(ex).lower() and "422" not in str(ex):
                        print(f"  link warn {p['handle']}: {ex}")

    out = Path(__file__).parent / "catalog" / "collections_result.json"
    out.write_text(json.dumps(results, indent=2), encoding="utf-8")
    print(f"Saved {out}")


if __name__ == "__main__":
    main()
