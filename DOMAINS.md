# iComply Property domains

## Site model (no group hub)

| Surface | URL | What it is |
|---------|-----|------------|
| **Services (original site)** | `https://icomplypropertyservices.co.uk/` | Property compliance **services** SEO site (this repo / main-web PHP) |
| **Products** | `https://icomplypropertyservices.co.uk/products` | iComply **products** SEO catalogue (products project rewrite) |
| **Shop** | `https://icomplypropertyservices.co.uk/shop` | Product landings + Buy Buttons (shopweb rewrite) |

The **group hub** (`/group`, group Vercel project) is **retired**. Old `/group` URLs 301 to the services homepage.

## Apex ownership

- **main-web** (this repo) owns `icomplypropertyservices.co.uk`
- Homepage and all service/area/keyword SEO pages are PHP via `api/index.php`
- Only `/products/*` and `/shop/*` are rewritten to other Vercel projects

## Legacy hosts

| Host | Behaviour |
|------|-----------|
| `www.` | 301 → apex |
| `group.` | 301 → apex (group deleted) |
| `shop.` / `shopweb.` | 301 → `/shop` on apex |
| `products.` | 301 → `/products` on apex |

## Vercel projects still used

| Project | Role |
|---------|------|
| **main-web** | Apex + services |
| **products-…** | Products catalogue |
| **icomply-shopweb** | Shop landings |
| ~~group~~ | **Deleted / unused** |
