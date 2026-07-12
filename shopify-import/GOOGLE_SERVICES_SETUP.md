# Google Services Setup — iComply Supplys

**Store:** `icomply-supplys.myshopify.com`  
**Admin:** https://admin.shopify.com/store/icomply-supplys  
**Plan:** Basic  
**Currency:** GBP (United Kingdom)

This guide covers connecting Google properties to the Shopify store. **OAuth / Google login steps must be completed by a store owner or staff account that has access to the merchant’s Google account.** Admin API credentials alone cannot finish Google account linking.

API prep already done (or re-runnable via `setup_google_services.py`):

- Inspected sales channels / publications
- Ensured products are published to the **Online Store** publication
- Tagged non-POA physical products with `google-shopping`
- Ensured `vendor` and `product_type` are populated
- Generated `catalog/google_product_feed_sample.json` (first 50 products)

---

## 0. Prerequisites

Before connecting Google services:

1. **Shopify Admin access** — Owner or staff with permission for Sales channels, Online Store, and Settings → Payments / Apps.
2. **Google account** — Prefer a dedicated business account (e.g. `sales@icomplysupplys.co.uk` or a Google Workspace user), not a personal-only account if possible.
3. **Business details ready**
   - Legal business name
   - UK registered address / trading address (Stockport area)
   - Phone: `07517806082`
   - Website: storefront URL (custom domain if live, else `https://icomply-supplys.myshopify.com`)
4. **Products ready for ads / free listings**
   - Active status
   - Title, description, images
   - Price in GBP (not POA / zero)
   - Vendor (brand) and product type set
   - Published to **Online Store** (required for Google & YouTube sync)
5. **Policies live** — Privacy, Refund, Shipping, Terms (Google Merchant Center reviews these).
6. **Checkout / payments** — Shopify Payments (or compatible) configured for Google Pay eligibility.

---

## 1. Google & YouTube sales channel

The **Google & YouTube** channel is the primary Shopify integration for:

- Google Merchant Center product sync
- Free listings on Google
- Google Shopping ads (optional, paid)
- YouTube product feeds (where available)

### 1.1 Install / open the channel

1. Sign in to [Shopify Admin](https://admin.shopify.com/store/icomply-supplys).
2. In the left sidebar, open **Sales channels**.
3. Click **+** (Add sales channel) if **Google & YouTube** is not listed.
4. Search for **Google & YouTube** → **Add channel**.
5. Open **Sales channels → Google & YouTube**.

### 1.2 Connect Google account (OAuth — merchant only)

1. Click **Connect Google account** (or **Get started**).
2. Choose the Google account that should own Merchant Center / Ads assets.
3. Accept Google and Shopify permission prompts.
4. If asked, create or select a **Google Merchant Center** account for the UK / GBP market.
5. Complete any **business information** and **website claim** steps (Shopify usually claims the shop domain automatically via the channel).

### 1.3 Choose products to sync

1. In Google & YouTube → **Products** (or **Overview**), set product selection:
   - **All products**, or
   - **Specific collections / tags**
2. Recommended for iComply Supplys:
   - Sync products tagged **`google-shopping`** (applied to non-POA physical products by API prep), **or**
   - Sync all Online Store products except service packages if you prefer simplicity.
3. Exclude:
   - POA / zero-price items
   - Service packages that are not shippable SKUs (if Google disapproves service-style offers)
4. Save and wait for the first sync (can take minutes to hours).

### 1.4 Verify channel publication

After install, a **Google & YouTube** publication may appear in Admin API `publications`. Until OAuth is done, only default channels appear:

| Publication   | Role                                      |
|---------------|-------------------------------------------|
| Online Store  | Required source for storefront + Google   |
| Point of Sale | POS (not required for Google)             |
| Shop          | Shop app (optional)                       |

**Note:** Google cannot list products that are not available on the Online Store sales channel.

### 1.5 Common issues

| Issue | Fix |
|-------|-----|
| Products missing in Google | Confirm Online Store publication + channel product filters |
| Disapproved products | Fix missing image, GTIN/MPN policy, policy pages, or landing page |
| Domain not claimed | Complete website verification in Merchant Center / channel setup |
| Wrong country | Set primary market to United Kingdom / GBP |

---

## 2. Google Analytics 4 (GA4)

GA4 tracks storefront traffic, funnels, and (with enhanced e‑commerce) purchases.

### 2.1 Create a GA4 property (Google side)

1. Go to [Google Analytics](https://analytics.google.com/).
2. **Admin** → **Create Property**.
3. Property name: e.g. `iComply Supplys`.
4. Reporting time zone: **United Kingdom**.
5. Currency: **GBP**.
6. Create a **Web** data stream:
   - URL: your live domain or `https://icomply-supplys.myshopify.com`
   - Stream name: `Shopify storefront`
7. Copy the **Measurement ID** (`G-XXXXXXXXXX`).

### 2.2 Add GA4 in Shopify Admin

**Option A — Online Store preferences (classic)**

1. Shopify Admin → **Online Store → Preferences**.
2. Find **Google Analytics**.
3. Paste the **Measurement ID** (`G-...`).
4. Save.
5. (If shown) enable **enhanced e‑commerce** / purchase tracking options.

**Option B — Via Google & YouTube channel**

1. Open **Sales channels → Google & YouTube**.
2. Connect / link **Google Analytics** when prompted during setup.
3. Select the GA4 property created above.

### 2.3 Verify tracking

1. Open the storefront in a private browser window.
2. In GA4: **Reports → Realtime** — confirm page views appear.
3. Complete a test checkout (or Shopify Bogus Gateway if test mode available) and confirm purchase events if enhanced measurement is enabled.
4. Exclude internal staff IPs in GA4 **Admin → Data streams → Configure tag settings → Define internal traffic** (optional but recommended).

### 2.4 Privacy / cookies

- Ensure the **Privacy Policy** mentions analytics cookies / Google Analytics.
- If using a cookie banner app, allow analytics only after consent where required (UK GDPR / PECR).

---

## 3. Google Merchant Center & free listings

Merchant Center holds the product catalog Google uses for free listings and Shopping ads.

### 3.1 Preferred path (Shopify channel)

1. Complete **Section 1** (Google & YouTube).
2. During setup, create or link **Merchant Center**.
3. Enable **Free listings** when offered (Surface: Google Search / Shopping tab / Images, etc.).
4. Confirm **Shipping** and **Returns** in Merchant Center match Shopify policies (UK delivery, trade lead times).
5. Submit for review and monitor **Diagnostics**.

### 3.2 What free listings need

- Accurate title, description, price, availability, brand
- At least one product image
- Working product landing page (Online Store)
- Valid business identity and policies
- Compliance with [Google Shopping product data specification](https://support.google.com/merchants/answer/7052112)

### 3.3 Optional: manual / supplemental feed

API prep generated a **sample** feed snapshot (not a live hosted feed):

- File: `catalog/google_product_feed_sample.json`
- Fields: `id`, `title`, `description`, `link`, `image_link`, `price`, `availability`, `brand` (+ extras)
- Product URLs use:  
  `https://icomply-supplys.myshopify.com/products/{handle}`

For production, prefer the **Google & YouTube automatic sync**. Use the sample JSON only to:

- Validate field quality
- Spot-check first 50 SKUs
- Prototype a custom feed if you later leave the official channel

### 3.4 GTIN / MPN notes (trade hardware)

Many fire/security trade products lack consumer GTINs. In Merchant Center:

- Provide **MPN** + **brand** when GTIN is unavailable (Shopify variant **SKU** maps to MPN in the sample feed).
- Use Merchant Center attribute rules carefully; avoid fake GTINs.
- Custom labels / product types help campaign structure later.

### 3.5 Shipping & tax (UK)

- Configure shipping rates in Shopify so Merchant Center can pull realistic delivery costs (or set shipping in Merchant Center).
- VAT: store is configured with taxes included where applicable — keep price presentation consistent with Google policy for UK.

---

## 4. Google Pay

Google Pay on Shopify is **not** a separate “Google sales channel.” It is a **wallet payment method** shown at checkout when supported by your payment provider.

### 4.1 If using Shopify Payments

1. Shopify Admin → **Settings → Payments**.
2. Confirm **Shopify Payments** is activated for the United Kingdom (GBP).
3. Complete business verification (legal entity, bank account, identity) if still pending.
4. Under Shopify Payments / wallet payments, ensure **Google Pay** is enabled (often on by default once Shopify Payments is live).
5. Test on a supported browser/device (Chrome + Google account with a payment method).

### 4.2 If not using Shopify Payments

- Google Pay availability depends on the third-party gateway.
- Check that gateway’s Shopify docs for Google Pay / digital wallets.
- Some gateways only support Apple Pay / PayPal, not Google Pay.

### 4.3 Storefront requirements

- HTTPS checkout (Shopify default)
- Compatible theme checkout
- Customer device/browser support (desktop Chrome / Android)

### 4.4 Current store context

| Field | Value |
|-------|--------|
| Plan | Basic |
| Currency | GBP |
| Country | United Kingdom |

Complete Shopify Payments onboarding in Admin before relying on Google Pay in production.

---

## 5. Product readiness checklist (Google Shopping)

Use this before requesting ads approval or free listings review:

- [ ] Product **status** = Active  
- [ ] Published to **Online Store**  
- [ ] Unique title with brand + model where possible  
- [ ] Description (plain text equivalent ≥ ~50–100+ characters preferred)  
- [ ] At least one image (white/clean background preferred for ads)  
- [ ] Non-zero **price** in GBP  
- [ ] **Vendor** = brand (e.g. Kentec, Advanced, Apollo, iComply Supplys)  
- [ ] **Product type** set (e.g. Fire Alarm Control Panel, Detector)  
- [ ] Tag **`google-shopping`** on physical sellable SKUs  
- [ ] SKU filled (used as MPN when no GTIN)  
- [ ] Inventory / availability policy decided (feed sample treats untracked stock as in stock)  
- [ ] Landing page loads without password (if store is password-protected, remove password before going live with Google)

### API prep script

From this repo:

```powershell
cd C:\Users\E-Store\Documents\icomplyproperty\shopify-import
python setup_google_services.py
```

The script:

1. Lists publications (REST + GraphQL)  
2. Publishes products to Online Store where needed  
3. Adds `google-shopping` to non-POA physical products  
4. Fills empty vendor / product_type  
5. Writes `catalog/google_product_feed_sample.json`  
6. Writes `google_setup_result.json`  

Re-run after large catalog imports.

---

## 6. Recommended order of operations

1. Finish storefront (theme, policies, navigation, contact).  
2. Confirm products published + tagged (`setup_google_services.py`).  
3. Activate **Shopify Payments** (for Google Pay + reliable checkout).  
4. Install **Google & YouTube** → OAuth with business Google account.  
5. Link / create **Merchant Center** → enable **free listings**.  
6. Create **GA4** property → paste Measurement ID in Online Store preferences (or link via channel).  
7. Fix Merchant Center diagnostics until products are **Approved**.  
8. (Optional) Link **Google Ads** for paid Shopping campaigns.  

---

## 7. Admin quick links

| Task | Path |
|------|------|
| Sales channels | Admin → Sales channels |
| Google & YouTube | Admin → Sales channels → Google & YouTube |
| Online Store preferences (GA) | Admin → Online Store → Preferences |
| Payments / Google Pay | Admin → Settings → Payments |
| Products | Admin → Products |
| Markets / domains | Admin → Settings → Domains / Markets |

External:

| Service | URL |
|---------|-----|
| Google Merchant Center | https://merchants.google.com/ |
| Google Analytics | https://analytics.google.com/ |
| Google Ads (optional) | https://ads.google.com/ |
| Product data spec | https://support.google.com/merchants/answer/7052112 |

---

## 8. What cannot be done via API alone

| Action | Why |
|--------|-----|
| Connect Google account OAuth | Requires interactive merchant login |
| Create Merchant Center ownership | Google account ownership |
| Claim website in Google (beyond channel automation) | Google Search Console / Merchant verification UX |
| Enable free listings review submission | Merchant Center UI |
| Paste GA4 Measurement ID (unless using limited APIs / apps) | Admin preference / channel UI |
| Complete Shopify Payments KYC | Identity / banking verification |
| Enable Google Pay wallets | Payments settings + provider eligibility |

---

## 9. Related project files

| File | Purpose |
|------|---------|
| `shopify_client.py` | Shared Admin API client |
| `setup_google_services.py` | Publications, tags, publish, feed sample |
| `google_setup_result.json` | Machine-readable result of last setup run |
| `catalog/google_product_feed_sample.json` | First 50 products feed snapshot |
| `TRADE_READY_CHECKLIST.md` | Broader go-live checklist |

---

*Last updated for iComply Supplys Shopify Basic (UK / GBP). Update this doc after custom domain go-live so Merchant Center / GA4 stream URLs match production.*
