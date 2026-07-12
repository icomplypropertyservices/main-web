# Deploy iComply to Vercel

This repo deploys the PHP site under `website/` using the community **vercel-php** runtime.

**Entry point must be at repo root:** `api/index.php`  
(Vercel only discovers Serverless Functions inside the top-level `api/` directory.)

## 1. Connect the repo

1. Open [vercel.com/new](https://vercel.com/new)
2. Import **icomplypropertyservices/main-web**
3. Framework Preset: **Other**
4. Root Directory: **leave empty / `.`** (must be repo root so `/api` is detected)
5. Deploy

## 2. Environment variables

In **Project → Settings → Environment Variables**, set for Production (and Preview if you want):

| Name | Example | Required |
|------|---------|----------|
| `SITE_URL` | `https://your-domain.vercel.app` or custom domain | Recommended |
| `ADMIN_USER` | your admin user | Yes (prod) |
| `ADMIN_PASS` | strong password | Yes (prod) |
| `SHOPIFY_DOMAIN` | `icomply-supplys.myshopify.com` | Optional |
| `SHOPIFY_STORE_URL` | `https://icomply-supplys.myshopify.com` | Optional |
| `SHOPIFY_STOREFRONT_TOKEN` | Storefront API token | Optional (Buy Buttons) |
| `PHONE` / `EMAIL` / `WHATSAPP` | business contact | Optional |

**Do not** put Shopify Client Secret (`shpss_…`) or Admin API tokens (`shpat_…`) in Vercel env for this site.

After adding env vars, **Redeploy**.

## 3. Custom domain

Project → Settings → Domains → add `icomplypropertyservices.co.uk` (or your domain) and point DNS as Vercel instructs.

Set `SITE_URL` to the final `https://…` domain so canonicals / OG URLs are correct.

## 4. CLI deploy (optional)

```bash
npm i -g vercel
vercel login
vercel link
vercel env pull
vercel --prod
```

## 5. Notes

- **Static assets** (`/assets/*`, sitemap, robots) are served as static files.
- **All PHP pages** route through `website/api/index.php`.
- Contact form lead storage is best-effort on serverless (filesystem is ephemeral). Prefer email/CRM webhook for production leads.
- Local dev is unchanged: `php -S localhost:8000 -t website` or `website/start-server.ps1`.
