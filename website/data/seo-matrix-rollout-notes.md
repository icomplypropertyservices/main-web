# SEO matrix rollout notes (P090 / anti-junk)

Official rules for the electrical + gas keyword expansion.

## Jack / export

- **Each** electrical and gas keyword in `seo-matrix-electrical.md` and `seo-matrix-gas.md` gets a hub **and** a keyword×area page for the site’s **full** areas list.
- Default Netlify `static-export.php` writes that full matrix into `dist/` (not behind `--full`).
- Do not wipe wave-1 hubs or other service keyword families.

## Sitemap (P090 — priority over new volume)

- `sitemap.xml` must **only** list URLs that return HTTP **200**.
- **Never** list `/pages/{service}/{town}` (gas-systems, electrical, fire-alarms, …). Those landings are `--full` only and 404 on the default export. Do **not** mass-generate thin service×area doorway pages for all 168 towns.
- Do **not** mass-include thin keyword×area doorways in the sitemap. Featured electrical/gas × a handful of towns is allowed because those files exist and return 200.
- The full ~36k keyword×town matrix stays in `dist/` for Jack; it stays **out** of `sitemap.xml`.

## Copy / POA

- Cost, price, quote and “how much” keywords always say **POA**. Never invent a £ figure.
- UK English, Stockport / North West. No fake accreditations, reviews or prices.

## HMO packages

- HMO **package** landings stay out (`/pages/packages/hmo`, `/pages/packages/hmo-compliance`, and the PR #3 stubs).
- Keyword hubs such as `hmo-electrical-certificate` or `hmo-gas-safety` are ordinary guides, not package pages.

## AOV nationwide (fire-protection adjacent)

- Coverage is every UK mainland town or city with usual resident population **greater than 10,000**: England and Wales Census 2021 built-up areas, London boroughs, and Scottish Census 2022 localities. Northern Ireland is not on the mainland, so it is not included.
- **Each** AOV keyword gets a hub and a keyword×area page for **every** town in `aov-nationwide-areas.json`.
- AOV **service×area** pages (`/pages/aov-air-handling/{town}`) are generated with `php website/bin/generate-aov-nationwide.php`, which calls `generate-site.php` and `generate-keyword-area-pages.php` with `--areas-json`.
- Places at or under 10,000 people do not get an AOV town page.
- Sitemap lists every `/pages/aov-air-handling/{town}` page. Keyword×town stays featured-only. Other services still keep `/pages/{service}/{town}` out of the sitemap.
- Fire-protection adjacent hubs (fire alarms, emergency lighting, fire doors, fire risk assessments) are linked from AOV area pages. Service quotes are **POA**. Never invent a £ figure.
- Non-prod only. No live promote. No `--prod`.

## Out of scope

- Legionella / CSS stays on its own PR.
- No `--prod` until Ellie.