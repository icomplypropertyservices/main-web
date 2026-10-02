# SEO + AI hard guidelines

A page **passes** only when `scripts/seo_ai_page_review.py` records zero fail codes. There is no weighted score and no “mostly fine” grade. Thresholds below are the constants in that script. If the script and this file disagree, fix both in the same change.

This is a content and template gate for `icomplypropertyservices/main-web`. It is not a production deploy. Do not run `netlify deploy --prod` from this work.

Phone on every indexable non-shop page: **07517806082** (`tel:` may also be `447517806082`). Office postcode used in copy: **SK2**.

## How to run

```bash
ICOMPLY_STATIC_EXPORT=1 php website/bin/render-seo-sample.php /tmp/seo-ai-sample
python3 scripts/seo_ai_page_review.py --skip-render --out /tmp/seo-ai-sample --report docs/SEO-AI-REVIEW-REPORT.md
```

Omit `--skip-render` to render and scan in one step. The renderer writes a stratified sample, not the full keyword × town matrix. Shop HTML already in `website/shop/` is scanned from disk (`family: shop`). Exit code 1 means at least one page failed.

## Coverage (what may be indexed)

`seo_local_landing_indexable()` in `website/includes/seo.php`:

- **Nationwide:** every service in the `fire-safety` category, plus `nurse-call` and `access-control` (barriers).
- **Greater Manchester and Burnley only:** every other service. Region comes from `website/includes/area-profiles.php`.
- Out-of-policy local landings must render `noindex, follow`. An indexable page that should be noindex fails `coverage_out_of_policy`. A page marked expect-index that is noindex fails `unexpected_noindex`.

Noindex pages are excluded from duplicate title, meta, H1, boilerplate and FAQ comparisons.

## Pass/fail rubric

### Titles

| Code | Rule |
| --- | --- |
| `title_missing` | No `<title>`. |
| `title_brand_only` | Title is only “Icomply” or “Icomply Property Services”. |
| `title_length` | Visible title length is outside **30–65** characters. `seo_document_title()` enforces this. |
| `title_duplicate` | Same title on two indexable pages. A service hub and a keyword hub must not share a title. Use “guide” on keyword titles. |

### Meta descriptions

| Code | Rule |
| --- | --- |
| `meta_missing` | No meta description. |
| `meta_length` | Length outside **70–160** characters. `seo_fit_meta()` enforces this. |
| `meta_duplicate` | Same description on two indexable pages. |
| `meta_town_swap` | Within one family + service + keyword, descriptions match after the town name is replaced. Swapping “Manchester” for “Carlisle” is not a unique meta. |
| `meta_keyword_list` | Five or more commas. Write a sentence, not a keyword list. |
| `meta_keywords_stuffing` | A `meta name="keywords"` tag with more than six items. Do not emit that tag. |

### Canonicals

| Code | Rule |
| --- | --- |
| `canonical_missing` | No canonical link. |
| `canonical_not_absolute` | Canonical is not `http://` or `https://`. |
| `canonical_duplicate` | Two scanned pages share a canonical. |
| `canonical_mismatch` | Canonical path does not contain the page path (home must be `/`). |

### H1

| Code | Rule |
| --- | --- |
| `h1_missing` | No H1. |
| `h1_multiple` | More than one H1. |
| `h1_short` | H1 shorter than 12 characters. Short names such as “EICR” must be extended (for example “EICR from Stockport”). |
| `h1_duplicate` | Same H1 on two indexable pages. |
| `h1_missing_place` | Indexable `service-area`, `keyword-area` or `area-hub` H1 does not contain the town name. Keep a space before “in {Town}” so the text is not glued (`Installationin`). |

### Thin content, duplicates, stuffing

Prose is the text of `p`, `li`, `h2` and `h3` inside `#main-content` or `<main>`, excluding `[data-seo-shared="1"]`.

| Code | Rule |
| --- | --- |
| `thin_content` | Indexable page has fewer than **160** prose words. |
| `boilerplate_paragraph` | A paragraph of at least **28** words matches another indexable page in the same family after the page’s own area, service name and keyword name are replaced. Shared stock/district sentences must include something that survives that replacement (the outward-code string and a service-specific angle). |
| `keyword_stuffing` | A token of 5+ letters, other than the page topic, appears at least **14** times and is more than **3.5%** of prose tokens. Topic tokens are words from the H1, the town, the service name and the keyword name. Do not repeat a second word (or the town, once it is no longer in the H1) in every card heading. |
| `local_specific_missing` | Indexable local family prose has no UK outward code other than SK2. `area-profiles.php` must supply a district string that is not only the town name. |
| `identical_faq_across_towns` | The same question+answer, after replacing only the town name, appears for two towns in the same service or keyword. |
| `faq_block_missing` | Indexable `service-area` or `keyword-area` has no FAQ inside `[data-seo-faq="1"]`. Navigation `<details>` do not count. |
| `internal_links_thin` | Fewer than **3** internal links in main content. |
| `ai_filler` | Two or more filler phrases: “in today's world”, “look no further”, “nestled”, “elevate your”, “cutting-edge”, “state-of-the-art”, “when it comes to”, “whether you're”, “whether you are”, “peace of mind”, “one-stop shop”, “second to none”, “tailored to your needs”, “go above and beyond”, “it's important to note”, “delve into”, “comprehensive solution”. |
| `ai_synonym_churn` | Five or more of these hype tokens in prose: expert, professional, specialist, qualified, certified, trusted, leading, premier, dedicated, comprehensive, bespoke, tailored, renowned, seasoned. |

### Schema

JSON-LD must parse. `@context` must contain `https://schema.org`. An `Offer` with `availability` ending `InStock` and no `price` / `lowPrice` / `highPrice` fails `schema_offer_price` (do not invent a price). An `FAQPage` with an empty question or answer fails `schema_faq_incomplete`. Missing JSON-LD on an indexable page fails `schema_missing`. Invalid JSON fails `schema_invalid_json`. Missing context or type fails `schema_missing_context`.

### Core Web Vitals basics

| Code | Rule |
| --- | --- |
| `cwv_viewport` | No viewport meta. |
| `cwv_lang` | `<html lang>` does not start with `en`. |
| `cwv_img_alt` | An image in main content has no alt text. |
| `cwv_img_dimensions` | An image in main content lacks `width` and `height`. |
| `cwv_lcp_lazy` | The first image in main content is `loading="lazy"`. |
| `cwv_preconnect` | A cross-origin stylesheet has no matching `preconnect`. |

`#main-content` wraps the footer on PHP pages, so footer and card images count.

### Banned claims and E-E-A-T

There is **no** verified accreditation policy in this repo. Do not claim NICEIC, Gas Safe registration, BAFE, CHAS, SafeContractor, ISO 9001, or “award-winning” for Icomply.

| Code | Rule |
| --- | --- |
| `banned_claim_niceic` | A sentence contains NICEIC and is not a denial (`do not`, `does not`, `don't`, `not claim`, `not print`, `no niceic`). |
| `banned_claim_gas_safe` | A sentence contains “Gas Safe registered” **and** we/our/Icomply, and is not a denial. A sentence that only states a landlord’s legal duty, with no we/our/Icomply, is allowed. |
| `banned_claim_badge` | BAFE, CHAS, SafeContractor, award-winning, or ISO 9001 outside a denial sentence. |

`seo_unverified_badge_label()` must drop keyword chips whose names contain NICEIC, BAFE, CHAS, SafeContractor, award-winning, ISO 9001, or Gas Safe. Do not link “NICEIC Certified” from the footer. `scrub_unverified_accreditation()` rewrites self-claims in keyword prose at render time. Do not edit a fake badge back into a template.

### AI-slop (how the gate treats it)

- Generic filler and synonym churn: the phrase lists above.
- Repeated boilerplate: the 28-word paragraph test. Town-name swap is not local writing.
- Identical FAQs across towns: the FAQ test. Every answer on a town page must include that town’s outward-code string from `area-profiles.php`.
- No local specifics: outward code required on local families. Unknown towns use districts `unspecified` and fail.
- Do not invent drive times. Travel copy is “scheduled from our Stockport SK2 base”.

### Families

`data-seo-family` on `<body>`: `home`, `static`, `service-hub`, `service-area`, `keyword-hub`, `keyword-area`, `area-hub`, `manufacturer`, `resource`, `shop`.

## What a fix looks like

Change the shared template (`combo.php`, `keyword-area.php`, `service.php`, `header.php`, `local-content.php`, shop HTML) so the next sample moves pages to pass. A report that only lists failures, with the same doorway paragraph still shipping, does not meet this gate.
