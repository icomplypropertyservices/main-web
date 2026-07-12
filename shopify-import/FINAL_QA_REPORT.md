# FINAL QA REPORT — iComply Supplys Shopify

**Store:** `icomply-supplys.myshopify.com`  
**Brand:** iComply Supplys  
**Verified (UTC):** 2026-07-12T13:15:43Z  
**Overall:** **PASS**

Verification performed via `shopify_client.py` (Admin REST) and Storefront GraphQL API.

## Summary metrics

| Metric | Value |
|--------|-------|
| Product count | 260 |
| Products with images | 260/260 (100.00%) |
| Taxable variants | 260/260 (100.00%) |
| POA-tagged products | 0 |
| Storefront token query | PASS |
| Collections (custom+smart) | 13 (13 custom / 0 smart) |

## Checks (pass/fail)

| # | Check | Result | Value | Expected | Detail |
|---|-------|--------|-------|----------|--------|
| 1 | Product count | **PASS** | `260` | >= 250 | 260 products on store |
| 2 | Products with images (%) | **PASS** | `100.0` | >= 95% | 260/260 products have at least one image (100.00%) |
| 3 | Taxable variants (%) | **PASS** | `100.0` | 100% | 260/260 variants taxable (100.00%) |
| 4 | POA-tagged products count | **PASS** | `0` | count retrieved (non-negative integer) | 0 products tagged poa and/or price-on-application |
| 5 | Storefront token query | **PASS** | `True` | GraphQL shop query succeeds | Storefront API returned shop data |
| 6 | Collections count | **PASS** | `13` | >= 12 (master plan trade collections) | 13 custom + 0 smart = 13 collections |

## Check details

### 1. Product count
- **Result:** PASS
- Live product count: **260**
- Criterion: >= 250 (catalog upload ~249 SKUs + service packages)

### 2. % with images
- **Result:** PASS
- Products with >=1 image: **260/260 (100.00%)**
- Without images: **0**
- Criterion: >= 95%

### 3. % taxable variants
- **Result:** PASS
- Taxable variants: **260/260 (100.00%)**
- Criterion: 100% of variants must have `taxable=true` (UK VAT chargeable goods)

### 4. Count of POA tags
- **Result:** PASS
- Products with tag `poa` and/or `price-on-application`: **0**
- No products currently tagged POA.

### 5. Storefront token query
- **Result:** PASS
- Token (prefix): `1c9d538a...`
- Endpoint: `https://icomply-supplys.myshopify.com/api/2024-10/graphql.json`
- Shop name: **iComply Supplys**
- Primary domain: https://icomply-supplys.myshopify.com
- Sample products returned: 3

### 6. Collections count
- **Result:** PASS
- Custom collections: **13**
- Smart collections: **0**
- Total: **13**
- Criterion: >= 12 (matches master_plan trade collections)
- Custom handles:
  - `batteries-power` — Batteries & Power Supplies
  - `bases-mounting` — Detector Bases & Mounting
  - `emergency-lighting-products` — Emergency Lighting Products
  - `fire-alarm-control-panels` — Fire Alarm Control Panels
  - `cables-accessories` — Fire Cable & Accessories
  - `heat-detectors` — Heat Detectors
  - `frontpage` — Home page
  - `interfaces-modules` — Interfaces & Modules
  - `manual-call-points` — Manual Call Points
  - `multi-sensor-detectors` — Multi-Sensor Detectors
  - `service-packages` — Service Packages
  - `smoke-detectors` — Smoke Detectors
  - `sounders-beacons` — Sounders & Beacons

## Overall verdict

**PASS** — All six final QA checks met the trade-ready criteria.

## Artifacts

- `final_qa.json` — machine-readable results
- `FINAL_QA_REPORT.md` — this report
- Client: `shopify_client.py`
