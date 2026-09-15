# Deploy (Netlify production)

**Site:** [icomply-main-web](https://app.netlify.com) — `https://icomplypropertyservices.co.uk`  
**Production branch:** `main`  
**Publish directory:** `dist/` (HTML from `php website/bin/static-export.php` — **not** raw `website/`)

Netlify does **not** execute PHP at request time. Publishing `website/` served `.php` as source and 404'd pretty URLs (`/privacy`, `/pages/about`, …). The build pre-renders those routes to HTML, then splat-rewrites extensionless paths to the matching `.php` file (which now contains HTML). `/assets` is not rewritten (existing files win).

Pushes to `main` always deploy via GitHub Actions. Do not rely on the Netlify GitHub app webhook alone (PR #1 merged and live stayed stale: hubs + `/manifest.json` 404).

## How auto-deploy works

1. A push (or merge) lands on `main`.
2. Workflow [`.github/workflows/netlify-deploy.yml`](.github/workflows/netlify-deploy.yml) runs.
3. It checks out the repo, installs PHP 8.3, runs `php website/bin/static-export.php`, checks `dist/` pretty URLs, then deploys with the official [`netlify/actions/cli`](https://github.com/netlify/actions) action:
   - `netlify deploy --dir=dist --prod`
4. It also queues a Netlify-side production build of `main` (`POST /sites/{id}/builds`) so a missed Git webhook cannot leave production behind. That UI build uses `netlify.toml` (`publish = dist`).

`netlify.toml` sets `publish = "dist"`, build command `php website/bin/static-export.php`, and `ignore = "false"` so Netlify UI builds are never skipped.

## Pretty URLs

| Request | Result |
|---------|--------|
| `/` | `dist/index.html` |
| `/privacy`, `/terms`, `/contact` | 200 rewrite → pre-rendered `*.php` HTML |
| `/pages/about`, `/pages/areas`, `/pages/manufacturers`, `/pages/resources`, `/pages/keywords` | same |
| `/pages/keywords/{slug}`, `/pages/keywords/{slug}/{town}` | pre-rendered keyword matrix |
| `/assets/*` | real files; splat does not apply (`force` is off) |

Keyword hubs (`/pages/keywords/{slug}`) from `sitemap.xml` / `getMajorKeywords()` are in the **default** export, plus town combos the previous PHP router served from site chrome (popular towns × all keywords, and all towns × priority keywords such as EICR / FRA). **Electrical and gas keyword families always export the full areas list** (`/pages/keywords/{slug}/{area}` for every town). Service×area landings stay behind `--full`. The complete keyword×area matrix for every service (`--keyword-towns=all`, ~200k HTML files) is optional — it is too large for a typical Netlify publish.

Contact form POST still needs a server (the static page is GET-only). That is unchanged and out of scope for pretty URLs.

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

Local export (does not deploy):

```bash
php website/bin/static-export.php
php website/bin/check-static-export.php
```

## Netlify UI checks (one-time)

- **Production branch** = `main` (Site configuration → Build & deploy → Continuous deployment → Production branch).
- **Publish directory** = `dist` (must match `netlify.toml`; do **not** publish raw `website/`).
- **Build command** can stay empty in the UI — `netlify.toml` owns it (`php website/bin/static-export.php`).
- Unlock production deploys if the CLI logs say deployments are locked.

## What gets published

| Path | Role |
|------|------|
| `dist/` | Canonical Netlify publish dir (pre-rendered HTML + assets) |
| `website/` | PHP source tree — rendered at **build** time only |
| `website/pages/*.php` | Physical hub sources (`/pages/areas`, `/pages/services`, …) |

This is a **PHP / static** codebase. There is no `npm run build` and no Next.js app in this repo.

## Local preview (not Netlify)

```bash
php -S 127.0.0.1:8000 -t website website/router.php
```

After export, static files can be inspected under `dist/` (pretty-URL rewrites are Netlify-only).
