#!/usr/bin/env python3
"""UK VAT setup for iComply Supplys — apply what the Admin API allows.

What the API can do on Basic plan (verified):
  - Read shop.taxes_included / taxShipping
  - Read /countries (GB present) and province tax fields
  - Set product variant taxable=true via REST PUT /variants/{id}.json
  - Create/update Online Store pages (VAT policy page)

What the API cannot fully do (verified on this store):
  - PUT /shop.json taxes_included → HTTP 406
  - PUT /countries/{id} tax=0.20 → accepted but tax remains 0.0
    (Shopify Tax / automatic UK VAT managed in Admin UI)
  - No public GraphQL taxRegions mutation for standard VAT rate

Manual Admin steps are written to VAT_SETUP_MANUAL.md.
"""

from __future__ import annotations

import json
import sys
import time
from datetime import datetime, timezone
from pathlib import Path

from shopify_client import api, graphql, get_store

ROOT = Path(__file__).resolve().parent
OUT = ROOT / "vat_setup_result.json"
MANUAL = ROOT / "VAT_SETUP_MANUAL.md"
GB_CODE = "GB"
TARGET_RATE = 0.20


def find_gb_country() -> dict | None:
    for c in api("GET", "/countries.json").get("countries", []):
        if c.get("code") == GB_CODE:
            return c
    return None


def attempt_gb_vat(country: dict) -> dict:
    result = {
        "country_id": country["id"],
        "country_name": country.get("name"),
        "before_tax": country.get("tax"),
        "before_tax_name": country.get("tax_name"),
        "country_put": None,
        "provinces": [],
        "after": None,
        "api_can_set_rate": False,
        "errors": [],
        "note": (
            "Modern Shopify UK stores use automatic Shopify Tax. "
            "REST country/province tax fields often stay 0.0 even after PUT."
        ),
    }
    cid = country["id"]

    try:
        updated = api(
            "PUT",
            f"/countries/{cid}.json",
            {"country": {"id": cid, "tax": TARGET_RATE, "tax_name": "VAT"}},
        )
        c = updated.get("country", {})
        result["country_put"] = {
            "tax": c.get("tax"),
            "tax_name": c.get("tax_name"),
            "ok": abs(float(c.get("tax") or 0) - TARGET_RATE) < 0.0001,
        }
    except Exception as e:
        result["errors"].append(f"country PUT: {e}")
        result["country_put"] = {"ok": False, "error": str(e)}

    full = api("GET", f"/countries/{cid}.json").get("country", {})
    for p in full.get("provinces") or []:
        entry = {
            "id": p["id"],
            "name": p["name"],
            "before_tax": p.get("tax"),
            "before_tax_percentage": p.get("tax_percentage"),
        }
        try:
            r = api(
                "PUT",
                f"/countries/{cid}/provinces/{p['id']}.json",
                {
                    "province": {
                        "id": p["id"],
                        "tax": TARGET_RATE,
                        "tax_name": "VAT",
                        "tax_percentage": 20.0,
                    }
                },
            )
            pr = r.get("province", {})
            entry["after_tax"] = pr.get("tax")
            entry["after_tax_percentage"] = pr.get("tax_percentage")
            entry["ok"] = abs(float(pr.get("tax") or 0) - TARGET_RATE) < 0.0001 or abs(
                float(pr.get("tax_percentage") or 0) - 20.0
            ) < 0.0001
        except Exception as e:
            entry["ok"] = False
            entry["error"] = str(e)
            result["errors"].append(f"province {p['name']}: {e}")
        result["provinces"].append(entry)

    after = api("GET", f"/countries/{cid}.json").get("country", {})
    result["after"] = {
        "tax": after.get("tax"),
        "tax_name": after.get("tax_name"),
        "provinces": [
            {
                "name": p.get("name"),
                "tax": p.get("tax"),
                "tax_percentage": p.get("tax_percentage"),
                "tax_name": p.get("tax_name"),
            }
            for p in (after.get("provinces") or [])
        ],
    }
    result["api_can_set_rate"] = abs(float(after.get("tax") or 0) - TARGET_RATE) < 0.0001
    return result


def attempt_taxes_included(target: bool = False) -> dict:
    """Trade recommendation: tax-exclusive catalogue (taxes_included=false)."""
    result = {
        "target": target,
        "rest_put": None,
        "graphql": None,
        "verified": None,
        "api_can_set": False,
        "errors": [],
    }
    try:
        shop_upd = api(
            "PUT",
            "/shop.json",
            {"shop": {"taxes_included": target, "tax_shipping": True}},
        )
        s = shop_upd.get("shop", {})
        result["rest_put"] = {
            "taxes_included": s.get("taxes_included"),
            "tax_shipping": s.get("tax_shipping"),
            "ok": s.get("taxes_included") is target,
        }
    except Exception as e:
        result["errors"].append(f"REST shop PUT: {e}")
        result["rest_put"] = {"ok": False, "error": str(e)}

    try:
        r = graphql(
            """
            mutation ShopUpdate($input: ShopInput!) {
              shopUpdate(input: $input) {
                shop { taxesIncluded taxShipping }
                userErrors { field message }
              }
            }
            """,
            {"input": {"taxesIncluded": target}},
        )
        result["graphql"] = r.get("data") or r
    except Exception as e:
        result["errors"].append(f"GraphQL shopUpdate: {e}")
        result["graphql"] = {"error": str(e)}

    shop = api("GET", "/shop.json").get("shop", {})
    result["verified"] = {
        "taxes_included": shop.get("taxes_included"),
        "tax_shipping": shop.get("tax_shipping"),
        "currency": shop.get("currency"),
        "country": shop.get("country"),
    }
    result["api_can_set"] = shop.get("taxes_included") is target
    return result


def iter_products(fields: str = "id,title,variants"):
    since_id = 0
    while True:
        products = api(
            "GET",
            f"/products.json?limit=50&fields={fields}&since_id={since_id}",
        ).get("products", [])
        if not products:
            break
        for p in products:
            yield p
        since_id = products[-1]["id"]


def ensure_variants_taxable() -> dict:
    summary = {
        "products_scanned": 0,
        "variants_scanned": 0,
        "variants_already_taxable": 0,
        "variants_updated": 0,
        "variants_failed": 0,
        "updated": [],
        "failed": [],
        "sample_non_taxable_before": [],
    }
    for product in iter_products():
        summary["products_scanned"] += 1
        for v in product.get("variants") or []:
            summary["variants_scanned"] += 1
            if v.get("taxable") is True:
                summary["variants_already_taxable"] += 1
                continue
            if len(summary["sample_non_taxable_before"]) < 25:
                summary["sample_non_taxable_before"].append(
                    {
                        "product_id": product["id"],
                        "variant_id": v["id"],
                        "title": product.get("title"),
                        "taxable": v.get("taxable"),
                    }
                )
            try:
                api(
                    "PUT",
                    f"/variants/{v['id']}.json",
                    {"variant": {"id": v["id"], "taxable": True}},
                )
                summary["variants_updated"] += 1
                summary["updated"].append(
                    {
                        "product_id": product["id"],
                        "variant_id": v["id"],
                        "title": product.get("title"),
                    }
                )
            except Exception as e:
                summary["variants_failed"] += 1
                summary["failed"].append(
                    {
                        "product_id": product["id"],
                        "variant_id": v["id"],
                        "error": str(e),
                    }
                )

    nontax = 0
    tax_true = 0
    for product in iter_products():
        for v in product.get("variants") or []:
            if v.get("taxable") is True:
                tax_true += 1
            else:
                nontax += 1

    summary["verify"] = {
        "variants_taxable_true": tax_true,
        "variants_not_taxable": nontax,
        "all_taxable": nontax == 0 and tax_true > 0,
    }
    return summary


def probe_graphql() -> dict:
    out: dict = {}
    try:
        r = graphql(
            """
            query {
              shop {
                taxesIncluded
                taxShipping
                currencyCode
                billingAddress { country countryCodeV2 }
                plan { displayName }
              }
            }
            """
        )
        out["shop"] = (r.get("data") or {}).get("shop")
    except Exception as e:
        out["shop_error"] = str(e)

    # Confirm taxRegions does not exist
    try:
        graphql("{ taxRegions(first: 1) { edges { node { id } } } }")
        out["taxRegions"] = "exists"
    except Exception as e:
        out["taxRegions"] = f"unavailable: {e}"
    return out


def create_vat_policy_page() -> dict:
    handle = "vat-and-pricing"
    title = "VAT & Pricing"
    body_html = """
<h1>VAT &amp; Pricing</h1>
<p><strong>iComply Supplys</strong> is a trading name of <strong>iComply Property Services</strong>,
17 Woodlands Park Road, Offerton, Stockport SK2 5DE.</p>

<h2>How our prices work</h2>
<p>Unless a product page clearly states otherwise, catalogue prices are intended to be shown
<strong>exclusive of VAT</strong> (ex-VAT). This matches UK trade supply practice for fire detection,
emergency lighting and electrical equipment.</p>
<ul>
  <li><strong>Trade / B2B:</strong> prices displayed ex-VAT where the store is configured for tax-exclusive display; UK standard rate VAT (currently 20%) is calculated and added at checkout on taxable goods.</li>
  <li><strong>Retail / B2C:</strong> the same product tax flags apply — VAT is charged at the rate configured for the delivery destination.</li>
</ul>

<h2>UK VAT</h2>
<p>We charge UK Value Added Tax at the standard rate of <strong>20%</strong> on taxable goods
(and on taxable shipping where the store is configured to charge tax on shipping) for deliveries within the United Kingdom.</p>
<p>If you are a VAT-registered business and believe reverse charge or a special treatment may apply
to a specific order, contact us <em>before</em> checkout with your company details and VAT number.
Reverse charge is not applied automatically at the online checkout.</p>

<h2>Invoices &amp; VAT numbers</h2>
<ul>
  <li>Ensure your billing company name and address are correct at checkout.</li>
  <li>To open a trade account and provide your VAT registration number, email
  <a href="mailto:icomplypropertyservices@gmail.com">icomplypropertyservices@gmail.com</a>
  with subject <strong>Trade Account Application</strong>.</li>
  <li>Order confirmations show VAT as calculated by the store at the time of sale.</li>
</ul>

<h2>Questions</h2>
<p>Phone: <a href="tel:07517806082">07517 806082</a> ·
Email: <a href="mailto:icomplypropertyservices@gmail.com">icomplypropertyservices@gmail.com</a></p>
""".strip()

    result: dict = {"handle": handle, "title": title}
    try:
        pages = api("GET", f"/pages.json?handle={handle}&limit=5").get("pages", [])
        match = next((p for p in pages if p.get("handle") == handle), None)
        if match:
            updated = api(
                "PUT",
                f"/pages/{match['id']}.json",
                {
                    "page": {
                        "id": match["id"],
                        "title": title,
                        "body_html": body_html,
                        "published": True,
                    }
                },
            )
            p = updated.get("page", {})
            result["action"] = "updated"
            result["id"] = p.get("id")
            result["handle"] = p.get("handle")
            result["url_path"] = f"/pages/{p.get('handle')}"
        else:
            created = api(
                "POST",
                "/pages.json",
                {
                    "page": {
                        "title": title,
                        "handle": handle,
                        "body_html": body_html,
                        "published": True,
                    }
                },
            )
            p = created.get("page", {})
            result["action"] = "created"
            result["id"] = p.get("id")
            result["handle"] = p.get("handle")
            result["url_path"] = f"/pages/{p.get('handle')}"
    except Exception as e:
        result["action"] = "error"
        result["error"] = str(e)
    return result


def write_manual(result: dict) -> None:
    shop_before = result["steps"].get("shop_before", {})
    shop_after = result["steps"].get("shop_after", {})
    gb = result["steps"].get("gb_after") or result["steps"].get("gb_vat", {}).get("after")
    variants = result["steps"].get("variants_taxable", {})
    page = result["steps"].get("vat_page", {})

    md = f"""# UK VAT setup — iComply Supplys (manual Admin steps)

**Store:** `icomply-supplys.myshopify.com`  
**Last API run:** {result.get("run_at")}  
**Recommendation:** **Tax-exclusive trade pricing** (catalogue prices **ex-VAT**, 20% VAT added at checkout)

---

## Pricing recommendation (chosen)

| Option | Use when | Decision for iComply Supplys |
|--------|----------|------------------------------|
| **Tax-exclusive (recommended)** | Trade / B2B electrical & fire supply | **YES — use this** |
| Tax-inclusive | Consumer retail-only shops | No |

**Why tax-exclusive for trade**

- UK fire, emergency lighting and electrical trade catalogues almost always quote **ex-VAT**.
- Contractors compare distributor list prices excluding VAT.
- Quotes, BOQs and purchase orders are easier to match when line prices are net.
- VAT still appears clearly on the checkout total and invoice.

**How it should look**

1. Product page / collection price: **£X.XX** (ex VAT)  
2. Cart / checkout: subtotal (ex VAT) + **VAT 20%** + shipping (+ shipping VAT if charged) = total  
3. Theme may show “ex VAT” near prices (theme setting / price badge)

---

## What the API already did

| Item | Result |
|------|--------|
| `shop.taxes_included` read | Before: `{shop_before.get("taxes_included")}` → After: `{shop_after.get("taxes_included")}` |
| Set `taxes_included=false` via REST/GraphQL | **Not possible** on this shop (REST returns **HTTP 406**; GraphQL `ShopInput` does not expose a reliable write) |
| GB country present | Yes (`code=GB`, tax_name currently `{ (gb or {}).get("tax_name") if isinstance(gb, dict) else "GB VAT" }`) |
| Set GB tax rate to 0.20 via REST | **Does not stick** (automatic Shopify Tax keeps REST `tax` at `0.0`) |
| All product variants `taxable=true` | Scanned **{variants.get("products_scanned")}** products / **{variants.get("variants_scanned")}** variants; updated **{variants.get("variants_updated")}**; verify all taxable: **{(variants.get("verify") or {}).get("all_taxable")}** |
| Customer-facing VAT page | **{page.get("action")}** → `/pages/{page.get("handle", "vat-and-pricing")}` |

Because automatic UK VAT is controlled in Admin (not fully via public API), complete the clicks below.

---

## Exact Admin clicks — enable 20% UK VAT

### A. Open Taxes and duties

1. Log in to Shopify Admin for **iComply Supplys**  
   `https://admin.shopify.com/store/c9zse6-gn` (or open the store from your partner/admin list).
2. Click **Settings** (bottom-left gear).
3. Click **Taxes and duties**.

### B. United Kingdom — 20% VAT

1. Under **Countries/regions** (or **Tax regions**), open **United Kingdom**.  
   Path summary: **Settings → Taxes and duties → United Kingdom**.
2. Confirm the region is set up to **collect tax / VAT** on sales to the UK.
3. Ensure the **standard VAT rate is 20%** (Shopify Tax usually applies current UK rates automatically).
4. If you see options such as:
   - **Collect VAT** / **Charge tax** → **ON**
   - **Shopify Tax** / **Automatic tax calculation** → leave **ON** for UK (recommended) so the 20% standard rate stays current
5. If using **manual rates** instead of Shopify Tax:
   - Add / edit country rate for **United Kingdom** → **VAT** → **20%**
   - Apply to taxable products (and shipping if you charge VAT on carriage)

### C. Tax-inclusive vs exclusive display (critical for trade)

1. Still under **Settings → Taxes and duties** (or **Settings → Store details / Markets** depending on Admin version).
2. Find the option similar to:  
   **“Include sales tax in product price and shipping rate”**  
   (wording may be **Include tax in prices** / linked from Markets).
3. Set this to **OFF** so catalogue prices are **ex-VAT** and VAT is added at checkout.  
   - This is the trade recommendation.  
   - API cannot toggle this on this store (406 / not writable).

### D. Shipping taxability

1. In **Taxes and duties → United Kingdom** (or shipping tax section):  
   enable charging tax on shipping if you sell standard-rated goods with taxed carriage.
2. Align shipping rates in **Settings → Shipping and delivery** with your VAT policy.

### E. VAT registration number

1. If the business is **VAT-registered**, enter the **VAT registration number** where Shopify requests it  
   (Taxes and duties / legal business details / invoice settings — exact field depends on Admin UI version).
2. Use the number on invoices and, where required, the store footer / policy pages.

### F. Product “Charge tax” (API already set)

1. Products → open any product → **Pricing** → **Charge tax on this product** should be **checked**.
2. Bulk API run set `taxable: true` on variants; spot-check a few SKUs after import.

### G. Test order (do not skip)

1. Place a test order to a **UK mainland** address.
2. Use a taxable product with known net price, e.g. **£100.00** ex-VAT.
3. Confirm checkout shows approximately **£20.00 VAT** (20%) plus shipping as configured.
4. Complete or cancel the test order; keep a screenshot for your accountant if needed.

### H. Optional B2B extras

1. Collect company name at checkout (already common on trade themes).
2. For VAT numbers on checkout: use Shopify’s tax ID / Markets B2B features or a VAT ID app if reverse-charge workflows are required later.
3. Link the **VAT & Pricing** page in the footer menu: Online Store → Navigation → Footer → add **VAT & Pricing** (`/pages/vat-and-pricing`).

---

## Theme note (ex-VAT label)

If prices still look “inc VAT” after turning off tax-inclusive pricing:

1. Online Store → Themes → **Customize**
2. Search theme settings for tax / VAT / price display
3. Add a small product-price suffix such as **“ex VAT”** if the theme supports it  
   (Dawn and many trade themes use a custom liquid/snippet or app block)

---

## API limitations (reference)

```
PUT /admin/api/2024-10/shop.json  {{ "shop": {{ "taxes_included": false }} }}
→ HTTP 406 on this store

PUT /admin/api/2024-10/countries/{{id}}.json  {{ "country": {{ "tax": 0.20 }} }}
→ Response returns tax: 0.0 (Shopify Tax owns UK rates)

PUT /admin/api/2024-10/variants/{{id}}.json  {{ "variant": {{ "taxable": true }} }}
→ Works — used for all catalogue variants
```

GraphQL `taxRegions` is **not** available on Admin API `2024-10` for this shop.

---

## Contacts

- **iComply Supplys / iComply Property Services**  
- 17 Woodlands Park Road, Offerton, Stockport SK2 5DE  
- Tel: 07517 806082  
- Email: icomplypropertyservices@gmail.com  

---

## Checklist (tick when done in Admin)

- [ ] Settings → Taxes and duties → United Kingdom opened
- [ ] Collect UK VAT enabled / confirmed
- [ ] Standard rate 20% confirmed (Shopify Tax or manual)
- [ ] Tax-inclusive product pricing **OFF** (ex-VAT catalogue)
- [ ] Tax on shipping decided and configured
- [ ] VAT registration number entered (if registered)
- [ ] Test UK order shows 20% VAT
- [ ] Footer link to `/pages/vat-and-pricing` (optional)
- [ ] Sample invoice checked with accountant
"""
    MANUAL.write_text(md, encoding="utf-8")


def main() -> int:
    store = get_store()
    print(f"Store: {store}")
    time.sleep(2)  # cool rate-limit bucket

    result: dict = {
        "store": store,
        "run_at": datetime.now(timezone.utc).isoformat(),
        "recommendation": {
            "pricing_display": "tax_exclusive",
            "taxes_included_target": False,
            "uk_vat_rate": 0.20,
            "tax_name": "VAT",
            "summary": (
                "Trade recommendation: catalogue prices EXCLUSIVE of VAT "
                "(taxes_included=false). Charge UK standard VAT 20% at checkout "
                "on taxable products. Matches UK fire/electrical trade norms."
            ),
        },
        "api_limitations": [],
        "steps": {},
    }

    shop = api("GET", "/shop.json").get("shop", {})
    result["steps"]["shop_before"] = {
        "taxes_included": shop.get("taxes_included"),
        "tax_shipping": shop.get("tax_shipping"),
        "currency": shop.get("currency"),
        "country": shop.get("country"),
        "plan_name": shop.get("plan_name"),
        "name": shop.get("name"),
        "myshopify_domain": shop.get("myshopify_domain"),
        "domain": shop.get("domain"),
    }
    print("Shop before:", result["steps"]["shop_before"])

    print("GraphQL probe...")
    result["steps"]["graphql_probe"] = probe_graphql()

    print("Attempt taxes_included=false...")
    result["steps"]["taxes_included"] = attempt_taxes_included(False)
    if not result["steps"]["taxes_included"]["api_can_set"]:
        result["api_limitations"].append(
            "Cannot set shop.taxes_included via REST (HTTP 406) or GraphQL on this store. "
            "Turn OFF 'Include sales tax in product price' in Admin → Taxes and duties."
        )
    print("  verified:", result["steps"]["taxes_included"]["verified"])

    print("Attempt GB 20% VAT via countries API...")
    gb = find_gb_country()
    if not gb:
        result["steps"]["gb_vat"] = {"ok": False, "error": "GB country not found"}
        result["api_limitations"].append("GB country missing from /countries.json")
    else:
        gb_full = api("GET", f"/countries/{gb['id']}.json").get("country", gb)
        result["steps"]["gb_vat"] = attempt_gb_vat(gb_full)
        if not result["steps"]["gb_vat"]["api_can_set_rate"]:
            result["api_limitations"].append(
                "Cannot set United Kingdom VAT rate to 20% via REST country/province tax fields "
                "(values remain 0.0 under Shopify Tax). Configure in Admin → Taxes and duties → United Kingdom."
            )
        print("  GB tax after:", (result["steps"]["gb_vat"].get("after") or {}).get("tax"))

    print("Ensure all variants taxable=true...")
    result["steps"]["variants_taxable"] = ensure_variants_taxable()
    v = result["steps"]["variants_taxable"]
    print(
        f"  products={v['products_scanned']} variants={v['variants_scanned']} "
        f"updated={v['variants_updated']} already={v['variants_already_taxable']} "
        f"failed={v['variants_failed']} verify={v.get('verify')}"
    )

    print("VAT & Pricing page...")
    result["steps"]["vat_page"] = create_vat_policy_page()
    print(" ", result["steps"]["vat_page"])

    shop2 = api("GET", "/shop.json").get("shop", {})
    result["steps"]["shop_after"] = {
        "taxes_included": shop2.get("taxes_included"),
        "tax_shipping": shop2.get("tax_shipping"),
        "currency": shop2.get("currency"),
        "country": shop2.get("country"),
    }

    gb_final = find_gb_country()
    if gb_final:
        gb_full = api("GET", f"/countries/{gb_final['id']}.json").get("country", {})
        result["steps"]["gb_after"] = {
            "id": gb_full.get("id"),
            "code": gb_full.get("code"),
            "tax": gb_full.get("tax"),
            "tax_name": gb_full.get("tax_name"),
            "provinces": [
                {
                    "name": p.get("name"),
                    "tax": p.get("tax"),
                    "tax_percentage": p.get("tax_percentage"),
                    "tax_name": p.get("tax_name"),
                }
                for p in (gb_full.get("provinces") or [])
            ],
        }

    result["manual_admin_required"] = True
    result["manual_admin_checklist"] = [
        "Settings → Taxes and duties → United Kingdom",
        "Confirm collect VAT / 20% standard rate (Shopify Tax recommended)",
        "Turn OFF include tax in product prices (tax-exclusive trade display)",
        "Configure tax on shipping as required",
        "Enter VAT registration number if VAT-registered",
        "Place test UK order and verify 20% VAT line",
        "Optional: footer link to /pages/vat-and-pricing",
    ]

    result["success"] = {
        "taxes_included_false": shop2.get("taxes_included") is False,
        "taxes_included_api_writable": result["steps"]["taxes_included"]["api_can_set"],
        "variants_all_taxable": bool((v.get("verify") or {}).get("all_taxable")),
        "variants_updated_count": v.get("variants_updated", 0),
        "gb_vat_20_via_api": bool(result["steps"].get("gb_vat", {}).get("api_can_set_rate")),
        "vat_page_ok": result["steps"]["vat_page"].get("action") in ("created", "updated"),
        "manual_doc_written": True,
    }

    write_manual(result)
    OUT.write_text(json.dumps(result, indent=2), encoding="utf-8")
    print(f"\nWrote {OUT}")
    print(f"Wrote {MANUAL}")
    print("Success flags:", json.dumps(result["success"], indent=2))
    return 0


if __name__ == "__main__":
    sys.exit(main())
