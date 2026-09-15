# Deploy (Netlify production)

**Site:** [icomply-main-web](https://app.netlify.com) — `https://icomplypropertyservices.co.uk`  
**Production branch:** `main`  
**Publish directory:** `website/` (PHP / static tree — **not** Next.js)

Pushes to `main` always deploy via GitHub Actions. Do not rely on the Netlify GitHub app webhook alone (PR #1 merged and live stayed stale: hubs + `/manifest.json` 404).

## How auto-deploy works

1. A push (or merge) lands on `main`.
2. Workflow [`.github/workflows/netlify-deploy.yml`](.github/workflows/netlify-deploy.yml) runs.
3. It checks out the repo, verifies `website/manifest.json` and hub files exist, then deploys with the official [`netlify/actions/cli`](https://github.com/netlify/actions) action:
   - `netlify deploy --dir=website --prod`
4. It also queues a Netlify-side production build of `main` (`POST /sites/{id}/builds`) so a missed Git webhook cannot leave production behind, and so any PHP / ServerlessWP runtime configured in the Netlify UI still runs.

`netlify.toml` sets `publish = "website"`, a verify-only build command (no `npm` / Next.js), and `ignore = "false"` so Netlify UI builds are never skipped.

## Secrets Jack must set once

Add these in **GitHub → `icomplypropertyservices/main-web` → Settings → Secrets and variables → Actions → New repository secret**.  
**Never commit tokens or site IDs.**

| Secret | What it is | Where to copy it |
|--------|------------|------------------|
| `NETLIFY_AUTH_TOKEN` | Netlify personal access token (PAT) | Netlify → [User settings → Applications → Personal access tokens](https://app.netlify.com/user/applications) → **New access token**. Copy once; it is not shown again. |
| `NETLIFY_SITE_ID` | Netlify project / site ID for **icomply-main-web** | Netlify → site **icomply-main-web** → **Project configuration → General → Project details → Project information** → **Project ID** (API / CLI still call this `site_id`). |

After both secrets exist, re-run the workflow (or push to `main`). Until they are set, the workflow fails with an explicit error naming the missing secret.

If Jack resets his Netlify password, the PAT is invalidated — generate a new token and update `NETLIFY_AUTH_TOKEN`.

## Manual deploy

Any one of these publishes current `main` to production:

1. **GitHub Actions (preferred):** Actions → **Deploy to Netlify** → **Run workflow** → branch `main`.
2. **Netlify UI:** Deploys → **Trigger deploy** → **Deploy site** (production). If deploys are **locked**, unlock production first.
3. **Empty commit / push to `main`:**
   ```bash
   git checkout main
   git pull origin main
   git commit --allow-empty -m "chore: trigger Netlify production deploy"
   git push origin main
   ```

## Netlify UI checks (one-time)

- **Production branch** = `main` (Site configuration → Build & deploy → Continuous deployment → Production branch).
- **Publish directory** = `website` (should match `netlify.toml`; do not set a Next.js or `dist` publish unless a static exporter actually exists).
- **Build command** can stay empty in the UI — `netlify.toml` owns it.
- Unlock production deploys if the CLI logs say deployments are locked.

## What gets published

| Path | Role |
|------|------|
| `website/` | Canonical site (hubs, assets, `manifest.json`, robots, sitemap) |
| `website/pages/*.php` | Physical hub files (`/pages/areas`, `/pages/services`, …) |
| Repo-root `pages/`, `manifest.json` | Mirrors for ServerlessWP when docroot is the repo root — edit `website/` only |

This is a **PHP / static** codebase. There is no `npm run build` and no Next.js app in this repo.

## Local preview (not Netlify)

```bash
php -S 127.0.0.1:8000 -t website website/router.php
```
