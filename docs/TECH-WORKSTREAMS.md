# Tech workstreams (2026-09-11) — iComply main-web

Production: Vercel `icomply-main-web` (RIDDLE) ← GitHub `main-web`. Burnley Next kept separate.

## Stream 1 — Website upgrade (P0 after apex claim)
- [ ] Apex/www verified (Jack TXT / release old account)
- [ ] `SITE_URL=https://icomplypropertyservices.co.uk` in Vercel env
- [ ] Confirm robots/sitemap on apex; clear accidental noindex on production host
- [ ] Sticky Call + WhatsApp (partially present in header; harden mobile sticky)
- [ ] Contact/quote form end-to-end

## Stream 1b — Wave 1 pages (after domain + Jack-confirmed copy; **no invented prices**)
New/upgrade routes in PHP `website/` pattern:
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
- Cursor GitHub SCM connect required for cloud coding agents
