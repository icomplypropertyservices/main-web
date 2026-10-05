# Page Enrichment slots — CLOSE-Q1, Q2, Q3, Q5, Q6

Website Team owns the markup. Page Enrichment owns the copy and image files that drop into these hooks. Do not add a second Open Graph helper for thin ×town pages.

Live service×town, keyword×town and job×town HTML is `renderTownPage()` in `netlify/edge-functions/town-matrix.js`. The PHP twins (`website/templates/combo.php`, `website/templates/keyword-area.php`, `website/includes/matrix-page.php`) call the same slot names through `website/includes/quality-bar.php`.

## Q1 — thin ×town prose

Shared fragment banks: `website/data/quality-bar-prose.json`.

| Family | Hook | Where |
| --- | --- | --- |
| service×town | `data-pe-slot="q1-service-town-prose"` | Edge `thinProseHtml()`; PHP `icomplyQualityBarThinProseHtml('service', …)` |
| keyword×town | `data-pe-slot="q1-keyword-town-prose"` | Edge `thinProseHtml()`; PHP `icomplyQualityBarThinProseHtml('keyword', …)` |
| job×town | `data-pe-slot="q1-job-town-prose"` | Edge `thinProseHtml()` only (job×town is not a PHP template) |
| GM blurb inside the prose block | `data-pe-slot="q1-town-blurb"` | Existing `gmTownBlurb()` text. Replace the blurb, not the whole floor, when the pack is ready. |

Wrapper for the PHP twins: `icomplyQualityBarThinBlock()`.

Default copy already renders at least 800 words of town-and-subject prose. PE copy should keep price on application, and on gas pages only the sentences `Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.` and `iComply is not Gas Safe registered.`

## Q2 — three content images

Partial: `icomplyQualityBarImages($serviceSlug, $altPrefix, $slot)` in `website/includes/quality-bar.php`.

Edge twin: `thinImages()` in `netlify/lib/thin-quality-bar.js`.

Each figure contains three `<img>` hooks:

- `data-pe-slot="q2-image-hero"`
- `data-pe-slot="q2-image-work"`
- `data-pe-slot="q2-image-context"`

Figure hooks:

| Surface | `data-pe-slot` |
| --- | --- |
| Thin ×town | `q2-thin-images` |
| Keyword hubs and job hubs | `q2-hub-images` |
| Manchester / Burnley area index (`templates/area-index.php`) | `q2-area-images` |
| `/pages/aov` | `q6-aov-images` |

Hero file is `/assets/images/services/{service-slug}.jpg`. The second image is `{service-slug}-photo.jpg` when that asset exists, otherwise another file already in `website/assets/images/services/`. Swap the `src` on the three hooks. Keep the three `src` values different.

## Q3 — Open Graph on thin ×town

One implementation: `thinOgMeta()` in `netlify/lib/thin-quality-bar.js`, called from `renderTownPage()`.

Emits, without changing `<title>`, meta description or canonical:

- `og:title` = existing `<title>`
- `og:description` = existing meta description
- `og:url` = canonical
- `og:image` = absolute `https://icomplypropertyservices.co.uk` URL of `data-pe-slot="q2-image-hero"`

PHP export chrome `icomplyMatrixChromeStart()` writes the same four properties for the static twin. Do not add another edge OG snippet. If a later main commit already calls `thinOgMeta()`, keep that call.

## Q5 — FAQ + FAQPage JSON-LD

| Surface | Section hook | JSON-LD id | Builder |
| --- | --- | --- | --- |
| Area hubs (`templates/area.php` and `templates/area-index.php`) | `data-pe-slot="q5-area-faq"` | `q5-area-faq-jsonld` | `icomplyQualityBarAreaFaqs($areaName)` |
| Homepage `/` | `data-pe-slot="q5-home-faq"` | `q5-home-faq-jsonld` | `icomplyQualityBarHomeFaqs()` |
| Thin ×town | `data-pe-slot="q5-thin-faq"` | inside the page `@graph` on the edge (`FAQPage`); PHP id `q5-thin-faq-jsonld` | `thinFaqs()` / `icomplyQualityBarFaqHtml()` |

Markup helper: `icomplyQualityBarFaqHtml()`. Section id is `faq`. Default questions are price-on-application and local-cover copy. PE may replace the question and answer strings.

## Q6 — `/pages/aov`

Template: `website/templates/aov/directory.php` (not `/pages/services/aov`, which is not the live index).

| Slot | Hook |
| --- | --- |
| Long copy | `data-pe-slot="q6-aov-prose"` via `icomplyQualityBarAovProseHtml()` |
| Images | `data-pe-slot="q6-aov-images"` |
| FAQ | `data-pe-slot="q6-aov-faq"` and `id="q6-aov-faq-jsonld"` via `icomplyQualityBarAovFaqs()` |

## Sitemap note (P1-Q5) — review only, no purge

Compound `--` keyword URLs in the matrix sitemap are intentional generator output. `website/includes/keyword-variants.php` defines a mixed-radix catalogue (10,000 variants per service). `netlify/lib/variant-matrix.js` `variantSlug()` joins service, stem, audience, modifier, scope and intent with `--`, and `renderVariantSitemap()` emits those locs. They are not a stray delimiter bug.

This PR does not add or remove routes, so committed sitemap files are unchanged. Do not delete the compound locs here.
