# iComply main-web

PHP / static property-compliance site for [icomplypropertyservices.co.uk](https://icomplypropertyservices.co.uk).

Canonical tree is `website/` (pages, assets, `manifest.json`). This is **not** a Next.js app.

## Deploy

Production is **Netlify** (`icomply-main-web`). **Production branch = `main`.**

Every push to `main` deploys via GitHub Actions. Jack must add two repository secrets **once** (never commit them):

| Secret | Purpose |
|--------|---------|
| `NETLIFY_AUTH_TOKEN` | Netlify personal access token |
| `NETLIFY_SITE_ID` | Project ID for site `icomply-main-web` |

Full steps, manual deploy, and Netlify UI checks: **[DEPLOY.md](DEPLOY.md)**.

## Local

```bash
php -S 127.0.0.1:8000 -t website website/router.php
```
