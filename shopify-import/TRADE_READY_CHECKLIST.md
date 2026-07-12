# iComply Supplys — Trade-Ready Checklist

Use this before opening the store to B2B / trade customers. Tick each item in Shopify Admin (or confirm via API scripts in this folder).

Store: `icomply-supplys.myshopify.com`  
Business base: **Stockport, UK** · Phone: **07517806082**

---

## 1. Payments

- [ ] **Shopify Payments** (or preferred UK gateway) activated and verified
- [ ] Business / payout bank account linked; identity checks complete
- [ ] Test a small live or test-mode card payment end-to-end
- [ ] Enable **invoice / Pay by bank** or **manual payment** methods if offering trade accounts
- [ ] Confirm currency is **GBP (£)**
- [ ] Disable payment methods you will not honour (e.g. unsupported wallets)
- [ ] Review **Settings → Payments** fraud / 3-D Secure defaults
- [ ] If offering credit terms: document approval process offline; do not auto-fulfil unpaid manual orders

---

## 2. Domains

- [ ] Primary custom domain purchased/connected (e.g. `www.icomplysupplys.co.uk` or brand domain)
- [ ] Domain status **Connected** under **Settings → Domains**
- [ ] Primary domain set; `myshopify.com` redirected to custom domain
- [ ] SSL certificate shows **Issued** / secure padlock on storefront
- [ ] Email sending domain / SPF-DKIM considered if using Shopify Email or transactional mail
- [ ] Update marketing materials and Google Business Profile with live URL

---

## 3. Taxes (UK VAT 20%)

- [ ] **Settings → Taxes and duties** configured for **United Kingdom**
- [ ] VAT registration number entered if VAT-registered (shown on invoices where required)
- [ ] Standard rate **20% VAT** applied to taxable products
- [ ] Confirm product tax settings: **Charge tax** enabled on taxable SKUs
- [ ] Decide and document treatment of:
  - Zero-rated / exempt items (if any)
  - Installation / labour vs goods
  - Shipping taxability (usually follows product rules in UK setup)
- [ ] B2B reverse-charge / VAT number collection: enable customer tax ID fields if selling to VAT-registered businesses
- [ ] Sample order: verify line VAT, shipping VAT, and total match expectations
- [ ] Invoice / order PDF shows VAT correctly for accountants

---

## 4. Shipping

- [ ] **Settings → Shipping and delivery** zones created (minimum: **UK Mainland**)
- [ ] Rates defined for trade parcels (e.g. flat rate, weight-based, free above threshold)
- [ ] Optional zones: Highlands & Islands, NI, ROI, EU — with realistic lead times
- [ ] Local pickup / Stockport collection option if offered
- [ ] Packaging weights/dimensions set on products that use carrier-calculated rates
- [ ] Cut-off times and delivery promises documented (see `storefront/shipping_notes.json` when present)
- [ ] Carrier accounts (DPD, Royal Mail, etc.) connected if using live rates
- [ ] Test checkout from a UK postcode and confirm rate + ETA messaging
- [ ] Hazardous / lithium / oversized fire-trade items: note restrictions on product pages

---

## 5. Legal pages & policies

Run `setup_storefront.py` when `storefront/policies.json` and `pages.json` exist, then verify in Admin:

- [ ] **Privacy Policy** live (Shopify policy or page `privacy-policy`)
- [ ] **Refund / Returns Policy** live (`refund-policy`)
- [ ] **Shipping Policy** live (`shipping-policy`)
- [ ] **Terms of Service** live (`terms-of-service`)
- [ ] Policies linked in **footer menu** and checkout (Shopify checkout policy links)
- [ ] Cookie / tracking consent if using non-essential analytics (UK PECR / GDPR)
- [ ] Contact page with phone **07517806082**, Stockport address, and trade enquiry email
- [ ] About / credentials page (NICEIC, BAFE, SSAIB, insurance — only claim what is true)
- [ ] Accessibility & data processing notes if collecting site visitor data

Checkout: **Settings → Legal** should list all four core policies (or linked pages).

---

## 6. Product completeness

- [ ] Every live SKU has: **title, description, price (GBP), status = Active**
- [ ] At least one product image with clear alt text
- [ ] Variants complete (size, pack qty, finish) with unique SKUs
- [ ] Inventory tracked where physical stock is held; policy set for out-of-stock
- [ ] Product type / vendor / tags consistent with collection rules
- [ ] Collections published and products assigned (see `setup_collections.py` / `master_plan.json`)
- [ ] Service packages clearly labelled (lead time, what is included / excluded)
- [ ] Compare-at prices only used when genuine
- [ ] SEO title & meta description on top sellers
- [ ] No placeholder “Lorem ipsum” or “TBC” copy on live products
- [ ] Weight / HS code if exporting or using calculated shipping
- [ ] Trade-only products: password, B2B catalogue, or draft until accounts approved

---

## 7. Store details & branding

- [ ] **Settings → Store details**: legal business name, Stockport address, phone **07517806082**
- [ ] Customer email / sender identity correct
- [ ] Logo, favicon, brand colours in theme
- [ ] Homepage hero, trust badges, trade CTA
- [ ] Main menu: Home, collections, key pages (run setup or follow `MENU_MANUAL.md`)
- [ ] Footer: policies, contact, company registration number if applicable
- [ ] 404 and password page reviewed if launching soft

---

## 8. Checkout & customer accounts

- [ ] Customer accounts: **optional** or **required** for trade (decide deliberately)
- [ ] Company / VAT fields captured for B2B (Shopify Markets/B2B or apps)
- [ ] Order confirmation and shipping notification emails branded
- [ ] Abandoned checkout emails on if appropriate
- [ ] Refund and cancellation process trained for staff

---

## 9. Compliance & operations

- [ ] GDPR: privacy policy matches actual data use; retention noted
- [ ] Product safety / CE / UKCA claims only where evidence exists
- [ ] Insurance and method statements available for service packages (PDF or page)
- [ ] Returns address and RMA process agreed
- [ ] Staff access roles limited (Settings → Users)
- [ ] 2FA enabled on owner account
- [ ] Backup export of products (CSV) stored securely

---

## 10. Go-live smoke test

1. [ ] Browse mobile + desktop: home → collection → product → cart → checkout
2. [ ] Place a test order (real low-value or gateway test mode)
3. [ ] Fulfil / mark shipped; confirm customer email
4. [ ] Refund test order; confirm ledger / VAT handling
5. [ ] Check Google search console / analytics tags (if used)
6. [ ] Remove password page **only** when checklist above is complete

---

## Related files

| File | Purpose |
|------|---------|
| `setup_storefront.py` | Pages, policies, metafields, shop contact, menus |
| `setup_storefront_result.json` | Last run output |
| `MENU_MANUAL.md` | Manual navigation clicks if menu API fails |
| `storefront/*.json` | Optional content sources (pages, policies, nav, shipping notes) |
| `setup_collections.py` | Trade collections |
| `shopify_client.py` | Shared Admin API client |

---

*Last prepared for iComply Supplys storefront setup agents. Update ticks in Admin as each item is completed.*
