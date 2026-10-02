# Mainland UK coverage buckets

The coverage generator records **every mainland area slug × every service hub**. It does not publish those URLs, and it does not deploy.

Live sitemap (`website/sitemap.xml`, tier 0) stays the compact hub sitemap. Coverage URLs live under `/pages/coverage/…` and are excluded from that file. Tier fragments sit in `website/data/coverage/sitemaps/` and are **not** linked from `robots.txt`.

Do not run `netlify deploy --prod`, do not promote a production deploy, and do not copy a tier fragment over `sitemap.xml`.

## What is already generated

| Piece | Path |
| --- | --- |
| Area slugs (1131, file order) | `website/data/coverage/areas-mainland.txt` |
| Guide prices | `website/data/coverage/pricing.json` |
| Service catalogue snapshot | `website/data/coverage/services.json` |
| One bucket per 40 areas | `website/data/coverage/buckets/b001.json` … |
| Matrix counts | `website/data/coverage/matrix-summary.json` |
| Sitemap tiers 1–3 | `website/data/coverage/sitemaps/` |
| Page templates | `website/templates/coverage/` |

Guide prices, when a page shows them, come only from `pricing.json`:

| Key | Service | Price |
| --- | --- | --- |
| `eicr` | Electrical (EICR) | £249 |
| `gas` | Gas safety | £85 |
| `fra` | Fire risk assessment | £350 |
| `bundle` | HMO compliance pack | £650 |

Every other service stays POA. Do not invent a fifth price.

Core services plus the existing hubs (EV chargers, plumbing, water, security, building, emergency, commercial, care homes, HMO compliance pack) are listed first. Every other hub under `website/pages/services/` and every slug in `website/data/services.json` is included as well. Aliases such as `gas-safety` → `gas-systems` are one cell, not two.

## URLs

- Index (noindex): `/pages/coverage`
- Area hub: `/pages/coverage/areas/{area-slug}`
- Service × area: `/pages/coverage/{service}/{area-slug}`

The PHP router renders these without stub files. Pending pages send `noindex`. A page is indexable only when that cell's copy is `ready`.

The default static export skips `/pages/coverage`. Set `ICOMPLY_EXPORT_COVERAGE=1` only for a non-production experiment. Stubs from `--materialize` are gitignored so a later `main` build cannot pick them up.

## Sitemap tiers

| Tier | File | What it lists |
| --- | --- | --- |
| 0 | `website/sitemap.xml` | Existing compact sitemap. No `/pages/coverage/` URLs. |
| 1 | `data/coverage/sitemaps/tier-1-hubs.xml` | Canonical hub URL for each coverage service. |
| 2 | `data/coverage/sitemaps/tier-2-areas.xml` | One area hub per mainland slug. |
| 3 | `data/coverage/sitemaps/tier-3-service-area.xml` | Service × area URLs whose copy status is `ready`. Empty until a bucket is filled. |

`php website/bin/generate-sitemap.php` still writes only tier 0. `php website/bin/generate-coverage.php` rewrites tiers 1–3 and does not touch `sitemap.xml`.

## How a sibling agent fills one bucket

Take **one** bucket. Leave every other `buckets/b*.json` alone.

1. See what is open:

   ```bash
   php website/bin/generate-coverage.php --bucket=b001
   ```

   Or read `matrix-summary.json` → `buckets_index`.

2. Claim it. A second agent gets a non-zero exit if the bucket is already claimed or published.

   ```bash
   php website/bin/generate-coverage.php --claim=b001 --agent=your-agent-name
   ```

3. For **each area** in that bucket's `areas` array, add one file:

   `website/data/coverage/content/b001/{area-slug}.json`

   ```json
   {
     "area": "abbey-village",
     "services": {
       "electrical": {
         "status": "ready",
         "intro": "At least 160 characters that mention the area name and are unique to this town and this service.",
         "meta_desc": "Optional. Falls back to a short description if omitted."
       }
     }
   }
   ```

   Include **every** slug from `services.json`. A missing service keeps the cell pending.

   Copy rules:

   - `intro` is at least 160 characters.
   - `intro` contains the area's display name (for example `Abbey Village`).
   - Do not reuse the scaffold sentence `waiting for a local write-up`.
   - Do not paste the same intro twice inside the bucket. Publish rejects duplicate intros.
   - Prices, if you mention them, must match `pricing.json`. Leave unpriced services as POA.

4. Refresh the tier fragments, then check structure. `--verify` alone does not rewrite files, so run the generator after you add or edit content:

   ```bash
   php website/bin/generate-coverage.php
   ```

   `--strict` fails until every cell in the whole matrix is ready. Use that only when the full matrix is filled, not for a single bucket.

5. Optional local stubs (gitignored, skipped by the default export):

   ```bash
   php website/bin/generate-coverage.php --materialize=b001
   ```

6. When every cell in the bucket is ready:

   ```bash
   php website/bin/generate-coverage.php --publish-bucket=b001
   ```

   That marks the bucket `published` and rebuilds tier 3 so those URLs appear in the **data** fragment only. It does not edit `sitemap.xml` and it does not deploy.

7. Release a claim you cannot finish:

   ```bash
   php website/bin/generate-coverage.php --release=b001 --agent=your-agent-name
   ```

   The agent name must match the claim. Content files you already committed can stay; the next agent continues them.

## Regenerating

```bash
php website/bin/generate-coverage.php
php website/bin/check-coverage.php
```

Re-running the generator keeps claimed and published buckets intact. New slugs appended to `areas-mainland.txt` are placed in new buckets. `--force-repartition` rebuilds chunks of 40 only when every bucket is still `open`.

## Out of scope for bucket agents

- `netlify deploy --prod`, production promote, or editing the Netlify production site
- Adding `/pages/coverage/` to `website/sitemap.xml` or `robots.txt`
- Rewriting `website/pages/pricing.php` (that page is a separate guide)
- Committing `website/pages/coverage/**` stubs
