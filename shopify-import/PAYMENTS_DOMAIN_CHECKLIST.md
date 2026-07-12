# iComply Supplys — Payments & Domain Checklist

**Merchant action only.** These steps cannot be completed by API scripts. No secrets are stored in this repo.

| Item | Value |
|------|--------|
| Store | `icomply-supplys.myshopify.com` |
| Business | iComply Property Services / iComply Supplys |
| Location | 17 Woodlands Park Road, Offerton, Stockport SK2 5DE |
| Phone | 07517 806082 |
| Order / trade email | **icomplypropertyservices@gmail.com** |
| Suggested shop domain | `shop.icomplyservices.co.uk` (or brand equivalent) |
| Currency | **GBP (£)** |
| Market | **United Kingdom** |

**Admin base URL:** `https://admin.shopify.com/store/icomply-supplys`  
(If your store handle differs, open Admin from the shop and use the same left-nav paths below.)

**How to use:** Complete sections 1 → 6 in order. Tick each checkbox in Shopify Admin as you go. When all are done, set `payments_domain_result.json` → `status` to `complete` and fill `completed_at`.

---

## 1. Shopify Payments & PayPal (UK)

### 1A. Prerequisites before you open Payments

- [ ] You are logged in as **store owner** or a staff account with **Settings** and **Payments** permission
- [ ] **Settings → Store details** shows correct legal business name, Stockport address, phone, and contact email
- [ ] **Settings → Markets** (or **Settings → Store details** currency): store currency is **GBP (British Pound)**
- [ ] You have ready:
  - UK business bank account (sort code + account number) for payouts
  - Government ID (passport / driving licence) for identity verification
  - Company details if limited company (company number, registered address) **or** sole trader details
  - VAT number if VAT-registered (optional for Payments signup, needed for tax invoices)

### 1B. Activate Shopify Payments (preferred UK card gateway)

1. Open **Settings** (bottom-left gear) → **Payments**.
2. Under **Shopify Payments**, click **Complete account setup** / **Activate Shopify Payments** / **Set up** (wording varies by plan/region).
3. Confirm **Country / region = United Kingdom**.
4. Enter **business type** (sole trader / limited company / partnership) and legal name matching your bank/ID.
5. Enter **business address**:  
   `17 Woodlands Park Road, Offerton, Stockport SK2 5DE, United Kingdom`.
6. Enter **business phone**: `07517806082`.
7. Enter **statement descriptor** customers will see on bank statements (short, recognisable — e.g. `ICOMPLY SUPPLY` or similar within Shopify’s character limit).
8. Add **payout bank account** (UK account). Confirm currency **GBP**.
9. Complete **identity verification** prompts (upload ID / enter personal details for the account holder/director).
10. Review **payout schedule** (e.g. daily / weekly) and save.
11. Wait until status shows **Active** / **Verified** (not “Action required” or “Pending”). Check the banner on **Settings → Payments** daily until clear.
12. Under Shopify Payments, confirm **Credit cards** are enabled (Visa, Mastercard, Amex as offered).
13. Leave **3D Secure / Strong Customer Authentication** on (UK/EU default — do not disable).
14. Optional for trade: enable **Shop Pay** only if you want one-click consumer checkout; disable if you prefer classic trade card checkout only.

**If Shopify Payments is not offered / declined**

- [ ] Use **Settings → Payments → Add payment method** → choose a third-party UK gateway (e.g. PayPal complete payments, Stripe via app, or another supported provider).
- [ ] Or keep **manual payment methods** for pro-forma trade only (see 1D) and do not take live card orders until a gateway is live.

### 1C. Activate PayPal (UK)

1. Still on **Settings → Payments**.
2. Find **PayPal** (often under **Additional payment methods** or **Express checkout**).
3. Click **Activate** / **Connect PayPal**.
4. Sign in with the business PayPal account that should receive funds (prefer a **Business** PayPal linked to `icomplypropertyservices@gmail.com` or a dedicated company PayPal).
5. Approve Shopify permissions in the PayPal popup; return to Admin when connected.
6. Confirm PayPal shows **On** / **Active** on the Payments page.
7. Decide whether **PayPal Express** (button on product/cart) is wanted; enable or hide per brand preference.
8. In PayPal Business settings (paypal.com), confirm:
   - [ ] Business name matches customer-facing brand
   - [ ] Bank for withdrawals is correct
   - [ ] Currency **GBP**
   - [ ] Notification email is monitored

### 1D. Manual / trade payment methods (optional but recommended for B2B)

1. **Settings → Payments** → scroll to **Manual payment methods** → **Add manual payment method**.
2. Add methods you will honour offline, for example:
   - **Bank deposit** / **Bank transfer (BACS)** — paste sort code, account name, account number, and instruction: *“Quote order number as payment reference. Goods despatched after cleared funds unless credit terms agreed.”*
   - **Cash on collection (Stockport)** — if offering local pickup.
3. Do **not** mark unpaid manual orders as fulfilled until payment is confirmed.
4. Document credit-term approval offline (email / trade account process on the Trade Accounts page); Shopify will not auto-enforce credit limits without B2B/plus features or apps.

### 1E. Payments verification tests

- [ ] Place a **low-value live test order** with a real card (or Shopify Payments test mode if still in test and you understand the switch to live).
- [ ] Confirm order appears under **Orders** and payment status is **Paid**.
- [ ] Confirm funds / pending payout appear in **Settings → Payments → View payouts** (or PayPal activity).
- [ ] Refund the test order fully; confirm refund status and customer receipt.
- [ ] Disable any payment methods you will **not** honour.

---

## 2. Google Pay & Apple Pay

These wallets are provided **through Shopify Payments** (or compatible wallet-capable gateways). Complete **Section 1B** first.

### 2A. Google Pay

1. Open **Settings → Payments → Shopify Payments → Manage** (or **Manage payment methods**).
2. Under **Digital wallets** / **Wallets**, ensure **Google Pay** is **enabled**.
3. Confirm your Shopify Payments account is fully verified (wallets often stay off while verification is pending).
4. On a Chrome (Android or desktop) browser with a card saved to Google Pay / Chrome:
   - Open the storefront → add a product → go to cart/checkout.
   - Confirm a **Google Pay** / **GPay** button appears (product, cart, or checkout depending on theme).
5. Complete a small test purchase if possible; confirm order tags/source show wallet payment.

**If Google Pay does not show**

- [ ] Browser has a supported saved card / Google account
- [ ] Store currency is GBP and market is UK
- [ ] Theme supports dynamic checkout buttons: **Online Store → Themes → Customize → Theme settings / Product / Cart** → enable **Dynamic checkout buttons** where available
- [ ] No app is replacing checkout in a way that blocks wallets

### 2B. Apple Pay

1. Same path: **Settings → Payments → Shopify Payments → Manage**.
2. Enable **Apple Pay**.
3. Shopify will use your **primary domain** for Apple Pay domain verification once the custom domain is connected (see Section 3). Until then, Apple Pay may work on the `*.myshopify.com` domain only.
4. After the custom domain is primary and SSL is issued (Sections 3–4), re-check Apple Pay:
   - On an **iPhone/iPad/Mac** with Safari and a card in **Wallet**:
   - Visit the **custom domain** storefront (not only myshopify.com).
   - Confirm **Apple Pay** button on product, cart, or checkout.
5. Complete a small test if possible.

**If Apple Pay does not show**

- [ ] Device + Safari + region support Apple Pay
- [ ] Custom domain is **primary** and SSL is **issued**
- [ ] Dynamic checkout buttons enabled in the theme
- [ ] Wait a few minutes after domain/SSL changes for Apple domain association to propagate; hard-refresh or try a private window

### 2C. Wallet policy for trade

- [ ] Decide if trade customers should use wallets (usually fine for card-paid trade) or prefer invoice/BACS only.
- [ ] If you want card-only without wallets: disable Google Pay / Apple Pay under Shopify Payments manage screen; leave cards on.

---

## 3. Custom domain connection

**Recommended hostnames (pick one primary strategy):**

| Role | Example | Notes |
|------|---------|--------|
| Shop primary | `shop.icomplyservices.co.uk` | Good if main marketing site stays on apex/www of parent brand |
| Brand shop | `www.icomplysupplys.co.uk` | If a dedicated supplys domain is purchased |
| Redirect | `icomply-supplys.myshopify.com` | Always keep; redirect to primary after connect |

This checklist uses **`shop.icomplyservices.co.uk`** as the worked example. Substitute your chosen hostname everywhere.

### 3A. Before Shopify — DNS access

- [ ] You can log into the DNS host for **`icomplyservices.co.uk`** (registrar, Cloudflare, GoDaddy, 123-reg, etc.).
- [ ] Decide: **subdomain** (`shop.…`) vs **root/apex** (`icomplyservices.co.uk`). Subdomain is simpler and does not disturb an existing marketing site.

### 3B. Connect domain in Shopify

1. Open **Settings → Domains**.
2. Click **Connect existing domain** (do **not** Buy unless you intend Shopify to manage DNS).
3. Enter: `shop.icomplyservices.co.uk` → **Next**.
4. Shopify shows required DNS records. Typical patterns:

   **Subdomain (recommended) — CNAME**

   | Type | Name / Host | Value / Points to | TTL |
   |------|-------------|-------------------|-----|
   | `CNAME` | `shop` | `shops.myshopify.com` | Auto / 3600 |

   **Important:** Some hosts require the target exactly as Shopify displays it (often `shops.myshopify.com.`). Copy **from the Shopify Domains screen**, not from memory.

   **If connecting apex/root domain** (only if intentional)

   | Type | Name | Value |
   |------|------|--------|
   | `A` | `@` | Shopify’s current A record IP (shown in Admin — verify live; do not use outdated blog IPs) |
   | `AAAA` (if shown) | `@` | Shopify IPv6 if listed |
   | `CNAME` | `www` | `shops.myshopify.com` |

5. In your DNS panel, create the record(s) exactly as Shopify lists them.
6. Remove conflicting records for the same host (old A/CNAME to other hosts, parking pages, etc.).
7. Return to Shopify → **Verify connection** / wait for status **Connected**.
8. Propagation can take **a few minutes up to 48 hours**. Use Shopify’s domain status and an external DNS lookup if stuck.

### 3C. Set primary domain & redirects

1. **Settings → Domains**.
2. Beside `shop.icomplyservices.co.uk` (once Connected), open the menu → **Change domain type** / **Set as primary** → confirm.
3. Ensure the `*.myshopify.com` domain is set to **Redirect to primary domain**.
4. If you also added `www.…` and apex, set **only one** primary; redirect the other to primary.
5. Update external links:
   - [ ] Marketing site CTAs → new shop URL
   - [ ] Google Business Profile / social bios (if used)
   - [ ] Email signatures and trade account PDF materials

### 3D. Domain connection checks

- [ ] Visiting `https://shop.icomplyservices.co.uk` loads the Shopify storefront (not a registrar parking page)
- [ ] Visiting `https://icomply-supplys.myshopify.com` redirects to the primary custom domain
- [ ] No mixed-content warnings in the browser console on homepage

---

## 4. SSL (HTTPS)

Shopify provisions free SSL automatically for connected domains. Merchants only need to verify status.

### 4A. Confirm SSL in Admin

1. Open **Settings → Domains**.
2. Select the custom domain (`shop.icomplyservices.co.uk`).
3. Confirm **TLS/SSL certificate** status is **Issued** / **Certificate is provisioned** (not Pending / Error).
4. If **Pending** after DNS Connected:
   - Wait up to 48 hours after DNS is correct
   - Confirm no CAA DNS records block Let’s Encrypt / Shopify issuers
   - Confirm CNAME/A points only to Shopify (no proxy misconfig unless Cloudflare is set to compatible SSL mode)
5. If using **Cloudflare** (or similar proxy):
   - Prefer DNS-only (grey cloud) during first connect, **or**
   - Full (strict) SSL mode once certificates exist — avoid “Flexible” SSL (breaks checkout security assumptions)

### 4B. Browser verification

- [ ] Open the primary URL in a private window: padlock shows **Connection is secure**
- [ ] Certificate common name / SAN includes your custom domain
- [ ] Checkout URL stays on `https://` through payment step
- [ ] Apple Pay re-tested on the custom domain after SSL **Issued** (Section 2B)

### 4C. Email / brand domain note (optional, not storefront SSL)

Storefront SSL ≠ email authentication. If later using Shopify Email or a custom From domain:

- [ ] Consider SPF/DKIM for the sending domain (separate DNS records; not required for basic Shopify order emails from Shopify’s mailers)

---

## 5. Checkout customer accounts (trade)

Goal: trade buyers can create/login accounts for re-orders and address books, while remaining compliant for B2C guests if you allow them.

### 5A. Choose account mode

1. Open **Settings → Customer accounts**.
2. Review the available options (labels vary slightly by Admin version):

| Mode | When to use for iComply |
|------|-------------------------|
| **Accounts are optional** | **Recommended default** — guests can buy; trade can create accounts |
| **Accounts are required** | Stricter trade-only feel; higher cart abandonment for one-off retail |
| **Accounts are disabled** | Not recommended if you want trade re-order history |

3. Select **Accounts are optional** unless you have deliberately decided on required login.
4. Save.

### 5B. New vs classic customer accounts

Shopify may offer **Customer accounts** (new, separate login domain) and/or **Classic customer accounts**.

1. On **Settings → Customer accounts**, note which system is active.
2. If **new customer accounts** are on:
   - Review branding / logo if prompted
   - Confirm login/sign-up experience on mobile
3. If **classic accounts** are preferred for simplicity with your theme:
   - Follow Admin prompts to use classic checkout accounts if still available on your plan
4. Either way, complete a **create account → log out → log in → checkout** test.

### 5C. Trade-oriented checkout fields

1. **Settings → Checkout**.
2. Under **Customer information** / contact:
   - [ ] **Email** required (always)
   - [ ] Phone: set to **Required** or **Optional** — for trade despatch, **Required** is recommended
3. Under **Customer contact method** / marketing options: choose opt-in defaults that match UK marketing rules (soft opt-in only where lawful).
4. Company name:
   - On many themes/checkouts, **Company** appears when customer accounts or address forms include it.
   - [ ] Place a test checkout and confirm **Company** field is available for billing/shipping addresses (enable in checkout/address settings if toggle exists).
5. If on a plan with **Shopify B2B / Companies** (Plus or eligible):
   - [ ] Configure **Companies** and catalogs separately; do not assume standard accounts = B2B price lists
6. If **not** on B2B Companies:
   - [ ] Keep trade pricing/process as documented on the **Trade Accounts** page (email application to icomplypropertyservices@gmail.com)
   - [ ] Use tags/notes on customer records after manual approval (**Customers → [customer] → Tags**, e.g. `trade-approved`)

### 5D. Order processing behaviour for trade

1. **Settings → Checkout → Order processing**:
   - [ ] Review **Automatically fulfill** — for physical fire/electrical goods, prefer **do not** auto-fulfill entire orders unless digital-only
   - [ ] Review paid vs pending behaviour for manual payment methods
2. **Settings → Checkout → Customer privacy** / cookie banner: enable if using non-essential tracking (UK PECR).
3. Staff process:
   - [ ] Unpaid **Bank deposit** orders stay **Pending** until finance confirms funds
   - [ ] Trade credit orders only after offline approval

### 5E. Checkout accounts smoke test

- [ ] Guest checkout completes with GBP + UK address
- [ ] New account signup works; confirmation/login email received
- [ ] Logged-in customer sees address book / order history after a paid order
- [ ] Mobile checkout usable for both guest and account paths

---

## 6. Order notification email → icomplypropertyservices@gmail.com

Every new order must notify the business mailbox: **icomplypropertyservices@gmail.com**.

### 6A. Store sender / contact identity

1. Open **Settings → Store details**.
2. Set **Store contact email** / **Sender email** related fields so customers and Shopify system mail use a monitored address.
3. Prefer **icomplypropertyservices@gmail.com** as the operational contact (or a domain mailbox that forwards to it).
4. Save. If Shopify asks to verify the sender domain/email, complete the verification email from Shopify.

### 6B. Staff / owner notification for new orders

1. Open **Settings → Notifications**.
2. Confirm **Customer notifications** templates exist (Order confirmation, Shipping confirmation, etc.) — leave customer-facing templates on.
3. For **Staff order notifications** (name varies):
   - Open **Settings → Notifications → Staff notifications** **or**
   - **Settings → Store details** staff alerts **or**
   - Click your account avatar → **Manage account** / notification preferences for the owner user
4. Ensure the **store owner** user email is **icomplypropertyservices@gmail.com**, **or** add a staff member with that email:

   **Add staff recipient**

   1. **Settings → Users and permissions** (or **Settings → Users**).
   2. **Add users** → email: `icomplypropertyservices@gmail.com`.
   3. Grant at least permission to view **Orders** (and Notifications if listed).
   4. Accept the invite from Gmail.
   5. In that user’s notification settings, enable **Order** / **New order** email alerts.

5. Alternative path used on many stores:
   - **Settings → Notifications** → scroll to **Order confirmation** (staff copy) / **Recipient** list
   - Add `icomplypropertyservices@gmail.com` as an additional recipient if the UI offers **Add email** / **Bcc**

### 6C. Gmail deliverability checks

- [ ] Inbox (and **Spam/Promotions**) monitored for Shopify mail (`@shopify.com` / store notification senders)
- [ ] Create a Gmail filter: from Shopify / subject contains `Order` → label **Shopify Orders**, never spam
- [ ] If using a phone: enable Gmail notifications for that label

### 6D. End-to-end notification test

1. Place a test order (real low value or known test path).
2. Confirm **customer** receives Order confirmation.
3. Confirm **icomplypropertyservices@gmail.com** receives staff/new-order notification.
4. From Admin **Orders → [test order] → Send notification**, resend if needed and verify again.
5. Optionally fulfil the order and confirm shipping notification behaviour to the customer.

### 6E. Related notification hygiene

- [ ] **Settings → Notifications → Abandoned checkout** — enable only if you will act on abandoned trade/retail carts
- [ ] **Shipping confirmation** / **shipping update** templates reviewed for brand tone
- [ ] Refund notification template reviewed
- [ ] Do not put API keys, payment card data, or bank passwords into any notification template

---

## Final go-live gate (payments & domain only)

Complete all of the following before removing the store password page for public trade traffic:

| # | Gate | Done |
|---|------|------|
| 1 | Shopify Payments **or** alternative card gateway **Active** + payout bank linked | [ ] |
| 2 | PayPal connected **or** consciously declined | [ ] |
| 3 | Google Pay / Apple Pay enabled & smoke-tested **or** consciously disabled | [ ] |
| 4 | Custom domain **Connected**, set as **primary**, myshopify redirects | [ ] |
| 5 | SSL **Issued**; HTTPS padlock on primary URL + checkout | [ ] |
| 6 | Customer accounts mode set for trade; guest/login checkout tested | [ ] |
| 7 | New order email arrives at **icomplypropertyservices@gmail.com** | [ ] |
| 8 | Test order paid → refunded (or documented manual payment path) | [ ] |

When all gates pass, update `payments_domain_result.json`:

```json
"status": "complete",
"completed_at": "YYYY-MM-DD"
```

---

## Related project files

| File | Purpose |
|------|---------|
| `TRADE_READY_CHECKLIST.md` | Full pre-launch checklist (taxes, shipping, products, etc.) |
| `payments_domain_result.json` | Machine-readable status for this payments/domain workstream |
| `storefront/pages.json` | Trade Accounts / contact content referencing the Gmail address |
| `storefront/policies.json` | Legal policies with Stockport contact details |
| `setup_storefront.py` | Pages/policies automation (does **not** configure payments or DNS) |

---

## Notes for operators

- **No API automation** can fully activate Shopify Payments KYC, connect third-party PayPal OAuth, prove Apple Pay on a physical device, or click domain “Set as primary” with DNS you do not control. This checklist is intentionally merchant-facing.
- **Never commit** bank details, PayPal passwords, national ID images, or Shopify/PayPal API secrets into this repository.
- For tax (UK VAT 20%) and shipping zones, continue with `TRADE_READY_CHECKLIST.md` sections 3–4 after payments and domain are live.

---

*iComply Supplys · Payments & Domain checklist · Merchant completion required*
