# MASTER TRADE-READY REPORT — iComply Supplys

| Field | Value |
|-------|--------|
| **Store** | `icomply-supplys.myshopify.com` |
| **Brand** | iComply Supplys (trading) / iComply Property Services (legal) |
| **Plan** | Shopify Basic |
| **Currency / market** | GBP · United Kingdom |
| **Timezone** | Europe/London |
| **Address** | 17 Woodlands Park Road, Offerton, Stockport SK2 5DE |
| **Phone** | 07517 806082 |
| **Email** | icomplypropertyservices@gmail.com |
| **Report generated (UTC)** | 2026-07-12T13:20:00Z (approx; post-agent scan) |
| **Final QA verdict** | **PASS** (all six automated checks) |
| **Overall trade readiness** | **API / catalog READY — merchant go-live gates still open** |

This report consolidates agent result JSON, audits, and setup markdown under  
`C:\Users\E-Store\Documents\icomplyproperty\shopify-import`.  
Machine-readable twin: `master_status.json`.

---

## Executive summary

| Area | Status | Notes |
|------|--------|--------|
| Product catalog | **READY** | 260 live products (249 hardware upload + ~11 service packages) |
| Images | **READY** | 260/260 (100%) with ≥1 image |
| Pricing / POA | **MOSTLY READY — review 14 POA lines** | 246 priced keep/adjust; 14 flagged POA in audit; Final QA saw **0** `poa` tags |
| VAT / tax | **READY (product-level)** | 260/260 variants taxable; `taxes_included=true`; VAT page live. Dedicated `vat_setup_result.json` **missing** |
| Collections / SEO | **READY** | 13 collections; SEO copy on 12; all published |
| Legal / policies | **READY** | Refund, shipping, terms shop policies + page mirrors; privacy via auto + page fallback; VAT page |
| Shipping rates | **READY (API)** | UK tiered standard + express + free £400+ applied; **local pickup not enabled via API** |
| Storefront API | **READY** | GraphQL shop/products/collections queries pass |
| Theme homepage | **MANUAL** | Horizon main theme; hero still generic; manual editor steps documented |
| Google services | **MANUAL / PARTIAL** | Setup guide written; `google_setup_result.json` + feed sample **missing** |
| Payments / domain | **MANUAL REQUIRED** | Cannot be completed by API; checklist ready |
| Overall go-live | **BLOCKED ON MERCHANT** | Payments KYC, domain/SSL, Google OAuth, homepage polish, pickup toggle |

---

## 1. Product count

| Metric | Value | Source |
|--------|-------|--------|
| Live products (Admin REST) | **260** | `final_qa.json` |
| Bulk hardware upload | 249 created, 0 errors | `upload_log.json` |
| Service packages upload | 11 created | `upload-results.json` |
| Expected minimum | ≥ 250 | Final QA criterion |
| **QA result** | **PASS** | |

**Breakdown (approx):** trade fire/security hardware SKUs + 11 service packages (electrical compliance, fire alarm service, emergency lighting, AOV, nurse call, gas safety, intruder, CCTV, access control, door entry, intercoms).

---

## 2. Image status

| Metric | Value | Source |
|--------|-------|--------|
| Products with ≥1 image | **260 / 260 (100%)** | `image_audit_final.json`, `final_qa.json` |
| Fixed this pass | 0 | image audit final |
| Failed / needs regen | 0 | image audit final |
| Early audit (pre-final) | 244/244 ok | `image_audit.json` (superseded) |
| **QA result** | **PASS** (≥ 95%) | |

CDN images present on sample Storefront API products (service packages and hardware). Collection **images** for featured collection cards remain empty (theme manual action).

---

## 3. Pricing / POA status

| Metric | Value | Source |
|--------|-------|--------|
| Products in pricing audit | 260 | `catalog/pricing_audit.json` |
| Action `keep` | 11 | Valid service-package band prices |
| Action `adjust` | 235 | In-band prices; tags/HTML cleanup |
| Action `poa` | **14** | Below band min → `new_price` 0.00 in audit |
| Products tagged `poa` / `price-on-application` (Final QA) | **0** | `final_qa.json` |
| **QA result (POA tag count)** | **PASS** (count retrieved) | |

### POA handles from pricing audit (merchant review)

These were zeroed / flagged POA by the pricing agent (band floor logic). Final QA did **not** find POA tags — **verify live prices and tags in Admin** so checkout does not sell £0 lines without POA UX:

1. `apollo-s65-standard-base`
2. `apollo-xp95-standard-base`
3. `apollo-series65-standard-base`
4. `hochiki-ybn-r3-standard-base`
5. `ctec-conventional-diode-base`
6. `ctec-activ-standard-base`
7. `eaton-conventional-detector-base`
8. `fire-cable-gland-pack-20`
9. `fire-cable-clip-red-pack-100`
10. `fire-cable-joint-kit-inline`
11. `trade-12v-3-2ah-vrla-battery`
12. `battery-terminal-spade-kit-4-8mm`
13. `emergency-lighting-test-key-switch`
14. `exit-sign-legend-pack-iso7010`

**Recommendation:** Either restore realistic trade prices **or** tag `poa` + `price-on-application`, set storefront messaging (theme POA note), and block add-to-cart / use “Contact for price” as intended.

Currency for all live guide prices: **GBP**.

---

## 4. VAT

| Item | Status | Evidence |
|------|--------|----------|
| Shop `taxes_included` | **true** | `theme_result.json` |
| Variants taxable | **260/260 (100%)** | `final_qa.json` |
| VAT information page | **Live** | `/pages/vat-information` (id `732204400972`) — `legal_pages_result.json` |
| Messaging target | Prices include UK VAT at 20% where applicable | Theme targets + VAT page topics |
| VAT registration number on invoices | **Merchant** | Enter if registered under **Settings → Taxes** |
| Dedicated VAT agent result file | **PENDING / MISSING** | Expected `vat_setup_result.json` not found after two scans |

**QA result (taxable variants):** **PASS**.

Merchant should still confirm in Admin:

- **Settings → Taxes and duties** → United Kingdom standard **20% VAT**
- Optional VAT number on shop/invoices
- Sample order VAT lines match accountant expectations
- Theme announcement / product tax info text applied (see Theme)

---

## 5. Google services (manual steps remaining)

**Guide:** `GOOGLE_SERVICES_SETUP.md`  
**API prep script:** `setup_google_services.py`  
**Machine result:** `google_setup_result.json` — **MISSING (pending)**  
**Feed sample:** `catalog/google_product_feed_sample.json` — **MISSING (pending)**

### What API prep is *intended* to do (re-run if needed)

```powershell
cd C:\Users\E-Store\Documents\icomplyproperty\shopify-import
python setup_google_services.py
```

- Inspect publications; publish products to **Online Store**
- Tag non-POA physical products `google-shopping`
- Fill empty `vendor` / `product_type`
- Write feed sample + `google_setup_result.json`

### Merchant-only (cannot finish via Admin API alone)

| # | Step | Admin / external path |
|---|------|------------------------|
| 1 | Install **Google & YouTube** sales channel | Admin → Sales channels → + |
| 2 | **Connect Google account** (OAuth) | Channel → Connect Google |
| 3 | Create/link **Merchant Center** (UK/GBP) | During channel setup / merchants.google.com |
| 4 | Choose product sync (`google-shopping` tag or all Online Store) | Channel → Products |
| 5 | Enable **free listings**; clear diagnostics | Merchant Center |
| 6 | Create **GA4** property (UK, GBP) | analytics.google.com |
| 7 | Paste Measurement ID `G-…` | Online Store → Preferences **or** channel link |
| 8 | **Google Pay** after Shopify Payments live | Settings → Payments → wallets |
| 9 | Optional Google Ads | ads.google.com |

Until OAuth + Payments are done, Google free listings and Google Pay stay **not production-ready**.

---

## 6. Shipping

| Item | Status | Detail |
|------|--------|--------|
| Origin location | **OK** | `iComply Supplys — Stockport`, 17 Woodlands Park Road, Offerton, SK2 5DE, phone 07517806082 |
| UK zone rates (GraphQL) | **OK** | `deliveryProfileUpdate` success — 8 methods active |
| Local pickup | **NOT ENABLED (API failed)** | Enable free collection in Admin |
| REST price-based rates | Failed 406 (expected; GraphQL path used) | — |
| Manual doc | Written | `SHIPPING_SETUP_MANUAL.md` |

### Active UK mainland rates (from `shipping_setup_result.json`)

**Standard tracked**

| Basket (GBP) | Shipping |
|-------------:|---------:|
| 0.00 – 49.99 | £5.95 |
| 50.00 – 149.99 | £7.95 |
| 150.00 – 399.99 | £9.95 |
| 400.00+ | **Free** |

**Express next working day**

| Basket (GBP) | Shipping |
|-------------:|---------:|
| 0.00 – 49.99 | £9.95 |
| 50.00 – 149.99 | £11.95 |
| 150.00 – 399.99 | £14.95 |
| 400.00+ | £9.95 |

**Coverage notes:** England, Wales, Scotland mainland on these rates. NI / Highlands & Islands / IoM / Channel Islands = **quote only**. Oversize/panel surcharge guidance £15; pallet/cable drums quote manually.

**Merchant remaining:** Enable **Local pickup** on Stockport location (or £0 “Collection — Stockport” rate); test checkout postcodes; connect carriers if live rates desired later.

---

## 7. Storefront API

| Item | Value |
|------|--------|
| Endpoint | `https://icomply-supplys.myshopify.com/api/2024-10/graphql.json` |
| Token test | **PASS** (prefix `1c9d538a…`) |
| Shop name returned | iComply Supplys |
| Primary domain (API) | `https://icomply-supplys.myshopify.com` |
| Products query | **PASS** (sample 10 titles + images + GBP prices) |
| Collections query | **PASS** (sample trade collections) |
| Artifacts | `storefront_api_test.json`, `final_qa.json` |

Storefront token is suitable for headless/theme integrations that need public catalog reads. **Do not commit raw tokens** to public repos.

---

## 8. Collections, pages, theme, legal (supporting)

### Collections

- **13** custom collections (0 smart) — Final QA **PASS**
- Handles: `fire-alarm-control-panels`, `smoke-detectors`, `heat-detectors`, `multi-sensor-detectors`, `manual-call-points`, `sounders-beacons`, `bases-mounting`, `interfaces-modules`, `batteries-power`, `cables-accessories`, `emergency-lighting-products`, `service-packages`, `frontpage`
- SEO: 12 bodies updated; `frontpage` published-only; all published — `collections_seo_result.json`

### Content pages (sample)

About Us, Contact, Trade Accounts, Delivery Information (possible 429 on one re-run), Fire Alarm Systems Guide, Shipping page, VAT Information, policy page mirrors.

### Shop policies

| Policy | Status |
|--------|--------|
| Privacy | Automatic Shopify policy still active (cannot overwrite until auto-management off); custom **page** `/pages/privacy-policy` present |
| Refund | Shop policy updated + page mirror |
| Shipping | Shop policy updated + page mirror |
| Terms of service | Shop policy updated + page mirror |

### Theme (Horizon — main)

- Asset API read/write OK
- Homepage still default-ish: hero **“Browse our latest products”**, announcement **“Welcome to our store”**, featured collection = `all`
- **Manual required:** see `THEME_HOMEPAGE_MANUAL.md` — hero text *UK Trade Fire & Security Supply*, collection list (panels / smoke / service packages), VAT + POA announcement copy, collection images

---

## 9. What the merchant must do manually

### Critical go-live gates (payments, domain, Google login)

1. **Shopify Payments (UK)**  
   - Settings → Payments → complete KYC, legal entity, Stockport address, UK bank payouts, ID verification  
   - Confirm cards active; leave 3-D Secure on  
   - Low-value test charge + full refund  

2. **PayPal (optional but recommended)**  
   - Connect Business PayPal under Payments  

3. **Manual / trade payment methods**  
   - Bank transfer (BACS) with order-number reference instructions  
   - Cash on collection if offering Stockport pickup  

4. **Google Pay / Apple Pay**  
   - Enable under Shopify Payments → Manage wallets after Payments verified  
   - Enable theme dynamic checkout buttons  
   - Device tests (Chrome/GPay, Safari/Apple Pay after custom domain)  

5. **Custom domain + SSL**  
   - Suggested: `shop.icomplyservices.co.uk` (or brand domain)  
   - Settings → Domains → Connect → CNAME to `shops.myshopify.com`  
   - Set primary; redirect `icomply-supplys.myshopify.com`  
   - Wait for SSL **Issued**  

6. **Google account login / services**  
   - Install Google & YouTube channel  
   - OAuth with business Google account  
   - Merchant Center free listings + diagnostics  
   - GA4 Measurement ID in Online Store preferences  
   - Re-run `python setup_google_services.py` if result/feed files still missing  

7. **Order email**  
   - Ensure staff/new-order notifications hit `icomplypropertyservices@gmail.com`  
   - Gmail filter so Shopify mail is not lost  

8. **Customer accounts**  
   - Decide optional vs required for trade  
   - Smoke-test guest + account checkout  

### Operational polish (should complete before marketing launch)

9. Theme homepage edits (`THEME_HOMEPAGE_MANUAL.md`)  
10. Enable **local pickup** free collection (`SHIPPING_SETUP_MANUAL.md`)  
11. Review **14 POA SKUs** prices/tags  
12. Confirm UK 20% VAT + optional VAT number  
13. Turn off Privacy Policy automatic management if custom privacy body must be the checkout policy  
14. Upload collection images for featured cards  
15. Remove storefront password only when gates 1–8 are green  
16. Full mobile + desktop checkout smoke test  

Full checklists: `PAYMENTS_DOMAIN_CHECKLIST.md`, `TRADE_READY_CHECKLIST.md`.

---

## 10. Admin links

| Purpose | URL / path |
|---------|------------|
| Admin home | https://admin.shopify.com/store/icomply-supplys |
| Products | https://admin.shopify.com/store/icomply-supplys/products |
| Collections | https://admin.shopify.com/store/icomply-supplys/collections |
| Orders | https://admin.shopify.com/store/icomply-supplys/orders |
| Settings — Payments | https://admin.shopify.com/store/icomply-supplys/settings/payments |
| Settings — Domains | https://admin.shopify.com/store/icomply-supplys/settings/domains |
| Settings — Shipping | https://admin.shopify.com/store/icomply-supplys/settings/shipping |
| Settings — Taxes | https://admin.shopify.com/store/icomply-supplys/settings/taxes |
| Settings — Locations | https://admin.shopify.com/store/icomply-supplys/settings/locations |
| Settings — Customer accounts | https://admin.shopify.com/store/icomply-supplys/settings/customer_accounts |
| Settings — Notifications | https://admin.shopify.com/store/icomply-supplys/settings/notifications |
| Settings — Legal / policies | https://admin.shopify.com/store/icomply-supplys/settings/legal |
| Online Store — Themes | https://admin.shopify.com/store/icomply-supplys/themes |
| Online Store — Preferences (GA) | https://admin.shopify.com/store/icomply-supplys/online_store/preferences |
| Sales channels | https://admin.shopify.com/store/icomply-supplys/settings/apps/app-status (or left nav **Sales channels**) |
| Pages | https://admin.shopify.com/store/icomply-supplys/pages |
| Storefront | https://icomply-supplys.myshopify.com |
| Google Merchant Center | https://merchants.google.com/ |
| Google Analytics | https://analytics.google.com/ |
| Google Ads (optional) | https://ads.google.com/ |

---

## 11. Agent artifact inventory

### Present (read for this report)

| File | Role |
|------|------|
| `final_qa.json` / `FINAL_QA_REPORT.md` | Final automated QA **PASS** |
| `image_audit_final.json` / `image_audit.json` | Image coverage |
| `catalog/pricing_audit.json` | Pricing/POA actions |
| `shipping_setup_result.json` / `SHIPPING_SETUP_MANUAL.md` | Shipping |
| `GOOGLE_SERVICES_SETUP.md` | Google manual guide |
| `payments_domain_result.json` / `PAYMENTS_DOMAIN_CHECKLIST.md` | Payments/domain gates |
| `setup_storefront_result.json` | Pages/policies/nav run |
| `storefront_api_test.json` | Storefront GraphQL test |
| `legal_pages_result.json` | VAT page + policies |
| `theme_result.json` / `THEME_HOMEPAGE_MANUAL.md` | Theme audit + manual |
| `collections_seo_result.json` | Collection SEO |
| `catalog/collections_result.json` | Collection creates |
| `upload_log.json` / `upload-results.json` | Product uploads |
| `TRADE_READY_CHECKLIST.md` | Broad go-live checklist |

### Missing / pending after **two** scans

| File | Impact |
|------|--------|
| `google_setup_result.json` | Google API prep run not confirmed |
| `catalog/google_product_feed_sample.json` | Feed sample not generated |
| `vat_setup_result.json` (or `vat_result.json`) | VAT country-rate API run not confirmed (product taxable + taxes_included still verified elsewhere) |

Agents folder `agents/` is empty of result dumps.

---

## 12. Verdict

| Layer | Result |
|-------|--------|
| Catalog automation (products, images, taxable, collections, storefront API) | **PASS / trade-catalog ready** |
| Shipping rates API | **PASS** (pickup manual) |
| Legal content | **PASS** (privacy auto-management caveat) |
| Theme presentation | **MANUAL outstanding** |
| Payments, domain, SSL, Google OAuth, order email | **MANUAL — go-live blocked** |
| **Composite** | **Catalog ready · Merchant gates open · Not fully trade-live until Sections 9.1–9.8 complete** |

---

*Master report for iComply Supplys Shopify build. Update `master_status.json` → `merchant_gates_complete: true` only after payments, domain, and Google login steps are finished in Admin.*
