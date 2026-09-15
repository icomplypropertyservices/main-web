# Category job packs (sibling agents)

Drop a JSON or CSV pack here. The integrator on PR #11 merges **content overlays** onto slugs that already exist in `job-types-master.csv` (exactly 1,753 unique slugs). Packs cannot grow or shrink that master.

## CSV columns

`slug,name,service,related,seo_title,h1,intro,body,meta_desc`

## JSON

Either a list of job objects or `{ "jobs": [ ... ] }` / `{ "slug": { ... } }`.

Optional richer fields: `faq` (list of `[question, answer]`), `focus_points`, `secondaries`, `manufacturers`.

The keyword template at `/pages/keywords/<slug>` is generated from the merged catalogue — do not add 1,753 stub PHP files.
