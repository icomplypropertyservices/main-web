# Tech workstreams (2026-09-11) — iComply main-web

**Production target: Netlify (static export).** Vercel `icomply-main-web` (RIDDLE) is **non-final** — preview/staging only until Netlify cutover. GitHub `main-web` remains source of truth. Burnley Next kept separate. See `docs/NETLIFY-MIGRATE.md`.

## Stream 1 — Website upgrade (P0 after apex claim)
- [ ] Apex/www verified (Jack TXT / release old account)
- [ ] Netlify site connected to `main-web`; P1 publish = `dist` from `php website/bin/static-export.php` (pretty URLs; no PHP at request time)
- [ ] GitHub Actions production deploy on every `main` push (`NETLIFY_AUTH_TOKEN` + `NETLIFY_SITE_ID` — see `DEPLOY.md`)
- [ ] `SITE_URL=https://icomplypropertyservices.co.uk` on Netlify (not Vercel) for production
- [ ] Confirm robots/sitemap on apex; clear accidental noindex on production host
- [ ] Sticky Call + WhatsApp (partially present in header; harden mobile sticky)
- [ ] Contact/quote form end-to-end

## Stream 1b — Wave 1 pages (after domain + Jack-confirmed copy; **no invented prices**)
New/upgrade routes in PHP `website/` pattern (exported to static `dist/` on Netlify P1+):
- `/services/epc` (domestic + non-domestic tabs)
- `/services/smoke-co-alarms`
- `/services/pat-testing`
- `/packages` hub + `/packages/let-ready` + `/packages/workplace-essentials` + `/packages/fire-ready`
- Upgrade landlord compliance / packages.php content
- Enrich existing services with Dom vs Com, cadence, standards, CTAs
- Packages prominent in nav (already under More — promote to top-level)

## Stream 2 — Mega header + full catalogue IA
Existing header has Services (by category), Areas, Brands, Shop, More.
Upgrade to true mega-nav:
- Domestic vs Commercial columns
- Packages row
- Products/Shop deep links from Marketing catalogue v4 Wave 1+
- Do not invent prices; copy via CoS/Marketing

## Stream 3 — Engineers portal
Separate repo `engineers-portal` (seeds frozen). Scaffold after Cursor SCM connect. Subdomain `engineers.` after apex stable.

## Blockers
- Domain claim TXT with Jack
- Netlify domain attach + account access (Jack)
- Confirm pretty URLs on Netlify preview (`/privacy`, `/pages/about`, `/`) before merging HMO PR #3
- Cursor GitHub SCM connect required for cloud coding agents
