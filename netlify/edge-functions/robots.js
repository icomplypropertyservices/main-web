export default async () => {
  return new Response(`User-agent: *
Allow: /

Sitemap: https://icomplypropertyservices.co.uk/sitemap.xml

Disallow: /admin/
Disallow: /bin/
Disallow: /data/
Disallow: /config.php
Disallow: /config.local.php
`, {
    headers: {
      "content-type": "text/plain; charset=utf-8",
      "cache-control": "public, max-age=3600",
    },
  });
};

export const config = { path: "/robots.txt" };