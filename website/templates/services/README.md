# Legacy per-service templates (unused)

Service × area pages use `templates/combo.php` via `renderServiceAreaPage()` /
`comboTemplatePath()` in `includes/render.php`.

These slug-specific files are retained only as content reference and are **not**
loaded at runtime. Safe to delete later once blurbs in `data/service-meta.json`
cover any unique copy you still need.
