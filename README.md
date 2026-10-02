# iComply main-web

PHP / static property-compliance site for [icomplypropertyservices.co.uk](https://icomplypropertyservices.co.uk).

Canonical tree is `website/` (pages, assets, `manifest.json`). This is **not** a Next.js app.

Netlify cannot run PHP at request time. Production publishes **`dist/`**, built by `php website/bin/static-export.php` (pretty URLs + HTML). Do not publish raw `website/` — that serves `.php` as source and 404s `/privacy`, `/pages/about`, and other extensionless paths.

## Deploy

Production is **Netlify** (`icomply-main-web`). **Production branch = `main`.**

Every push to `main` exports `website/` → `dist/` then deploys via GitHub Actions. Jack must add two repository secrets **once** (never commit them):

| Secret | Purpose |
|--------|---------|
| `NETLIFY_AUTH_TOKEN` | Netlify personal access token |
| `NETLIFY_SITE_ID` | Project ID for site `icomply-main-web` |

Full steps, manual deploy, and Netlify UI checks: **[DEPLOY.md](DEPLOY.md)**.

## Powered by Netlify badge

Netlify injects `/.netlify/scripts/hud` on the edge for the public “Powered by Netlify” badge (and the pre-launch toolbar on private projects). The project toggle is **Project configuration → General → Powered by Netlify badge** and should stay **off**. It is not stored in git, so this repo also blocks the script on every deploy:

- `netlify.toml`, `website/_headers`, and the `_headers` written by `website/bin/static-export.php` set `Content-Security-Policy: script-src` to inline scripts plus `/assets/` only. There is no `'self'`, so `/.netlify/scripts/hud` cannot run.
- `website/includes/netlify-badge.php` removes that tag, Netlify Identity chrome, and “Powered by Netlify” nodes if they still appear in the DOM. The main footer, keyword matrix pages, and shop hubs include it.

Leave that `script-src` path-scoped to `/assets/`. A `'self'` source would allow the badge script again.

## Local

```bash
php -S 127.0.0.1:8000 -t website website/router.php
php website/bin/static-export.php
php website/bin/check-static-export.php
# optional: php website/bin/static-export.php --full
# optional: php website/bin/static-export.php --keyword-towns=all
```
