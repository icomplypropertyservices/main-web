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

## Local

```bash
php -S 127.0.0.1:8000 -t website website/router.php
php website/bin/static-export.php
php website/bin/check-static-export.php
```
