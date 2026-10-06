/**
 * AOV + Barriers DEEP P0 — edge ×town renderer checks (netlify/lib/aov-barriers-deep.js).
 * Lock: icomply-ops/seo/AOV-BARRIERS-DEEP-LOCK-2026-10-05.md (manual + width/height; no wind theme).
 */
import townMatrix, { nonGmMatrixRedirect } from "../../netlify/edge-functions/town-matrix.js";
import { p0KeepsTown, renderNationwideP0Town } from "../../netlify/lib/nationwide-p0.js";
import { aovBarriersDeepGmOnly, aovBarriersDeepJobRedirect, aovBarriersDeepJobTarget, aovBarriersDeepSlugs } from "../../netlify/lib/aov-barriers-deep.js";
import jobRedirectDoc from "../data/aov-barriers-deep-job-redirects.json" with { type: "json" };

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
const windRe = /\b(wind|winds|windy|wind-?load(?:ing|s)?|beaufort|storms?|stormy|coastal|coast|hurricanes?|gales?|gusts?|gusting|exposed sites?)\b/i;
const timingRe = /(within \d+ ?(minutes|mins|hours|hrs)|\d+[- ]hour response|same[- ]day|same[- ]week|next[- ]day|24\/7|24-hour|round[- ]the[- ]clock|fast response|rapid response|guaranteed (attendance|response|arrival))/i;
const decode = (s) => s.replace(/&#039;/g, "'").replace(/&quot;/g, '"').replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&amp;/g, "&");
const bytes = (s) => new TextEncoder().encode(s).length;

const slugs = aovBarriersDeepSlugs();
ok(slugs.length === 179, `179 DEEP hubs (got ${slugs.length})`);
const gm = slugs.filter(aovBarriersDeepGmOnly).sort();
ok(JSON.stringify(gm) === JSON.stringify(["came-barrier-installation", "colt-aov-installation", "se-controls-aov-installation"]), `gm60 heads ${gm.join(",")}`);

ok(nonGmMatrixRedirect("/pages/keywords/manual-height-barrier/cardiff") === null, "Cardiff kept");
ok(nonGmMatrixRedirect("/pages/keywords/manual-height-barrier/belfast") === "/pages/keywords/manual-height-barrier", "Belfast → hub");
ok(nonGmMatrixRedirect("/pages/keywords/aov-control-panel-installation/chorlton") === null, "Chorlton kept");
ok(nonGmMatrixRedirect("/pages/keywords/colt-aov-installation/cardiff") === "/pages/keywords/colt-aov-installation", "mfr head × Cardiff → hub");
ok(!p0KeepsTown("colt-aov-installation", "leeds") && p0KeepsTown("colt-aov-installation", "bolton"), "mfr head GM only");
ok(renderNationwideP0Town("colt-aov-installation", "cardiff") === null, "mfr head × Cardiff not rendered");

let minWords = Infinity;
for (const slug of slugs) {
  const town = aovBarriersDeepGmOnly(slug) ? "stockport" : "glasgow";
  const page = renderNationwideP0Town(slug, town);
  const label = `${slug}/${town}`;
  ok(page && page.robots === "index, follow", `${label} renders indexable`);
  if (!page) continue;
  ok(page.html.includes(`rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/${slug}/${town}"`), `${label} canonical`);
  for (const prop of ["og:title", "og:description", "og:url"]) ok(page.html.includes(`property="${prop}"`), `${label} ${prop}`);
  ok(page.html.includes('property="og:image" content="https://icomplypropertyservices.co.uk/assets/images/'), `${label} og:image absolute`);
  const d = decode(page.description);
  ok(bytes(d) >= 140 && bytes(d) <= 160, `${label} meta bytes ${bytes(d)}`);
  ok(page.title.includes("iComply"), `${label} title brand`);
  const article = page.html.match(/<article id="local-copy">([\s\S]*?)<\/article>/)?.[1] || "";
  const words = (article.replace(/<[^>]+>/g, " ").match(/[A-Za-z0-9'’—-]+/g) || []).length;
  minWords = Math.min(minWords, words);
  ok(words >= 800, `${label} words ${words}`);
  ok((page.html.match(/<img /g) || []).length >= 3, `${label} 3 images`);
  ok(page.html.includes('"FAQPage"') && (page.html.match(/<h3>/g) || []).length >= 4, `${label} FAQ`);
  const text = decode(page.html.replace(/<script[\s\S]*?<\/script>/g, " ").replace(/<[^>]+>/g, " "));
  const w = text.match(windRe);
  ok(!w, `${label} no wind-theme copy${w ? ` ('${w[0]}')` : ""}`);
  ok(!timingRe.test(text), `${label} no attendance-time promise`);
  ok(!/£\s*\d/.test(text), `${label} no £`);
  ok(text.includes("17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"), `${label} NAP`);
}

// DEEP wins the canonical (SEO ruling 2026-10-06): job hubs + job×town 301 at the edge.
const jobs = Object.entries(jobRedirectDoc.jobs || {});
ok(jobs.length === 18, `18 job→DEEP overlaps (got ${jobs.length})`);
for (const [job, row] of jobs) {
  const kw = row.keyword;
  ok(aovBarriersDeepJobTarget(job) === kw && slugs.includes(kw), `${job} → DEEP ${kw}`);
  ok(aovBarriersDeepJobRedirect(`/pages/jobs/${job}`) === `/pages/keywords/${kw}`, `${job} hub → keyword hub`);
  const gmOnly = aovBarriersDeepGmOnly(kw);
  ok(aovBarriersDeepJobRedirect(`/pages/jobs/${job}/bolton`) === `/pages/keywords/${kw}/bolton`, `${job}/bolton → keyword×town`);
  ok(aovBarriersDeepJobRedirect(`/pages/jobs/${job}/glasgow`) === `/pages/keywords/${kw}${gmOnly ? "" : "/glasgow"}`, `${job}/glasgow → ${gmOnly ? "hub" : "keyword×town"}`);
  ok(aovBarriersDeepJobRedirect(`/pages/jobs/${job}/belfast`) === `/pages/keywords/${kw}`, `${job}/belfast (no town page) → keyword hub`);
}
ok(aovBarriersDeepJobRedirect("/pages/jobs/came-gard-gt4/stockport") === null, "came-gard-gt4 job×town untouched");
const fakeContext = { next: () => new Response("next", { status: 200 }) };
for (const [path, want] of [
  ["/pages/jobs/car-park-barrier/stockport", "/pages/keywords/car-park-barrier/stockport"],
  ["/pages/jobs/car-park-barrier/burnley/", "/pages/keywords/car-park-barrier/burnley"],
  ["/pages/jobs/car-park-barrier/belfast", "/pages/keywords/car-park-barrier"],
  ["/pages/jobs/aov-service/leeds", "/pages/keywords/aov-service/leeds"],
]) {
  const res = await townMatrix(new Request(`https://icomplypropertyservices.co.uk${path}`), fakeContext);
  ok(res.status === 301 && new URL(res.headers.get("location")).pathname === want, `edge ${path} 301 → ${want} (got ${res.status} ${res.headers.get("location")})`);
}

console.log(fail === 0 ? `PASS (${pass} pass, 0 fail)` : `FAIL (${pass} pass, ${fail} fail)`);
console.log(`hubs=${slugs.length} gm60=${gm.length} min_town_words=${minWords}`);
process.exit(fail === 0 ? 0 : 1);
