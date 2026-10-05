/**
 * Per-page quality score. 100 means every check passed.
 * title 15, meta 15, one H1 10, body depth 15, image 15, internal links 10, schema 10, local intent 10.
 */

export function scoreHtml(html, options = {}) {
  const text = String(html || "");
  const localNeedles = (options.local && options.local.length
    ? options.local
    : ["stockport", "north west"]
  ).map((item) => String(item).toLowerCase());
  const title = (text.match(/<title>([^<]*)<\/title>/i) || [, ""])[1].trim();
  const meta = (text.match(/<meta\s+name="description"\s+content="([^"]*)"/i) || [, ""])[1].trim();
  const h1s = text.match(/<h1[\s>]/gi) || [];
  const words = text
    .replace(/<script[\s\S]*?<\/script>/gi, " ")
    .replace(/<style[\s\S]*?<\/style>/gi, " ")
    .replace(/<[^>]+>/g, " ")
    .trim()
    .split(/\s+/)
    .filter(Boolean).length;
  const image = /<img\b[^>]*\balt="[^"]+"[^>]*\bsrc="[^"]+"|<img\b[^>]*\bsrc="[^"]+"[^>]*\balt="[^"]+"/i.test(text);
  const links = (text.match(/href="(\/|https:\/\/icomplypropertyservices\.co\.uk\/)/gi) || []).length;
  const schema = /<script\s+type="application\/ld\+json"/i.test(text) && /"@type"/.test(text);
  const local = localNeedles.some((needle) => needle !== "" && text.toLowerCase().includes(needle));
  const checks = {
    title: title.length >= 15 && title.length <= 90,
    meta: meta.length >= 40 && meta.length <= 180,
    h1: h1s.length === 1,
    body: words >= 250,
    image,
    links: links >= 3,
    schema,
    local,
  };
  const weights = { title: 15, meta: 15, h1: 10, body: 15, image: 15, links: 10, schema: 10, local: 10 };
  let score = 0;
  for (const [key, weight] of Object.entries(weights)) {
    if (checks[key]) score += weight;
  }
  return { score, checks, words, title, metaLength: meta.length, links };
}

function average(rows) {
  if (!rows.length) return 0;
  return Math.round(rows.reduce((sum, row) => sum + row.score, 0) / rows.length);
}

const isDirect = process.argv[1] && process.argv[1].endsWith("score-pages.mjs") && process.argv.length > 2;
if (isDirect) {
  const fs = await import("node:fs");
  const rows = [];
  for (const file of process.argv.slice(2)) {
    const html = fs.readFileSync(file, "utf8");
    const place = file.split("/").pop().replace(/\.html$/, "").replace(/-/g, " ");
    const result = scoreHtml(html, { local: [place, "stockport", "north west", "ellesmere", "manchester", "came", "eicr", "electrical"] });
    rows.push(result);
    console.log(`${result.score}\t${file}\t${JSON.stringify(result.checks)}\twords=${result.words}\tmeta=${result.metaLength}`);
  }
  console.log(`average ${average(rows)}`);
}
