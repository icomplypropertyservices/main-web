import { legacyProductTarget } from "../lib/legacy-products.js";

// Real files and existing netlify.toml rules (package 301s, /products hub) win:
// the request runs through first and only a 404 becomes a single 301.
export default async (request, context) => {
  const url = new URL(request.url);
  const target = legacyProductTarget(url.pathname);
  if (!target) return context.next();
  const res = await context.next();
  if (res.status !== 404) return res;
  return new Response(null, {
    status: 301,
    headers: { location: new URL(target, url.origin).toString(), "cache-control": "public, max-age=86400" },
  });
};

export const config = {
  path: ["/products/*", "/shop/products/*"],
  excludedPath: ["/products/sitemap.xml"],
};
