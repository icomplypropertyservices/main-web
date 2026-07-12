#!/usr/bin/env python3
"""POA UX / metafields: set custom.price_display and ensure POA body CTAs.

Rules:
  - Tagged poa OR price 0.00  -> custom.price_display = "POA"
  - Priced products            -> custom.price_display = "FROM"
  - POA items get clear contact CTA (phone + email) in body_html
"""

from __future__ import annotations

import json
import time
from pathlib import Path

from shopify_client import api, get_token, graphql

ROOT = Path(__file__).resolve().parent
OUT = ROOT / "poa_metafields_result.json"

PHONE = "07517806082"
PHONE_DISPLAY = "07517 806082"
EMAIL = "icomplypropertyservices@gmail.com"

POA_HTML = (
    f'<p><strong>Price on Application (POA)</strong> — Contact us for live trade '
    f'pricing and stock. Tel: <a href="tel:{PHONE}">{PHONE_DISPLAY}</a> · '
    f'Email: <a href="mailto:{EMAIL}">{EMAIL}</a>.</p>'
)

METAFIELDS_SET = """
mutation metafieldsSet($metafields: [MetafieldsSetInput!]!) {
  metafieldsSet(metafields: $metafields) {
    metafields { id namespace key value ownerType }
    userErrors { field message }
  }
}
"""


def iter_products():
    since = 0
    while True:
        data = api(
            "GET",
            f"/products.json?limit=50"
            f"&fields=id,title,handle,tags,body_html,variants,status"
            f"&since_id={since}",
        )
        batch = data.get("products") or []
        if not batch:
            break
        for p in batch:
            since = max(since, int(p["id"]))
            yield p
        if len(batch) < 50:
            break


def min_variant_price(variants: list[dict]) -> float:
    prices: list[float] = []
    for v in variants or []:
        try:
            prices.append(float(v.get("price") or 0))
        except (TypeError, ValueError):
            prices.append(0.0)
    return min(prices) if prices else 0.0


def is_poa(tags: list[str], min_price: float) -> bool:
    tagset = {t.lower() for t in tags}
    if "poa" in tagset or "price-on-application" in tagset:
        return True
    if min_price <= 0.0:
        return True
    return False


def body_has_poa_cta(body: str) -> bool:
    b = (body or "").lower()
    has_phone = PHONE in body or PHONE_DISPLAY.replace(" ", "") in body.replace(" ", "")
    has_email = EMAIL.lower() in b
    has_poa_label = (
        "price on application" in b
        or "poa" in b
        or "contact us for live trade" in b
    )
    return has_phone and has_email and has_poa_label


def set_price_display(product_id: int, value: str) -> dict:
    """Set custom.price_display via GraphQL metafieldsSet; REST fallback."""
    owner = f"gid://shopify/Product/{product_id}"
    try:
        g = graphql(
            METAFIELDS_SET,
            {
                "metafields": [
                    {
                        "ownerId": owner,
                        "namespace": "custom",
                        "key": "price_display",
                        "type": "single_line_text_field",
                        "value": value,
                    }
                ]
            },
        )
        payload = (g.get("data") or {}).get("metafieldsSet") or {}
        uerr = payload.get("userErrors") or []
        if uerr:
            raise RuntimeError(str(uerr))
        mfs = payload.get("metafields") or []
        mid = mfs[0].get("id") if mfs else None
        return {"ok": True, "method": "graphql", "metafield_id": mid, "value": value}
    except Exception as gql_err:
        try:
            resp = api(
                "POST",
                f"/products/{product_id}/metafields.json",
                {
                    "metafield": {
                        "namespace": "custom",
                        "key": "price_display",
                        "type": "single_line_text_field",
                        "value": value,
                    }
                },
            )
            mf = resp.get("metafield") or {}
            return {
                "ok": True,
                "method": "rest",
                "metafield_id": mf.get("id"),
                "value": value,
                "graphql_error": str(gql_err),
            }
        except Exception as rest_err:
            return {
                "ok": False,
                "method": "failed",
                "value": value,
                "error": f"GQL: {gql_err}; REST: {rest_err}",
            }


def ensure_poa_body(product_id: int, body: str) -> tuple[str, bool]:
    """Return (new_body, updated?)."""
    if body_has_poa_cta(body):
        # Still ensure phone + email present even if partial CTA existed
        return body, False
    new_body = POA_HTML + ("\n" + body if body else "")
    api(
        "PUT",
        f"/products/{product_id}.json",
        {"product": {"id": product_id, "body_html": new_body}},
    )
    return new_body, True


def main() -> None:
    get_token(force=True)
    items: list[dict] = []
    summary = {
        "total": 0,
        "poa": 0,
        "priced_from": 0,
        "metafield_ok": 0,
        "metafield_fail": 0,
        "body_cta_updated": 0,
        "body_cta_already_ok": 0,
        "errors": 0,
    }

    for p in iter_products():
        pid = int(p["id"])
        handle = p.get("handle") or ""
        title = p.get("title") or ""
        tags = [t.strip() for t in (p.get("tags") or "").split(",") if t.strip()]
        variants = p.get("variants") or []
        min_price = min_variant_price(variants)
        poa = is_poa(tags, min_price)
        display = "POA" if poa else "FROM"
        body = p.get("body_html") or ""

        record: dict = {
            "id": pid,
            "handle": handle,
            "title": title,
            "tags": tags,
            "min_price": min_price,
            "classification": "poa" if poa else "priced",
            "price_display": display,
        }

        # Metafield
        mf_result = set_price_display(pid, display)
        record["metafield"] = mf_result
        if mf_result.get("ok"):
            summary["metafield_ok"] += 1
        else:
            summary["metafield_fail"] += 1
            summary["errors"] += 1

        # Body CTA for POA only
        if poa:
            summary["poa"] += 1
            try:
                if body_has_poa_cta(body):
                    record["body_cta"] = {"updated": False, "status": "already_ok"}
                    summary["body_cta_already_ok"] += 1
                else:
                    _, updated = ensure_poa_body(pid, body)
                    record["body_cta"] = {
                        "updated": updated,
                        "status": "injected" if updated else "already_ok",
                        "phone": PHONE,
                        "email": EMAIL,
                    }
                    if updated:
                        summary["body_cta_updated"] += 1
                    else:
                        summary["body_cta_already_ok"] += 1
            except Exception as ex:
                record["body_cta"] = {"updated": False, "status": "error", "error": str(ex)}
                summary["errors"] += 1
        else:
            summary["priced_from"] += 1
            record["body_cta"] = {"updated": False, "status": "n/a_priced"}

        items.append(record)
        summary["total"] += 1
        print(
            f"[{summary['total']}] {handle} -> {display} "
            f"mf={'ok' if mf_result.get('ok') else 'FAIL'} "
            f"price={min_price}"
        )
        time.sleep(0.15)

    result = {
        "summary": summary,
        "rules": {
            "poa": 'tag "poa"/"price-on-application" OR min variant price == 0.00 -> price_display=POA + body CTA',
            "priced": "otherwise -> price_display=FROM",
            "metafield": "namespace=custom key=price_display type=single_line_text_field",
            "cta_contact": {"phone": PHONE, "email": EMAIL},
        },
        "items": items,
    }
    OUT.write_text(json.dumps(result, indent=2), encoding="utf-8")
    print(json.dumps(summary, indent=2))
    print(f"Wrote {OUT}")


if __name__ == "__main__":
    main()
