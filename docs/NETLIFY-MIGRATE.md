# Netlify migrate plan (static export)

**Continuous deploy:** every push to `main` pre-renders `website/` → `dist/` via GitHub Actions, then publishes `dist/`. Required secrets and manual trigger: **[DEPLOY.md](../DEPLOY.md)**. Do not rely on the Netlify GitHub webhook alone.

Production target: **Netlify** with build-time PHP static export.

## Why static export is required

Netlify does not run PHP at request time. The existing `website/` tree is PHP-driven. Publishing raw `website/` serves `.php` as source and 404s extensionless pretty URLs (`/privacy` 404 while `/privacy.php` 200).

`website/bin/static-export.php` renders public routes to HTML under `dist/` (plus `_redirects` / `_headers`). Pretty URLs rewrite to the matching `.php` file with status **200**. Those `.php` files are **pre-rendered HTML**, not source. Splat `/* → /:splat.php` does **not** use `force`, so `/assets/*` still wins as real files.

```bash
php website/bin/static-export.php          # core + hubs (default Netlify build)
php website/bin/check-static-export.php
php website/bin/static-export.php --full   # also keyword hubs + service×area (large)
```

`netlify.toml` sets `publish = dist` and `command = php website/bin/static-export.php`.

## Phases

### P0 — Scaffold + smoke
- Land `netlify.toml` (publish = `website`, placeholder build command).
- Connect GitHub `main-web` → Netlify site; smoke-deploy static assets only.
- Document SEO preserve rules; do **not** prune keywords or rewrite sitemap XML bodies.
- Apex/www claim still required (Jack).

### P1 — Static exporter (implemented)
- `website/bin/static-export.php` renders public routes to `dist/`.
- `publish = dist`; build command: `php website/bin/static-export.php`.
- Pretty URL 200 rewrites: `/privacy`, `/terms`, `/contact`, `/pages/about`, `/pages/areas`, `/pages/manufacturers`, `/pages/resources`, plus splat `/* → /:splat.php`.
- `/` is `dist/index.html`.
- Preserve redirects: www→apex, `/shop` + `/products` → `/pages/packages`.
- Verify robots + sitemap on the Netlify preview host before domain attach.
- Default export is core pages + hubs (services / areas / manufacturers / resources / packages). `--full` adds keyword hubs and service×area landings.

### P2 — Domain cutover
- Attach apex + www on Netlify; force www→apex 301.
- Point DNS / release old host once Netlify SSL + redirects green.
- Post-cutover: confirm live host robots/sitemap, no accidental noindex.

## SEO preserve rules

- **100% keywords** — do not prune, thin, or rewrite keyword inventories during migrate.
- **Single robots** — one canonical `robots.txt` for the live host; no conflicting copies.
- **Sitemap = live host** — sitemap URLs must use the production apex (`https://icomplypropertyservices.co.uk`); do not point sitemap at preview hosts after cutover.
- **shop / products** — keep 301s to `/pages/packages` (see `netlify.toml`); do not drop legacy paths without redirects.
- Do **not** touch sitemap XML body content except host/base URL when switching live host.

## Domain attach steps (Netlify)

1. Site settings → Domain management → Add `icomplypropertyservices.co.uk` and `www`.
2. Follow Netlify DNS / external DNS instructions; complete Jack TXT verification / release old account if still blocked.
3. Enable HTTPS; confirm www→apex force redirect (also in `netlify.toml`).
4. Set site env if needed; keep `PHP_VERSION=8.3` and `SITE_URL=https://icomplypropertyservices.co.uk` for the exporter.
5. Deploy production from `main`; smoke-check apex, robots, sitemap, shop/products redirects, and pretty URLs (`/privacy`, `/pages/about`).

## Auth blockers for Jack

- Domain claim TXT / release old registrar or DNS account.
- Netlify account access (invite Jack as owner/collaborator) if site is created under another login.
- GitHub `main-web` deploy permissions for the Netlify Git integration.
- Cursor GitHub SCM connect (unchanged) for cloud coding agents — separate from Netlify auth.
