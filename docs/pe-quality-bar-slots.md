# Page Enrichment slots — CLOSE-Q1, Q2, Q3, Q5, Q6

Website Team owns the markup. Page Enrichment owns the JSON under `website/data/close-q/`. Loaders prefer that copy when the key exists and keep the generated floor when the file or key is missing. Do not add a second Open Graph helper for thin ×town pages.

Live service×town, keyword×town and job×town HTML is `renderTownPage()` in `netlify/edge-functions/town-matrix.js`. The PHP twins (`website/templates/combo.php`, `website/templates/keyword-area.php`, `website/includes/matrix-page.php`) call `icomplyQualityBarThinBlock()` in `website/includes/quality-bar.php`.

## Loaders

| File | PHP | Edge |
| --- | --- | --- |
| `website/data/close-q/town-prose.json` | `icomplyCloseQLoad('town-prose.json')` then `icomplyCloseQTownPage($kind, $slug, $town)` | `loadCloseQTownPages()` passes `closeQPages` into `renderTownPage()` |
| `website/data/close-q/area-faqs.json` | `icomplyCloseQLoad('area-faqs.json')` inside `icomplyQualityBarHomeFaqs()`, `icomplyQualityBarAreaFaqs()`, `icomplyQualityBarAreaImages()` | Not an edge route |
| `website/data/close-q/aov-hub.json` | `icomplyCloseQLoad('aov-hub.json')` inside `icomplyQualityBarAovProseHtml()`, `icomplyQualityBarAovImages()`, `icomplyQualityBarAovFaqs()` | Not an edge route |

`/data/*` is a public 404. Static export copies `website/data/close-q/` to `dist/assets/close-q/` so the edge function can fetch `/assets/close-q/town-prose.json`. The contract path stays `website/data/close-q/`. PHP reads that path directly. FAQ objects use `q` and `a`. Loaders do not require `question` or `answer`.

Generated banks in `website/data/quality-bar-prose.json` are the fallback only.

## Q1 — thin ×town prose

Key: `pages["{kind}/{slug}/{town}"]` where `kind` is `service`, `keyword` or `job`. Field: `.body` (plain text, paragraphs split on a blank line).

| Family | Hook | Lookup |
| --- | --- | --- |
| service×town | `data-pe-slot="q1-service-town-prose"` | `pages["service/{service-slug}/{town}"]` via `icomplyQualityBarThinBlock(..., $serviceSlug)` and edge `thinProseHtml()` `ctx.peBody` |
| keyword×town | `data-pe-slot="q1-keyword-town-prose"` | `pages["keyword/{keyword-slug}/{town}"]`. PHP passes `$pageSlug` as the 8th argument of `icomplyQualityBarThinBlock()` |
| job×town | `data-pe-slot="q1-job-town-prose"` | `pages["job/{job-slug}/{town}"]` on the edge only |
| GM blurb, only when no `.body` is present | `data-pe-slot="q1-town-blurb"` | `gmTownBlurb()` inside the generated floor |

Wrapper: `icomplyQualityBarThinBlock()`. Element id on the prose div stays `q1-thin-prose`.

## Q2 — three content images

| Surface | Figure hook | Source |
| --- | --- | --- |
| Thin ×town | `q2-thin-images` | `pages[…].images[0]` → `q2-image-hero`, `[1]` → `q2-image-work`, `[2]` → `q2-image-context`. Each item is `{src, alt}`. Edge: `thinImages(..., peImages)`. PHP: `icomplyCloseQImagesHtml()` |
| Keyword hubs and job hubs | `q2-hub-images` | Generated floor until a close-q hub file is wired |
| Manchester area index | `q2-area-images` | `area-faqs.json` → `manchester_images.images` via `icomplyQualityBarAreaImages('Manchester')` |
| Other area indexes | `q2-area-images` | `icomplyQualityBarImages()` fallback |
| `/pages/aov` | `q6-aov-images` | `aov-hub.json` → `.images` via `icomplyQualityBarAovImages()` |

## Q3 — Open Graph on thin ×town

One implementation: `thinOgMeta()` in `netlify/lib/thin-quality-bar.js`, called from `renderTownPage()`.

Emits, without changing `<title>`, meta description or canonical:

- `og:title` = existing `<title>`
- `og:description` = existing meta description
- `og:url` = canonical
- `og:image` = absolute `https://icomplypropertyservices.co.uk` URL of `data-pe-slot="q2-image-hero"` (PE `images[0].src` when that page key exists)

PHP export chrome `icomplyMatrixChromeStart()` writes the same four properties for the static twin. Do not add another edge OG snippet.

## Q5 — FAQ + FAQPage JSON-LD

| Surface | Section hook | JSON-LD id | Source | Builder |
| --- | --- | --- | --- | --- |
| Homepage `/` | `data-pe-slot="q5-home-faq"` | `q5-home-faq-jsonld` | `area-faqs.json` → `homepage.faqs[{q,a}]` | `icomplyQualityBarHomeFaqs()` |
| Stockport, Bolton, Wigan area hubs | `data-pe-slot="q5-area-faq"` | `q5-area-faq-jsonld` | `area-faqs.json` → `stockport` / `bolton` / `wigan` → `.faqs[{q,a}]` | `icomplyQualityBarAreaFaqs($areaName)` |
| Other area hubs | `data-pe-slot="q5-area-faq"` | `q5-area-faq-jsonld` | Generated floor | `icomplyQualityBarAreaFaqs($areaName)` |
| Thin ×town | `data-pe-slot="q5-thin-faq"` | Edge `FAQPage` in `@graph`; PHP id `q5-thin-faq-jsonld` | `pages[…].faqs[{q,a}]` | `thinFaqs()` / `icomplyQualityBarFaqHtml()` |

Markup helper: `icomplyQualityBarFaqHtml()`. Pair reader: `icomplyCloseQFaqList()`. Section id is `faq`.

## Q6 — `/pages/aov`

Template: `website/templates/aov/directory.php`. File: `website/data/close-q/aov-hub.json`.

| Slot | Hook | Field | Function |
| --- | --- | --- | --- |
| Long copy | `data-pe-slot="q6-aov-prose"` | `.body` | `icomplyQualityBarAovProseHtml()` |
| Images | `data-pe-slot="q6-aov-images"` | `.images[{src,alt}]` | `icomplyQualityBarAovImages()` |
| FAQ | `data-pe-slot="q6-aov-faq"` and `id="q6-aov-faq-jsonld"` | `.faqs[{q,a}]` | `icomplyQualityBarAovFaqs()` |

## Sitemap note (P1-Q5) — review only, no purge

Compound `--` keyword URLs in the matrix sitemap are intentional generator output. `website/includes/keyword-variants.php` defines a mixed-radix catalogue (10,000 variants per service). `netlify/lib/variant-matrix.js` `variantSlug()` joins service, stem, audience, modifier, scope and intent with `--`, and `renderVariantSitemap()` emits those locs. They are not a stray delimiter bug.

This PR does not add or remove routes, so committed sitemap files are unchanged. Do not delete the compound locs here.
