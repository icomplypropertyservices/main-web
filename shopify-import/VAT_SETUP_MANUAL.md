# UK VAT setup — iComply Supplys (manual Admin steps)

**Store:** `icomply-supplys.myshopify.com`  
**Last API run:** 2026-07-12T13:18:45.573394+00:00  
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
| `shop.taxes_included` read | Before: `True` → After: `True` |
| Set `taxes_included=false` via REST/GraphQL | **Not possible** on this shop (REST returns **HTTP 406**; GraphQL `ShopInput` does not expose a reliable write) |
| GB country present | Yes (`code=GB`, tax_name currently `GB VAT`) |
| Set GB tax rate to 0.20 via REST | **Does not stick** (automatic Shopify Tax keeps REST `tax` at `0.0`) |
| All product variants `taxable=true` | Scanned **260** products / **260** variants; updated **0**; verify all taxable: **True** |
| Customer-facing VAT page | **created** → `/pages/vat-and-pricing` |

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
PUT /admin/api/2024-10/shop.json  { "shop": { "taxes_included": false } }
→ HTTP 406 on this store

PUT /admin/api/2024-10/countries/{id}.json  { "country": { "tax": 0.20 } }
→ Response returns tax: 0.0 (Shopify Tax owns UK rates)

PUT /admin/api/2024-10/variants/{id}.json  { "variant": { "taxable": true } }
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
