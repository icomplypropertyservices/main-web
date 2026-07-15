# Google monitoring & SEO automation (Icomply)

## Live site checks

After every deploy, verify:

```
https://www.icomplypropertyservices.co.uk/
https://www.icomplypropertyservices.co.uk/pages/services
https://www.icomplypropertyservices.co.uk/pages/services/fire-risk-assessments
https://www.icomplypropertyservices.co.uk/pages/kitchens/manchester
https://www.icomplypropertyservices.co.uk/sitemap.xml
https://www.icomplypropertyservices.co.uk/robots.txt
```

Automated health job:

```bash
php website/bin/cron-seo.php
# or (after setting SEO_CRON_KEY on Vercel):
# https://www.icomplypropertyservices.co.uk/bin/cron-seo?key=YOUR_SECRET
```

Writes `website/data/seo-health.json` and pings Google/Bing with the sitemap.

## Google Search Console (required for “Google monitoring”)

1. Open [Google Search Console](https://search.google.com/search-console)
2. Add property: **URL prefix** `https://www.icomplypropertyservices.co.uk`
3. Verify using **one** of:
   - **HTML file** already in repo:  
     `google-site-verification=2lrgYlgy9RIa8rKrDRp4fpYk6EZXyQzxrP1F7Nb-b2s.txt`
   - **HTML tag**: set Vercel env `GOOGLE_SITE_VERIFICATION` to the `content` value  
     (emitted as `<meta name="google-site-verification" …>` in `header.php`)
4. **Sitemaps → Add**: `https://www.icomplypropertyservices.co.uk/sitemap.xml`
5. Prefer **www** as the canonical host (apex should 308 → www)

### What GSC gives you

- Coverage / indexing errors  
- Core Web Vitals  
- Query performance (impressions, CTR, position)  
- Manual actions / security issues  

There is **no free Google API that auto-ranks you #1**. GSC + sitemaps + content quality + technical health are the real levers. This repo automates the technical side.

## Vercel environment variables

| Name | Purpose |
|------|---------|
| `SITE_URL` | `https://www.icomplypropertyservices.co.uk` |
| `GOOGLE_SITE_VERIFICATION` | GSC meta tag content |
| `GA_MEASUREMENT_ID` | Google Analytics 4 (`G-…`) |
| `SEO_CRON_KEY` | Secret for web cron |
| `INDEXNOW_KEY` | Optional Bing IndexNow key |

After setting vars: **Redeploy**.

## IndexNow (Bing / others)

1. Generate a random key (e.g. 32 hex chars)  
2. Set `INDEXNOW_KEY`  
3. Cron writes `/{key}.txt` and POSTs priority URLs to IndexNow  

## What “auto amend” does

| Automated | Not automated (needs humans/content) |
|-----------|--------------------------------------|
| Fix localhost leaks in HTML on Vercel | Writing unique copy for every query |
| Robots + sitemap host rewrites | Link building / reviews / GBP posts |
| Ping Google/Bing with sitemap | Buying rankings or guaranteed #1 |
| Health report of key URLs | Manual GSC “Request indexing” at scale |
| Category nav + 221k URL sitemap | Changing Google’s algorithm |

## Redeploy note

If production still shows old titles or 404s on new services, GitHub → Vercel deploy did not pick up latest `main`. Trigger a redeploy in the Vercel dashboard after this push.
