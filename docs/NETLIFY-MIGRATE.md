# Netlify migrate plan (static export)

**Continuous deploy:** every push to `main` publishes `website/` via GitHub Actions. Required secrets and manual trigger: **[DEPLOY.md](../DEPLOY.md)**. Do not rely on the Netlify GitHub webhook alone.

Production target: **Netlify** with build-time PHP static export. Vercel is **non-final** (preview/staging only until cutover).

## Why static export is required

Netlify does not run PHP at request time. The existing `website/` tree is PHP-driven. A build-time exporter (`php bin/static-export.php` → `dist/`) is required so every public HTML page, asset, robots, and sitemap is emitted as static files Netlify can serve. Publishing raw `website/` is scaffold/smoke only and will not execute PHP.

## Phases

### P0 — Scaffold + smoke
- Land `netlify.toml` (publish = `website`, placeholder build command).
- Connect GitHub `main-web` → Netlify site; smoke-deploy static assets only.
- Document SEO preserve rules; do **not** prune keywords or rewrite sitemap XML bodies.
- Apex/www claim still required (Jack).

### P1 — Static exporter
- Implement `bin/static-export.php` (or equivalent) that renders all public routes to `dist/`.
- Switch `publish` to `dist`; real build command: `php bin/static-export.php`.
- Preserve redirects: www→apex, `/shop` + `/products` → `/pages/packages`.
- Verify robots + sitemap on the Netlify preview host before domain attach.

### P2 — Domain cutover
- Attach apex + www on Netlify; force www→apex 301.
- Point DNS / release old host once Netlify SSL + redirects green.
- Mark Vercel non-production; keep only if needed for parallel previews.
- Post-cutover: confirm live host robots/sitemap, no accidental noindex.

## SEO preserve rules

- **100% keywords** — do not prune, thin, or rewrite keyword inventories during migrate.
- **Single robots** — one canonical `robots.txt` for the live host; no conflicting copies.
- **Sitemap = live host** — sitemap URLs must use the production apex (`https://icomplypropertyservices.co.uk`); do not point sitemap at Vercel or Netlify preview hosts after cutover.
- **shop / products** — keep 301s to `/pages/packages` (see `netlify.toml`); do not drop legacy paths without redirects.
- Do **not** touch sitemap XML body content except host/base URL when switching live host.

## Domain attach steps (Netlify)

1. Site settings → Domain management → Add `icomplypropertyservices.co.uk` and `www`.
2. Follow Netlify DNS / external DNS instructions; complete Jack TXT verification / release old account if still blocked.
3. Enable HTTPS; confirm www→apex force redirect (also in `netlify.toml`).
4. Set site env if needed; keep `PHP_VERSION=8.3` for build image when exporter lands.
5. Deploy production from `main`; smoke-check apex, robots, sitemap, shop/products redirects.

## Vercel is non-final

Vercel `icomply-main-web` remains a temporary/preview surface only. It is **not** the production target. Do not treat Vercel domain or env as source of truth for cutover. After Netlify P2, decommission or demote Vercel production aliases.

## Auth blockers for Jack

- Domain claim TXT / release old registrar or DNS account.
- Netlify account access (invite Jack as owner/collaborator) if site is created under another login.
- GitHub `main-web` deploy permissions for the Netlify Git integration.
- Cursor GitHub SCM connect (unchanged) for cloud coding agents — separate from Netlify auth.
