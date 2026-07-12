# iComply Supplys — Theme & Homepage Manual (Horizon)

**Store:** iComply Supplys  
**Admin:** https://admin.shopify.com/store/icomply-supplys  
**Theme:** Horizon (Online Store 2.0)  
**Main theme ID:** `201870311756`  
**Role:** main (live)  
**Theme store ID:** `2481`  
**Currency / market:** GBP · UK · prices **include VAT** (`taxes_included: true`)  
**Date audited:** 2026-07-12  

This guide gives **exact admin clicks** to finish homepage customisation. Horizon is a section-based OS 2.0 theme; most branding and homepage layout should be done in the **theme editor**, not by hand-editing Liquid.

---

## 0. Open the live theme editor

1. Shopify admin → **Online Store** → **Themes**
2. Confirm **Horizon** shows **Current theme** (ID `201870311756`)
3. Click **Customize**
4. Top bar page selector → **Home page** (if not already there)

You are now editing `templates/index.json` for the live theme.

---

## 1. Hero heading → “UK Trade Fire & Security Supply”

### Current state (API audit)

Homepage section order today:

| Order | Section type | Editor name | Notes |
|------:|--------------|-------------|--------|
| 1 | `hero` | Hero | Heading block text: *Browse our latest products* · Button: *Shop all* → All products |
| 2 | `product-list` | Featured collection | Collection = **All products** (`all`), max 8, 4 columns |

### Exact clicks

1. In the left sidebar, under **Home page**, click the **Hero** section  
2. Expand the **Heading** block (type: Text) — currently shows *Browse our latest products*  
3. In the text field, replace all content with:

   ```text
   UK Trade Fire & Security Supply
   ```

4. Recommended heading settings (optional polish):
   - **Type preset:** H1 or H2 (largest readable hero style)
   - **Alignment:** Center (or Left if you keep left-aligned layout)
   - **Width:** Fit content or Full as preferred  
5. **Button** block (optional but recommended for trade store):
   - **Label:** `Shop fire products` (or keep `Shop all`)
   - **Link:** Collections → **Fire Alarm Control Panels**  
     *or* Collections → **All products**
6. **Hero section settings** (still on Hero):
   - Upload a trade-appropriate **Image** (panel / detector / site gear — no stock lifestyle fluff)
   - Keep **Toggle overlay** on if text sits on a photo (readability)
   - **Section height:** Medium (default) or Large if the image is strong
7. Click **Save** (top right)

### Optional second line under the hero heading

1. Still in **Hero** → **Add block** → **Text**
2. Place it under the heading in the block list
3. Suggested copy:

   ```text
   Fire detection, emergency lighting & compliance equipment for UK installers and FM teams.
   ```

4. Use a smaller type preset (e.g. paragraph / RTE), not H1

---

## 2. Feature these three collections on the homepage

Use a **Collection list** section so shoppers land on the three priority ranges.

| Collection title (admin) | Handle | Collection ID |
|--------------------------|--------|---------------:|
| Fire Alarm Control Panels | `fire-alarm-control-panels` | `686993015116` |
| Smoke Detectors | `smoke-detectors` | `686993047884` |
| Service Packages | `service-packages` | `686993375564` |

Public paths (once storefront domain is live):

- `/collections/fire-alarm-control-panels`
- `/collections/smoke-detectors`
- `/collections/service-packages`

### Exact clicks — add Collection list

1. Theme editor → **Home page**
2. Left sidebar → **Add section**
3. Choose **Collection list**
4. Drag **Collection list** so it sits **directly under Hero** (above Featured collection)
5. Click **Collection list**
6. Setting **Collections** (collection picker) → **Select**
7. Tick / add exactly these three (in this order):
   1. **Fire Alarm Control Panels**
   2. **Smoke Detectors**
   3. **Service Packages**
8. Click **Select** / **Done**
9. Layout recommendations:
   - **Layout:** Grid  
   - **Columns:** 3 (desktop)  
   - Mobile: 1 or 2 columns as the theme offers  
10. Add a heading block on the section if available:
    - **Add block** → **Text**
    - Copy: `Shop by category`
    - Type preset: H3 / H4
11. **Save**

### Exact clicks — retarget the existing Featured collection products

Horizon’s “Featured collection” is the **Product list** section (`product-list`), currently bound to **All products**.

**Option A — one product strip (recommended: panels)**

1. Click **Featured collection** (Product list)
2. Setting **Collection** → change from **All products** / Home page to **Fire Alarm Control Panels**
3. Max products: **8** (or 4)
4. Columns: **4** desktop / **2** mobile
5. Header text can stay dynamic (`{{ closest.collection.title }}`) or hard-set via the header Text block to:  
   `Fire Alarm Control Panels`
6. **View all** button should follow the selected collection automatically
7. **Save**

**Option B — three product strips (one per priority collection)**

1. Keep / retarget the existing Product list → **Fire Alarm Control Panels**
2. **Add section** → **Featured collection** (Product list) again
3. Collection → **Smoke Detectors** · max 4–8 · columns 4
4. **Add section** → **Featured collection** again
5. Collection → **Service Packages** · max 4–8 · columns 3 or 4
6. Order sections top → bottom:
   1. Hero  
   2. Collection list (3 cards)  
   3. Product list — Fire Alarm Control Panels  
   4. Product list — Smoke Detectors  
   5. Product list — Service Packages  
7. **Save**

### Collection images (strongly recommended)

Cards look empty without collection images:

1. Admin → **Products** → **Collections**
2. Open **Fire Alarm Control Panels** → **Image** → upload → **Save**
3. Repeat for **Smoke Detectors** and **Service Packages**

Use clear product photography (panel fascia, optical detector, service package graphic from `images/*-package.png` if suitable).

---

## 3. VAT note (UK trade store)

Shop setting: **`taxes_included = true`** — catalogue prices are **VAT-inclusive**.

Recommended customer-facing wording:

```text
Prices include UK VAT at 20% where applicable.
```

Longer variant (product / footer):

```text
All prices include UK VAT (20%) where applicable. VAT invoices available for trade accounts. VAT number available on request.
```

### 3A. Announcement bar (highest visibility)

1. Theme editor left sidebar → open **Header** group (or click the top announcement area on the preview)
2. Click **Announcement bar**
3. Click the existing announcement block (*Welcome to our store*)
4. Replace **Text** with:

   ```text
   Prices include UK VAT at 20% where applicable · Trade accounts welcome
   ```

5. Optional: **Add block** for a second rotating line (POA — see §4)
6. **Save**

### 3B. Product price “Tax information” toggle

Horizon Price blocks include **Tax information** (`show_tax_info`).

**Product page**

1. Theme editor top bar → **Products** → **Default product**
2. Find the **Price** block(s) in the product information area
3. Enable **Tax information**
4. **Save**

Note: Horizon’s `snippets/tax-info.liquid` prints Shopify’s standard duties/taxes/shipping strings based on cart tax state. With VAT-inclusive pricing it will lean toward “taxes included” style messaging — still enable it, and reinforce with the announcement bar copy above for trade clarity.

**Homepage product cards**

1. Home page → each **Featured collection** → expand **Product card** → **Price**
2. Currently audited: `show_tax_info: false`
3. Set **Tax information** → **ON** if you want the note under every card price (can look busy — announcement bar alone is often enough)
4. **Save**

### 3C. Footer VAT line

1. Theme editor → scroll to **Footer**
2. In a text column, **Add block** → **Text** (or edit an existing text block)
3. Paste:

   ```text
   Prices include UK VAT at 20% where applicable. iComply Supplys — trading name of iComply Property Services, Stockport SK2 5DE.
   ```

4. **Save**

### 3D. Admin tax configuration (not theme — once)

1. Admin → **Settings** → **Taxes and duties**
2. Confirm **United Kingdom** · standard VAT **20%**
3. Confirm product prices are set as **Including tax** (matches `taxes_included: true`)
4. If VAT-registered, enter registration number under **Settings** → **Store details** / tax settings as prompted by Shopify

---

## 4. POA note (Price on Application)

Many lines (and all weak/variable trade prices) are handled as **POA** in product body copy and tags by the pricing scripts. Customers still need a global explanation.

Recommended wording:

```text
POA = Price on Application. Contact us for live trade pricing on selected lines.
```

With contact:

```text
POA (Price on Application): call 07517 806082 or email icomplypropertyservices@gmail.com for live trade pricing.
```

### 4A. Announcement bar (second slide)

1. Theme editor → **Announcement bar**
2. **Add block** → announcement / text block
3. Text:

   ```text
   POA = Price on Application · Call 07517 806082 for live trade pricing
   ```

4. Leave autoplay enabled if multiple announcements (default ~5s)
5. **Save**

### 4B. Homepage text strip under collections

1. Home page → **Add section** → **Section** / **Custom liquid** / rich text style section (Horizon: often **Section** with Text blocks, or **Custom liquid**)
2. Place below Collection list
3. Text:

   ```text
   Looking for POA items or project pricing? Email icomplypropertyservices@gmail.com or call 07517 806082 — we quote panels, devices and multi-site packages for trade customers.
   ```

4. **Save**

### 4C. Product template note (optional Custom liquid)

1. Theme editor → **Products** → **Default product**
2. **Add block** → **Custom liquid** (near price or description)
3. Liquid example:

   ```liquid
   {% if product.tags contains 'poa' or product.tags contains 'POA' %}
     <p><strong>Price on Application (POA)</strong> — Contact us on
     <a href="tel:07517806082">07517 806082</a> or
     <a href="mailto:icomplypropertyservices@gmail.com">icomplypropertyservices@gmail.com</a>
     for live trade pricing.</p>
   {% endif %}
   ```

4. **Save**

(Product bodies may already include a POA paragraph from `apply_pricing_poa.py` / `apply_poa_and_taxable.py` — avoid duplicating if copy already present.)

---

## 5. Suggested final homepage section order

| # | Section | Purpose |
|---|--------|---------|
| Header | Announcement bar | VAT + POA one-liners |
| Header | Header | Logo, Main menu, search |
| 1 | **Hero** | Heading: **UK Trade Fire & Security Supply** + CTA |
| 2 | **Collection list** | Fire Alarm Control Panels · Smoke Detectors · Service Packages |
| 3 | Text / custom | Optional short VAT + POA trust line |
| 4 | **Featured collection** | Fire Alarm Control Panels products |
| 5 | **Featured collection** | Smoke Detectors (if Option B) |
| 6 | **Featured collection** | Service Packages (if Option B) |
| Footer | Footer | Policies, VAT line, contact, newsletter |

---

## 6. Header menu check (related)

1. Theme editor → **Header** → **Menu** block  
2. Confirm menu = **Main menu**  
3. Admin → **Online Store** → **Navigation** → **Main menu** should include links to the three featured collections (and other catalogue ranges)

---

## 7. Brand polish (same Customize session)

| Setting path | Recommendation |
|--------------|----------------|
| Theme settings → **Logo** | Upload iComply logo; height ~36px (current default) |
| Theme settings → **Colors** | Keep professional dark/light trade contrast; avoid pure novelty palettes |
| Theme settings → **Currency code** | Optional: enable currency code on product pages if multi-market later |
| Cart type | Drawer (current) is fine for trade browsing |
| Sticky header | Already **always** — keep |

Path: theme editor → left gear / **Theme settings** (not Home page sections).

---

## 8. Asset API soft-documentation (developer note)

Audited via Admin API `2024-10` using `shopify_client.py`.

| Item | Value |
|------|--------|
| Main theme | Horizon · ID **201870311756** · role **main** |
| GraphQL ID | `gid://shopify/Theme/201870311756` |
| Asset count | **448** keys |
| Homepage template | `templates/index.json` |
| Global settings | `config/settings_data.json` |
| Header group | `sections/header-group.json` |
| Footer group | `sections/footer-group.json` |
| Hero section file | `sections/hero.liquid` |
| Collection list | `sections/collection-list.liquid` |
| Featured collection | `sections/product-list.liquid` (schema name: Featured collection) |
| Tax snippet | `snippets/tax-info.liquid` |
| OAuth scopes present | `write_themes`, `write_theme_code`, … |
| Asset **read** | ✅ Works |
| Asset **write** | ✅ Probe write/delete of `assets/_icomply_theme_probe.txt` succeeded |
| Recommendation | Prefer **theme editor** for homepage JSON/section settings. Avoid bulk Liquid rewrites of Horizon (upgrade-fragile). If automating, PUT only `templates/index.json` / group JSON with extreme care and a full theme backup first. |

### Current `templates/index.json` structure (summary)

```text
order:
  1. hero_jVaWmY          → type: hero
       blocks: text (heading), button (Shop all → collections/all)
  2. product_list_fa6P9H  → type: product-list
       settings.collection: "all"
       max_products: 8, columns: 4
       product card price show_tax_info: false
```

### Why not fully automate homepage content?

- Horizon uses nested OS 2.0 **blocks-within-blocks** and static block IDs; hand-built JSON is easy to break.
- Image picks and collection pickers resolve to store resources best chosen in the UI.
- Shopify may further restrict Asset API writes on some theme plans; editor remains the supported path for merchants.
- This manual is the durable runbook for staff / other agents.

### Safe API read examples

```python
from shopify_client import api

THEME_ID = 201870311756
themes = api("GET", "/themes.json")
home = api("GET", f"/themes/{THEME_ID}/assets.json?asset[key]=templates/index.json")
print(home["asset"]["value"])
```

---

## 9. Post-edit QA checklist

- [ ] Desktop + mobile: hero shows **UK Trade Fire & Security Supply**
- [ ] Collection list shows exactly the three collections, correct titles, images load
- [ ] Each collection card links to the right collection URL
- [ ] Featured product grids show real products (not placeholders)
- [ ] Announcement bar shows VAT note and POA note
- [ ] Product page price area acceptable with tax info on/off decision
- [ ] Footer VAT / company line present
- [ ] No “Welcome to our store” / “Browse our latest products” placeholder copy left
- [ ] Click **Save**, then open the live storefront (incognito) and re-check
- [ ] Optional: **Online Store** → **Themes** → **…** → **Download theme file** as backup after changes

---

## 10. Contacts for POA / trade (use in theme copy)

| Channel | Value |
|---------|--------|
| Phone | 07517 806082 |
| Email | icomplypropertyservices@gmail.com |
| Address | 17 Woodlands Park Road, Offerton, Stockport SK2 5DE |
| Trading name | iComply Supplys (iComply Property Services) |

---

*Generated by the Theme / Homepage specialist agent from live Horizon theme audit (Asset API + Collections API).*
