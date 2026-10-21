// GSC 21 Oct 2026 (§1): old apex /products/* and /shop/products/* URLs 404.
// Each one gets ONE 301 to its live equivalent:
//   /products/{handle}, /products/product/{handle}, /shop/products/{handle}
//       -> shop.icomplypropertyservices.co.uk/products/{handle} when the handle is live,
//          else the brand collection on the shop, else the shop's all-products collection.
//   /products/keywords/{k} -> /pages/keywords/{k} (buy-/cheap- prefixes dropped), else /pages/keywords.
//   /products/brand/{b}    -> /pages/manufacturers/{b}, else the shop brand collection, else /pages/manufacturers.
// Data: website/data/legacy-product-map.json (scripts/gsc-2026-10-21/build_legacy_product_map.py).
import map from "../../website/data/legacy-product-map.json" with { type: "json" };

const PRODUCTS = new Set(map.products);
const COLLECTIONS = new Set(map.collections);
const KEYWORDS = new Set(map.keywords);
const MANUFACTURERS = new Set(map.manufacturers);
const SHOP = map.shop;
const FALLBACK = `${SHOP}/collections/all`;

function slugOk(s) {
  return /^[a-z0-9][a-z0-9-]*$/.test(s);
}

function brandCollection(handle) {
  const parts = handle.split("-");
  for (let n = Math.min(3, parts.length); n >= 1; n--) {
    const head = parts.slice(0, n).join("-");
    const c = map.brandAlias[head] || head;
    if (COLLECTIONS.has(c)) return c;
  }
  return "";
}

function productTarget(handle) {
  if (PRODUCTS.has(handle)) return `${SHOP}/products/${handle}`;
  const c = brandCollection(handle);
  return c ? `${SHOP}/collections/${c}` : FALLBACK;
}

function keywordTarget(k) {
  const tries = [k, k.replace(/^(buy|cheap|best|trade)-/, ""), k.replace(/^(buy|cheap)-/, "").replace(/^(cheap|buy)-/, "")];
  for (const t of tries) if (KEYWORDS.has(t)) return `/pages/keywords/${t}`;
  return "/pages/keywords";
}

function brandTarget(b) {
  const m = map.manufacturerAlias[b] || b;
  if (MANUFACTURERS.has(m)) return `/pages/manufacturers/${m}`;
  const c = map.brandAlias[b] || b;
  if (COLLECTIONS.has(c)) return `${SHOP}/collections/${c}`;
  return "/pages/manufacturers";
}

/** Target for a legacy products path, or null when the path is not a legacy product URL. */
export function legacyProductTarget(pathname) {
  const path = String(pathname || "").toLowerCase().replace(/\/+$/, "");
  let m = path.match(/^\/products\/keywords\/([^/]+)$/);
  if (m) return slugOk(m[1]) ? keywordTarget(m[1]) : "/pages/keywords";
  m = path.match(/^\/products\/brand\/([^/]+)$/);
  if (m) return slugOk(m[1]) ? brandTarget(m[1]) : "/pages/manufacturers";
  m = path.match(/^\/(?:shop\/products|products\/product|products)\/([^/]+)$/);
  if (m) {
    if (m[1] === "sitemap.xml" || m[1].endsWith(".php")) return null;
    return slugOk(m[1]) ? productTarget(m[1]) : FALLBACK;
  }
  return null;
}
