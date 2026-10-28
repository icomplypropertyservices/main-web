// Static sitemap.xml is served as a file: a sharded sitemap index in dist (sitemap0..N.xml,
// <=45,000 URLs each). No path config on purpose, so this module never shadows it.
export default async () => new Response('', { status: 204 });