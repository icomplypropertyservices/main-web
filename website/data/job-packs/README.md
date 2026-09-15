# Job packs

Sibling agents drop category overlays here. Packs **cannot** grow or shrink the 1,753-job master.

Supported shapes:

- JSON list of `{slug, name, service, …}`
- `{jobs:[…]}` or `{slug:{…}}`

Optional fields: `seo_title`, `h1`, `intro`, `body`, `meta_desc`, `seo_keywords`, `faq`, `focus_points`, `secondaries`, `manufacturers`, `extra_manufacturer_names`, `related`.

Merge order in `getMajorKeywords()`: keywords.json → wave-1 overlay → master synthesis → **job packs (last write wins)**.
