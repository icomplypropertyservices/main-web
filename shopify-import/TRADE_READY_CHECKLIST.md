# iComply Supplys — Trade-Ready Checklist

Use this before opening the store to B2B / trade customers. Tick each item in Shopify Admin (or confirm via API scripts in this folder).

Store: `icomply-supplys.myshopify.com`  
Business base: **Stockport, UK** · Phone: **07517806082**  
**Status snapshot:** 2026-07-12 — storefront setup completed; catalog trade-ready pending payments/domain/Google merchant login.

---

## Live catalog status (API verified)

| Item | Status |
|------|--------|
| Products | **260** active |
| Product images | **260 / 260** have at least one image (`images/products_fixed` = 260 PNGs) |
| Collections | **13** custom (12 trade + frontpage) |
| VAT / tax | Shop `currency=GBP`, `taxes_included=True`; all 260 products have taxable variants |
| VAT page | Page handle `vat-information` present |
| Google Merchant | **Pending** — needs merchant centre login / claim by owner |
| Pages | about-us, contact, trade-accounts, delivery-information, shipping, fire-alarm-systems-guide, privacy/refund/shipping/terms policy pages |
| Policies | Refund, Shipping, Terms via shopPolicy API; Privacy via page (Shopify auto-managed privacy) |
| Navigation | Main menu + Footer updated via GraphQL (no `MENU_MANUAL.md` required) |

---

## 1. Payments

- [ ] **Shopify Payments** (or preferred UK gateway) activated and verified
- [ ] Business / payout bank account linked; identity checks complete
- [ ] Test a small live or test-mode card payment end-to-end
- [ ] Enable **invoice / Pay by bank** or **manual payment** methods if offering trade accounts
- [x] Confirm currency is **GBP (£)** — verified via Admin API (`shop.currency = GBP`)
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

- [x] Shop taxes mode: **prices include tax** (`taxes_included = true`) — GBP store
- [x] Product tax flags: **Charge tax** enabled on all 260 live SKUs (API sample full catalogue)
- [x] VAT information page published (`/pages/vat-information`)
- [ ] **Settings → Taxes and duties** reviewed for **United Kingdom** (confirm 20% standard rate in Admin UI)
- [ ] VAT registration number entered if VAT-registered (shown on invoices where required)
- [ ] Decide and document treatment of:
  - Zero-rated / exempt items (if any)
  - Installation / labour vs goods
  - Shipping taxability
- [ ] B2B reverse-charge / VAT number collection: enable customer tax ID fields if selling to VAT-registered businesses
- [ ] Sample order: verify line VAT, shipping VAT, and total match expectations
- [ ] Invoice / order PDF shows VAT correctly for accountants

---

## 4. Shipping

- [x] Shipping content published: pages `delivery-information` + `shipping`; shop **Shipping Policy** updated via API
- [x] Rate guidance documented in `storefront/shipping_notes.json` (UK mainland standard/express/collection; quote-only remote)
- [x] Shop metafield `icomply.shipping_notes` set for theme/app use
- [ ] **Settings → Shipping and delivery** zones created in Admin (minimum: **UK Mainland**) using suggested rates from shipping_notes
- [ ] Rates live at checkout (flat / weight / free above threshold)
- [ ] Optional zones: Highlands & Islands, NI, ROI, EU — with realistic lead times
- [ ] Local pickup / Stockport collection option enabled if offered
- [ ] Packaging weights/dimensions set on products that use carrier-calculated rates
- [ ] Carrier accounts (DPD, Royal Mail, etc.) connected if using live rates
- [ ] Test checkout from a UK postcode and confirm rate + ETA messaging
- [ ] Hazardous / lithium / oversized fire-trade items: note restrictions on product pages

---

## 5. Legal pages & policies

Run `setup_storefront.py` when `storefront/policies.json` and `pages.json` exist — **last run: completed OK**.

- [x] **Privacy Policy** live as page `privacy-policy` (Shopify automatic privacy management blocks shopPolicyUpdate — page + footer link is the fallback)
- [x] **Refund / Returns Policy** live via shop policy API (`refund-policy`)
- [x] **Shipping Policy** live via shop policy API (`shipping-policy`)
- [x] **Terms of Service** live via shop policy API (`terms-of-service`)
- [x] Contact page with phone **07517806082**, Stockport address, and trade enquiry email
- [x] About / trade / delivery / fire guide pages published
- [ ] Policies linked in **footer menu** and checkout — footer menu updated via API; confirm theme footer assignment + checkout legal links
- [ ] Cookie / tracking consent if using non-essential analytics (UK PECR / GDPR)
- [ ] Accessibility & data processing notes if collecting site visitor data
- [ ] Optional: turn off automatic Privacy Policy management in Admin if you want the custom body as the official shop policy

Checkout: **Settings → Legal** should list Refund / Shipping / Terms; Privacy may show Shopify auto policy until toggled.

---

## 6. Product completeness

- [x] **260** live SKUs: **Active** status confirmed via API
- [x] At least one product image on **every** live product (260/260)
- [x] Collections published (13 custom) and products assigned via upload/collection scripts
- [x] Service packages collection present (`service-packages`)
- [x] Taxable flags set on variants (catalogue-wide)
- [ ] Variants complete (size, pack qty, finish) with unique SKUs — spot-check top sellers
- [ ] Inventory tracked where physical stock is held; policy set for out-of-stock
- [ ] Product type / vendor / tags consistent with collection rules — ongoing hygiene
- [ ] Compare-at prices only used when genuine
- [ ] SEO title & meta description on top sellers
- [ ] No placeholder “Lorem ipsum” or “TBC” copy on live products
- [ ] Weight / HS code if exporting or using calculated shipping
- [ ] Trade-only products: password, B2B catalogue, or draft until accounts approved

---

## 7. Store details & branding

- [x] Store name **iComply Supplys**; city **Stockport**; country **GB**; email set
- [ ] **Phone 07517806082** — API cannot write shop phone (406); set manually under **Settings → Store details**
- [ ] Full street address / legal business name confirmed in Admin
- [ ] Customer email / sender identity correct
- [ ] Logo, favicon, brand colours in theme
- [ ] Homepage hero, trust badges, trade CTA
- [x] Main menu + Footer menu updated via GraphQL (`setup_storefront.py`)
- [ ] Theme header/footer assigned to Main menu / Footer in theme editor
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

- [x] Privacy / refund / shipping / terms content authored for UK fire-trade context
- [ ] GDPR: privacy policy matches actual data use; retention noted (review auto vs custom privacy)
- [ ] Product safety / CE / UKCA claims only where evidence exists
- [ ] Insurance and method statements available for service packages (PDF or page)
- [ ] Returns address and RMA process agreed
- [ ] Staff access roles limited (Settings → Users)
- [ ] 2FA enabled on owner account
- [ ] Backup export of products (CSV) stored securely

---

## 10. Marketing / Google

- [ ] **Google Merchant Center** — **pending merchant login** (owner must sign in / claim store)
- [ ] Google & YouTube sales channel connected in Shopify after merchant login
- [ ] Product feed approved; shipping/tax attributes match UK setup
- [ ] Search Console / Analytics tags if used

---

## 11. Go-live smoke test

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
| `storefront_final_result.json` | Storefront agent completion summary |
| `MENU_MANUAL.md` | Manual navigation clicks if menu API fails (not needed when GraphQL succeeds) |
| `storefront/*.json` | Content sources (pages, policies, nav, shipping notes) |
| `setup_collections.py` | Trade collections |
| `shopify_client.py` | Shared Admin API client |

---

## Remaining owner actions (blocking true go-live)

1. Set phone **07517806082** in **Settings → Store details** (API write blocked).
2. Configure **Payments** (Shopify Payments + bank verification).
3. Configure **Shipping zones/rates** in Admin from `shipping_notes.json` suggestions.
4. Connect **custom domain** + SSL.
5. **Google Merchant Center** login / claim (blocked on merchant account access).
6. Optional: disable automatic Privacy Policy management to use custom privacy body as official shop policy.
7. Theme: confirm header/footer menus + homepage branding.
8. End-to-end test order.

---

*Updated 2026-07-12 by storefront/trade-ready agent after successful `setup_storefront.py` run.*
