// Static sitemap.xml is the published urlset. No path config on purpose.
export default async () => new Response('', { status: 204 });