/**
 * Margin DEEP packs: edge ×town renderer checks (netlify/lib/margin-deep.js), including
 * byte parity with the PHP twin (website/includes/margin-deep.php) on sample pages.
 * Locks: icomply-ops/seo/*-DEEP-LOCK-2026-10-05.md. Usage: node bin/check-margin-deep.mjs [--pack=fencing]
 */
import { execFileSync } from "node:child_process";
import { readFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";
import { nonGmMatrixRedirect } from "../../netlify/edge-functions/town-matrix.js";
import { MARGIN_DEEP_PACKS } from "../../netlify/lib/margin-deep-packs.js";
import { marginDeepGmOnly, marginDeepKeepsTown, marginDeepRedirect, marginDeepSlugs, renderMarginDeepTown } from "../../netlify/lib/margin-deep.js";

const root = dirname(dirname(fileURLToPath(import.meta.url)));
const only = (process.argv.find((a) => a.startsWith("--pack=")) || "").slice(7);
let fail = 0;
let pass = 0;
const ok = (cond, msg) => {
  if (cond) {
    pass += 1;
    return;
  }
  fail += 1;
  console.log(`[FAIL] ${msg}`);
};
const timingRe = /(within \d+ ?(minutes|mins|hours|hrs|days)|\d+[- ]hour (response|call-?out)|same[- ]day|same[- ]week|next[- ]day|24\/7|24-hour|round[- ]the[- ]clock|fast response|rapid response|guaranteed (attendance|response|arrival)|we will be there|on site in \d|response time of)/i;
const decode = (s) => s.replace(/&#039;/g, "'").replace(/&quot;/g, '"').replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&amp;/g, "&");
const bytes = (s) => new TextEncoder().encode(s).length;
const NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE";

const allow = JSON.parse(readFileSync(join(root, "data/dual-ring-allowlist.json"), "utf8"));
const towns = allow.towns || allow;
ok(towns.length === 269, `269 dual-ring towns (got ${towns.length})`);
const gmTown = towns.find((t) => t.bucket === "gm_core").slug;
const ringTown = towns.find((t) => t.bucket !== "gm_core").slug;

const docs = MARGIN_DEEP_PACKS.filter((d) => !only || d.pack === only);
ok(docs.length > 0, `packs listed in margin-deep-packs.js${only ? ` (${only})` : ""}`);
const summary = [];
for (const doc of docs) {
  const id = doc.pack;
  const slugs = marginDeepSlugs(id);
  ok(slugs.length === doc.p0_count, `${id}: ${doc.p0_count} hubs (got ${slugs.length})`);
  for (const [from, to] of Object.entries(doc.redirects || {})) ok(marginDeepRedirect(from) === to, `${id}: 301 ${from} → ${to}`);
  let minWords = Infinity;
  const parity = [];
  slugs.forEach((slug, i) => {
    const hub = `/pages/keywords/${slug}`;
    const gm = marginDeepGmOnly(slug);
    const town = gm ? gmTown : ringTown;
    ok(nonGmMatrixRedirect(`${hub}/${gmTown}`) === null, `${id}: ${slug} × ${gmTown} kept`);
    ok(nonGmMatrixRedirect(`${hub}/${ringTown}`) === (gm ? hub : null), `${id}: ${slug} × ${ringTown} ${gm ? "→ hub (GM 60 only)" : "kept"}`);
    ok(nonGmMatrixRedirect(`${hub}/london`) === hub, `${id}: ${slug} × london → hub`);
    ok(!marginDeepKeepsTown(slug, "london") && renderMarginDeepTown(slug, "london") === null, `${id}: ${slug} × london not rendered`);
    const page = renderMarginDeepTown(slug, town);
    const label = `${id}: ${slug}/${town}`;
    ok(page && page.robots === "index, follow", `${label} renders indexable`);
    if (!page) return;
    ok(page.html.includes(`rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/${slug}/${town}"`), `${label} canonical`);
    for (const prop of ["og:title", "og:description", "og:url"]) ok(page.html.includes(`property="${prop}"`), `${label} ${prop}`);
    ok(page.html.includes('property="og:image" content="https://icomplypropertyservices.co.uk/assets/images/'), `${label} og:image absolute`);
    const d = decode(page.description);
    ok(bytes(d) >= 140 && bytes(d) <= 160, `${label} meta bytes ${bytes(d)}`);
    ok(page.title.includes("iComply"), `${label} title brand`);
    const article = page.html.match(/<article id="local-copy">([\s\S]*?)<\/article>/)?.[1] || "";
    const words = (article.replace(/<[^>]+>/g, " ").match(/[A-Za-z0-9'’-]+/g) || []).length;
    minWords = Math.min(minWords, words);
    ok(words >= 800, `${label} words ${words}`);
    ok((page.html.match(/<img /g) || []).length >= 3, `${label} 3 images`);
    ok(page.html.includes('"FAQPage"') && (page.html.match(/<h3>/g) || []).length >= 4, `${label} FAQ`);
    const text = decode(page.html.replace(/<script[\s\S]*?<\/script>/g, " ").replace(/<[^>]+>/g, " "));
    ok(!timingRe.test(text), `${label} no attendance-time promise`);
    ok(!/£\s*\d/.test(text), `${label} no £`);
    ok(text.includes(NAP), `${label} NAP`);
    ok(page.html.includes("https://wa.me/447517806082") && page.html.includes("mailto:info@icomplypropertyservices.co.uk"), `${label} WhatsApp + email`);
    if (i % 27 === 0) parity.push([slug, town, page.html]);
  });
  for (const [slug, town, html] of parity) {
    const php = execFileSync("php", ["-r", `putenv('SITE_URL=https://icomplypropertyservices.co.uk'); require '${root}/config.php'; $p = icomplyMarginDeepTownPage($argv[1], $argv[2]); echo $p['html'] ?? '';`, slug, town], { cwd: root, encoding: "utf8", maxBuffer: 1 << 26 });
    ok(php === html, `${id}: ${slug}/${town} edge HTML matches PHP byte for byte`);
  }
  summary.push(`${id} hubs=${slugs.length} min_town_words=${minWords} parity_samples=${parity.length}`);
}

console.log(fail === 0 ? `PASS (${pass} pass, 0 fail)` : `FAIL (${pass} pass, ${fail} fail)`);
for (const line of summary) console.log(line);
process.exit(fail === 0 ? 0 : 1);
