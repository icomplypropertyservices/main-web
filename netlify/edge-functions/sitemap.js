// Static sitemap.xml is the index. No path config, so this cannot shadow it.
export default async () => new Response('', { status: 204 });