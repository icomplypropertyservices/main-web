# `/pages/*.php` mirrors

Property Manager’s live crawl expects **physical** `/pages/{hub}.php` files (same pretty-URL pattern as working `/pages/services.php` and `/pages/keywords.php`).

This directory is a **repo-root mirror** for ServerlessWP / Netlify when the document root is the repository root.

Canonical implementations live under `website/pages/` (this repo’s publish directory and PHP `SITE_ROOT`):

- `website/pages/areas.php` → areas hub
- `website/pages/manufacturers.php` → manufacturers hub
- `website/pages/resources.php` → resources hub
- `website/pages/resources/{guide}.php` → the six resource guides
- `website/pages/services.php` / `website/pages/keywords.php` → same pattern as live

Do not paste a separate Netlify-only tree here. Edit the `website/pages/` files; these mirrors only `require` them.
