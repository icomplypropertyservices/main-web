# SEO + AI review report

Hard gate from `docs/SEO-AI-HARD-GUIDELINES.md`. A page passes only with zero fail codes.
Noindex pages outside the coverage policy are not compared for duplicate titles, metas, FAQs or body paragraphs.

Sample: every service hub, a stratified set of service×town and keyword×town pages (fire, AOV, nurse call and barriers across Manchester, Stockport, Burnley, Liverpool, Carlisle; other services in Manchester, Stockport and Burnley; two out-of-policy noindex checks), four area hubs, six keyword hubs, two manufacturer pages, home, about, contact, one resource, and the static shop HTML already in the tree. The full keyword×town matrix is not pre-rendered.

## Totals

- Pages scanned: 138
- Pass: 138
- Fail: 0

## Fail counts by template family

| Family | Pages | Pass | Fail | Top fail codes |
| --- | ---: | ---: | ---: | --- |
| area-hub | 4 | 4 | 0 | — |
| home | 1 | 1 | 0 | — |
| keyword-area | 8 | 8 | 0 | — |
| keyword-hub | 6 | 6 | 0 | — |
| manufacturer | 2 | 2 | 0 | — |
| resource | 1 | 1 | 0 | — |
| service-area | 50 | 50 | 0 | — |
| service-hub | 59 | 59 | 0 | — |
| shop | 5 | 5 | 0 | — |
| static | 2 | 2 | 0 | — |

## Fail counts by reason

| Code | Pages |
| --- | ---: |
| — | 0 |

## Failing pages

None in this sample.

## Coverage rule used by the renderer

- Nationwide (indexable in every town in `website/data/areas.json`): fire-safety category, nurse call, access control (barriers).
- Other services: indexable for Greater Manchester towns and Burnley. Other towns render `noindex, follow`.
- Phone checked against 07517806082.
- NICEIC / Gas Safe self-claims fail. Sentences that only state the legal duty, or that explicitly deny a badge, do not fail.

