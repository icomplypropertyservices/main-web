# SEO matrix rollout notes (P090 / anti-junk)

Official rules for the electrical + gas keyword expansion.

## Jack / export

- **Each** electrical and gas keyword in `seo-matrix-electrical.md` and `seo-matrix-gas.md` gets a hub **and** a keyword×area page for the site’s **full** areas list.
- Default Netlify `static-export.php` writes that full matrix into `dist/` (not behind `--full`).
- Do not wipe wave-1 hubs or other service keyword families.

## Sitemap (P090 — priority over new volume)

- `sitemap.xml` must **only** list URLs that return HTTP **200** and do not redirect.
- Service×town pretty URLs (`/pages/electrical/{town}` and the same for gas, fire alarms and emergency lighting) 301 to the service hub because those pages are not published. They stay **out** of the sitemap even when a bespoke article exists. Other trades stay live and `noindex`.
- Do **not** put a town name into a shared paragraph. `check-town-uniqueness.php` rejects any repeated 6-word run across those articles.
- Shared area-town templates (`/pages/areas/{town}`) stay on the site and stay **out** of the sitemap (`noindex`). Manchester and Burnley use the featured area index (`index, follow`) and are listed.
- Do **not** list keyword×town doorways in the sitemap. Those pretty URLs 301 to the keyword hub. The HTML may stay in `dist/` for direct visits.

## Copy / POA

- Cost, price, quote and “how much” keywords always say **POA**. Never invent a POA figure.
- UK English, Stockport / North West. No fake accreditations, reviews or prices.

## HMO packages

- HMO **package** landings stay out (`/pages/packages/hmo`, `/pages/packages/hmo-compliance`, and the PR #3 stubs).
- Keyword hubs such as `hmo-electrical-certificate` or `hmo-gas-safety` are ordinary guides, not package pages.

## Out of scope

- Legionella / CSS stays on its own PR.
- No `--prod` until Ellie.