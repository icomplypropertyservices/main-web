# SEO matrix rollout notes (P090 / anti-junk)

Official rules for the electrical + gas keyword expansion.

## Jack / export

- **Each** electrical and gas keyword in `seo-matrix-electrical.md` and `seo-matrix-gas.md` gets a hub **and** a keyword×area page for the site’s **full** areas list.
- Default Netlify `static-export.php` writes that full matrix into `dist/` (not behind `--full`).
- Do not wipe wave-1 hubs or other service keyword families.

## Sitemap (P090 — priority over new volume)

- `sitemap.xml` must **only** list URLs that return HTTP **200**.
- **Fire protection** (`fire-safety` category, AOV included) has a service×area page for **every** area slug, and those URLs **are** in `sitemap.xml`. AOV is the quality page. Do **not** mass-generate thin fire keyword×town copies.
- **Non-fire** service×area is Manchester and Burnley only, and those URLs stay **out** of `sitemap.xml`.
- **Never** list `/pages/electrical/{town}` or `/pages/gas-systems/{town}`. Electrical and gas stay on keyword×town pages.
- Do **not** mass-include thin keyword×area doorways in the sitemap. Featured electrical/gas × a handful of towns is allowed because those files exist and return 200.
- The full ~36k keyword×town matrix stays in `dist/` for Jack; it stays **out** of `sitemap.xml`.

## Copy / POA

- Cost, price, quote and “how much” keywords always say **POA**. Never invent a £ figure.
- Exception Jack set: a standard written fire risk assessment is **£350**. Do not copy that fee onto other services.
- UK English, Stockport / North West. No fake accreditations, reviews or prices.

## HMO packages

- HMO **package** landings stay out (`/pages/packages/hmo`, `/pages/packages/hmo-compliance`, and the PR #3 stubs).
- Keyword hubs such as `hmo-electrical-certificate` or `hmo-gas-safety` are ordinary guides, not package pages.

## Out of scope

- Legionella / CSS stays on its own PR.
- No `--prod` until Ellie.