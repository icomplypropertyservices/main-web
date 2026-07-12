#!/usr/bin/env python3
"""Create/update VAT information page and ensure legal policies for iComply Supplys.

- Upserts page handle: vat-information
- Updates shop policies via GraphQL shopPolicyUpdate (fallback: REST, then pages)
- Writes legal_pages_result.json
"""

from __future__ import annotations

import json
import time
import traceback
from pathlib import Path
from typing import Any

from shopify_client import api, graphql

ROOT = Path(__file__).parent
STOREFRONT = ROOT / "storefront"
RESULT_PATH = ROOT / "legal_pages_result.json"

COMPANY = {
    "trading_name": "iComply Supplys",
    "legal_name": "iComply Property Services",
    "address": "17 Woodlands Park Road, Offerton, Stockport SK2 5DE",
    "phone": "07517806082",
    "phone_display": "07517 806082",
    "email": "icomplypropertyservices@gmail.com",
}

POLICY_MAP = [
    {
        "json_key": "privacy_policy",
        "gql_type": "PRIVACY_POLICY",
        "handle": "privacy-policy",
        "title": "Privacy Policy",
        "rest_key": "privacy_policy",
    },
    {
        "json_key": "refund_policy",
        "gql_type": "REFUND_POLICY",
        "handle": "refund-policy",
        "title": "Refund Policy",
        "rest_key": "refund_policy",
    },
    {
        "json_key": "shipping_policy",
        "gql_type": "SHIPPING_POLICY",
        "handle": "shipping-policy",
        "title": "Shipping Policy",
        "rest_key": "shipping_policy",
    },
    {
        "json_key": "terms_of_service",
        "gql_type": "TERMS_OF_SERVICE",
        "handle": "terms-of-service",
        "title": "Terms of Service",
        "rest_key": "terms_of_service",
    },
]

VAT_PAGE_TITLE = "VAT Information"
VAT_PAGE_HANDLE = "vat-information"

VAT_BODY_HTML = f"""
<h1>VAT Information</h1>
<p><strong>{COMPANY['trading_name']}</strong> is the online supply arm of <strong>{COMPANY['legal_name']}</strong>, {COMPANY['address']}. Telephone: <a href="tel:{COMPANY['phone']}">{COMPANY['phone_display']}</a>. Email: <a href="mailto:{COMPANY['email']}">{COMPANY['email']}</a>.</p>

<p>This page explains how Value Added Tax (VAT) applies to orders placed through our Shopify store, trade invoices, and Price on Application (POA) quotations.</p>

<h2>UK VAT rate</h2>
<p>Unless stated otherwise, goods and taxable services supplied by {COMPANY['trading_name']} are subject to <strong>UK VAT at the standard rate of 20%</strong>.</p>
<ul>
  <li>Where a product price is shown as taxable, VAT at 20% is applied at checkout in line with Shopify tax settings for the United Kingdom.</li>
  <li>Displayed catalogue prices should be read together with the tax note on each product and at checkout — the order total confirms the VAT amount before payment.</li>
  <li>If a line is zero-rated, exempt, or outside the scope of VAT, we will state that clearly on the product, invoice or written quote.</li>
</ul>

<h2>What you will see on invoices</h2>
<p>For completed orders we issue invoices (including trade invoices where applicable) that show:</p>
<ul>
  <li>Supplier name and trading address;</li>
  <li>Customer name and billing address;</li>
  <li>Invoice / order reference and date;</li>
  <li>Description of goods or services;</li>
  <li>Net amount, VAT rate, VAT amount and gross total (where VAT applies).</li>
</ul>
<p>Trade customers should ensure their company name, billing address and VAT registration number (if registered) are provided on the order or trade account so invoices can be issued correctly for your records.</p>

<h2>Trade invoices</h2>
<p>B2B / trade orders are invoiced to the business named on the account or order. Payment terms (card at checkout, pro-forma, or credit where expressly agreed) do not change the VAT treatment of the supply — VAT is still charged at the applicable rate on taxable supplies.</p>
<p>If you need a copy invoice, credit note or VAT breakdown for an existing order, contact us with your order number at <a href="mailto:{COMPANY['email']}">{COMPANY['email']}</a> or call <a href="tel:{COMPANY['phone']}">{COMPANY['phone_display']}</a>.</p>

<h2>POA (Price on Application) quotes</h2>
<p>Some products and project packages are listed as <strong>POA</strong> (Price on Application). For these items:</p>
<ul>
  <li>We provide a written quotation before you commit to purchase;</li>
  <li>Each quote states whether prices are <strong>exclusive of VAT</strong> or <strong>inclusive of VAT</strong>, as printed on that quote;</li>
  <li>Where a quote is exclusive of VAT, UK VAT at 20% (or the rate applicable at the tax point) will be added on the invoice unless a different VAT treatment is stated;</li>
  <li>Where a quote is inclusive of VAT, the quoted figure already includes VAT at the stated rate — the invoice will still show the VAT element for transparency;</li>
  <li>Always rely on the wording of the specific quote you received; if anything is unclear, ask us to confirm net, VAT and gross before ordering.</li>
</ul>

<h2>Checkout and online prices</h2>
<p>Online checkout calculates tax according to your delivery/billing address and our store tax configuration. The final order confirmation and invoice are the authoritative record of VAT charged for that order.</p>

<h2>Questions</h2>
<p>For VAT, invoicing or trade account queries:</p>
<p>
<strong>{COMPANY['trading_name']} / {COMPANY['legal_name']}</strong><br>
{COMPANY['address']}<br>
Tel: <a href="tel:{COMPANY['phone']}">{COMPANY['phone_display']}</a><br>
Email: <a href="mailto:{COMPANY['email']}">{COMPANY['email']}</a>
</p>
<p><em>This page is general information about how we present VAT on sales. It is not tax advice. Customers remain responsible for their own accounting and VAT recovery positions.</em></p>
""".strip()


def existing_pages_by_handle() -> dict[str, dict]:
    out: dict[str, dict] = {}
    data = api("GET", "/pages.json?limit=250")
    for p in data.get("pages", []):
        out[p["handle"]] = p
    return out


def upsert_page(
    handle: str,
    title: str,
    body_html: str,
    existing: dict[str, dict],
    published: bool = True,
) -> dict:
    payload = {
        "title": title,
        "handle": handle,
        "body_html": body_html,
        "published": published,
    }
    # Extra pause beyond shopify_client courtesy delay to avoid 429 bursts
    time.sleep(0.4)
    if handle in existing:
        pid = existing[handle]["id"]
        updated = api(
            "PUT",
            f"/pages/{pid}.json",
            {"page": {"id": pid, **payload}},
        ).get("page", {})
        return {
            "action": "updated",
            "handle": handle,
            "id": updated.get("id", pid),
            "title": title,
            "url": f"/pages/{handle}",
        }
    created = api("POST", "/pages.json", {"page": payload}).get("page", {})
    pid = created.get("id")
    if pid:
        existing[handle] = created
    return {
        "action": "created",
        "handle": handle,
        "id": pid,
        "title": title,
        "url": f"/pages/{handle}",
    }


def load_policies() -> dict[str, str]:
    path = STOREFRONT / "policies.json"
    raw = json.loads(path.read_text(encoding="utf-8"))
    out: dict[str, str] = {}
    if isinstance(raw, dict):
        for k, v in raw.items():
            if isinstance(v, str):
                out[k] = v
            elif isinstance(v, dict):
                out[k] = v.get("body_html") or v.get("body") or v.get("content") or ""
    return out


def update_shop_policy_graphql(gql_type: str, body: str, title: str) -> dict[str, Any]:
    mutation = """
    mutation shopPolicyUpdate($shopPolicy: ShopPolicyInput!) {
      shopPolicyUpdate(shopPolicy: $shopPolicy) {
        shopPolicy { type title url body }
        userErrors { field message }
      }
    }
    """
    # Prefer body only; some API versions reject title on ShopPolicyInput
    variables = {"shopPolicy": {"type": gql_type, "body": body}}
    g = graphql(mutation, variables)
    payload = (g.get("data") or {}).get("shopPolicyUpdate") or {}
    user_errors = payload.get("userErrors") or []
    if user_errors:
        raise RuntimeError(f"userErrors: {user_errors}")
    sp = payload.get("shopPolicy") or {}
    return {
        "via": "graphql_shopPolicyUpdate",
        "type": sp.get("type", gql_type),
        "title": sp.get("title") or title,
        "url": sp.get("url"),
        "body_len": len(sp.get("body") or body),
    }


def update_shop_policy_rest(rest_key: str, title: str, body: str) -> dict[str, Any]:
    # Shopify Admin REST policies are largely read-only; attempt for completeness
    res = api(
        "PUT",
        f"/policies/{rest_key}.json",
        {"policy": {"title": title, "body": body}},
    )
    return {"via": "rest_policies", "response_keys": list(res.keys()), "rest_key": rest_key}


def ensure_policy(
    meta: dict[str, str],
    body: str,
    existing_pages: dict[str, dict],
) -> dict[str, Any]:
    handle = meta["handle"]
    title = meta["title"]
    notes: list[str] = []
    result: dict[str, Any] = {
        "key": meta["json_key"],
        "handle": handle,
        "title": title,
        "gql_type": meta["gql_type"],
    }

    # 1) GraphQL shop policy
    try:
        gres = update_shop_policy_graphql(meta["gql_type"], body, title)
        result.update(gres)
        result["action"] = "updated"
        result["status"] = "ok"
        # Also ensure a page exists for footer/menu linking even if shop policy worked
        try:
            pr = upsert_page(handle, title, body, existing_pages)
            result["page_mirror"] = pr
        except Exception as ex:
            notes.append(f"page_mirror: {ex}")
        if notes:
            result["notes"] = notes
        return result
    except Exception as ex:
        notes.append(f"GQL: {ex}")

    # 2) REST attempt
    try:
        rres = update_shop_policy_rest(meta["rest_key"], title, body)
        result.update(rres)
        result["action"] = "updated"
        result["status"] = "ok"
        try:
            pr = upsert_page(handle, title, body, existing_pages)
            result["page_mirror"] = pr
        except Exception as ex:
            notes.append(f"page_mirror: {ex}")
        if notes:
            result["notes"] = notes
        return result
    except Exception as ex:
        notes.append(f"REST: {ex}")

    # 3) Page fallback (required when shop policies API fails)
    try:
        pr = upsert_page(handle, title, body, existing_pages)
        result["action"] = pr["action"]
        result["via"] = f"page_fallback:{pr['action']}"
        result["id"] = pr.get("id")
        result["url"] = pr.get("url")
        result["status"] = "ok"
        result["notes"] = notes
        return result
    except Exception as ex:
        notes.append(f"page: {ex}")
        result["action"] = "error"
        result["status"] = "error"
        result["errors"] = notes
        return result


def verify_shop_policies() -> list[dict]:
    try:
        g = graphql(
            """
            {
              shop {
                shopPolicies {
                  type
                  title
                  url
                  body
                }
              }
            }
            """
        )
        policies = ((g.get("data") or {}).get("shop") or {}).get("shopPolicies") or []
        return [
            {
                "type": p.get("type"),
                "title": p.get("title"),
                "url": p.get("url"),
                "body_len": len(p.get("body") or ""),
                "body_preview": (p.get("body") or "")[:120],
            }
            for p in policies
        ]
    except Exception as ex:
        return [{"error": str(ex)}]


def main() -> int:
    result: dict[str, Any] = {
        "company": COMPANY,
        "vat_page": None,
        "policies": [],
        "verification": {},
        "status": "pending",
    }

    try:
        print("== Existing pages ==")
        existing = existing_pages_by_handle()
        print(f"  {len(existing)} pages: {sorted(existing.keys())}")

        print("\n== VAT Information page ==")
        vat = upsert_page(VAT_PAGE_HANDLE, VAT_PAGE_TITLE, VAT_BODY_HTML, existing)
        result["vat_page"] = {
            **vat,
            "body_len": len(VAT_BODY_HTML),
            "topics": [
                "UK VAT 20%",
                "trade invoices",
                "POA quotes exclusive/inclusive of VAT as stated on quote",
            ],
        }
        print(f"  {vat['action']} {vat['handle']} id={vat.get('id')}")

        print("\n== Policies ==")
        policy_bodies = load_policies()
        policy_results: list[dict] = []
        for meta in POLICY_MAP:
            body = policy_bodies.get(meta["json_key"], "").strip()
            if not body:
                item = {
                    "key": meta["json_key"],
                    "handle": meta["handle"],
                    "action": "skipped",
                    "status": "error",
                    "reason": "empty body in policies.json",
                }
                policy_results.append(item)
                print(f"  SKIP {meta['handle']}: empty body")
                continue
            print(f"  ensuring {meta['handle']} ({len(body)} chars)...")
            item = ensure_policy(meta, body, existing)
            policy_results.append(item)
            print(
                f"    -> {item.get('status')} via={item.get('via')} "
                f"action={item.get('action')}"
            )
        result["policies"] = policy_results

        print("\n== Verification ==")
        shop_pols = verify_shop_policies()
        pages_after = existing_pages_by_handle()
        needed_handles = [VAT_PAGE_HANDLE] + [m["handle"] for m in POLICY_MAP]
        page_check = {
            h: {
                "exists": h in pages_after,
                "id": pages_after.get(h, {}).get("id"),
                "title": pages_after.get(h, {}).get("title"),
            }
            for h in needed_handles
        }
        result["verification"] = {
            "shop_policies": shop_pols,
            "pages": page_check,
        }
        for h, info in page_check.items():
            print(f"  page {h}: exists={info['exists']} id={info.get('id')}")

        ok_policies = all(p.get("status") == "ok" for p in policy_results)
        ok_vat = bool(result["vat_page"] and result["vat_page"].get("id"))
        ok_pages = all(page_check[h]["exists"] for h in needed_handles)
        result["status"] = "ok" if (ok_vat and ok_policies and ok_pages) else "partial"
        result["summary"] = {
            "vat_page_ok": ok_vat,
            "policies_ok": ok_policies,
            "all_pages_present": ok_pages,
            "policy_count": len(policy_results),
        }
    except Exception as ex:
        result["status"] = "error"
        result["error"] = str(ex)
        result["traceback"] = traceback.format_exc()
        print(f"FATAL: {ex}")
        print(result["traceback"])

    RESULT_PATH.write_text(json.dumps(result, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"\nWrote {RESULT_PATH}")
    print(f"Status: {result['status']}")
    return 0 if result["status"] in ("ok", "partial") else 1


if __name__ == "__main__":
    raise SystemExit(main())
