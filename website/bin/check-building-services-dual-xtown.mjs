/**
 * Edge renderer for building-services P0 × dual 269.
 */
import { readFileSync } from "node:fs";
import { nonGmMatrixRedirect, renderTownPage } from "../../netlify/edge-functions/town-matrix.js";

const pack = JSON.parse(readFileSync(new URL("../data/building-services-dual-p0.json", import.meta.url), "utf8"));
let fail = 0;
let pass = 0;
function ok(cond, msg) {
  if (cond) {
    pass += 1;
    console.log(`[PASS] ${msg}`);
    return;
  }
  fail += 1;
  console.log(`[FAIL] ${msg}`);
}

ok(pack.slugs.length === 100, `slugs=${pack.slugs.length}`);
ok(pack.towns.length === 269, `towns=${pack.towns.length}`);
ok(pack.towns.filter((town) => town.gm).length === 60, "gm=60");

const catalogue = { keywords: {}, places: {}, services: { labels: {} }, jobs: {}, manufacturers: {}, nationwide: {} };
const samples = [
  renderTownPage({ kind: "keyword", keyword: "plasterers", town: "york", catalogue }),
  renderTownPage({ kind: "job", job: "cp12", town: "burnley", catalogue }),
  renderTownPage({ kind: "keyword", keyword: "garden-wall-building", town: "stockport", catalogue }),
  renderTownPage({ kind: "job", job: "fire-door-installer", town: "manchester", catalogue }),
  renderTownPage({ kind: "keyword", keyword: "boiler-service", town: "york", catalogue }),
  renderTownPage({ kind: "job", job: "dryliners", town: "burnley", catalogue }),
];

for (const page of samples) {
  ok(page && page.status === 200 && page.robots === "index, follow", `200 index ${page && page.path}`);
  if (!page) continue;
  const html = page.html;
  const article = html.match(/<article\b[^>]*>([\s\S]*)<\/article>/i);
  const prose = (article ? article[1] : html).replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim();
  const words = prose.split(/\s+/).filter(Boolean).length;
  ok(words >= 800, `${page.path} words=${words}`);
  ok((html.match(/<img\b/gi) || []).length >= 3, `${page.path} images`);
  ok(html.includes('data-seo-faq="1"') && html.includes("<details"), `${page.path} FAQ`);
  ok((html.match(/<h1>/g) || []).length === 1, `${page.path} one h1`);
  ok(html.includes(`rel="canonical" href="${page.canonical}"`), `${page.path} canonical`);
  ok(html.includes('property="og:title"') && html.includes('property="og:description"') && html.includes('property="og:url"') && html.includes('property="og:image"'), `${page.path} OG`);
  ok(/price on application|\bPOA\b/i.test(html), `${page.path} POA`);
  ok(html.includes("17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"), `${page.path} NAP`);
  ok(!html.includes("£"), `${page.path} no price`);
  ok(!html.includes("approved subcontractors"), `${page.path} no approved subcontractors`);
  ok(!/\bb\d{5}\b/.test(html), `${page.path} no bNNNNN`);
  ok(page.title.length >= 30 && page.title.length <= 65, `${page.path} title ${page.title.length}`);
  ok(page.description.length >= 140 && page.description.length <= 160, `${page.path} meta ${page.description.length}`);
  const gas = page.path.includes("/cp12/") || page.path.includes("/boiler-service/");
  if (gas) {
    ok(html.includes("The visit is carried out by Gas Safe registered engineers."), `${page.path} gas duty`);
    ok(html.includes("iComply does not claim a Gas Safe registration."), `${page.path} gas denial`);
    ok(!/iComply is Gas Safe registered\./.test(html), `${page.path} no positive claim`);
  } else {
    ok(!html.includes("Gas Safe registered"), `${page.path} no Gas Safe line`);
  }
}

ok(nonGmMatrixRedirect("/pages/jobs/cp12/york") === null, "cp12/york stays");
ok(nonGmMatrixRedirect("/pages/keywords/plasterers/burnley") === null, "plasterers/burnley stays");
ok(nonGmMatrixRedirect("/pages/keywords/plasterers/aberdeen") === "/pages/keywords/plasterers", "aberdeen redirects");
ok(nonGmMatrixRedirect("/pages/keywords/rewire/liverpool") === "/pages/keywords/rewire", "rewire Liverpool redirects");
ok(nonGmMatrixRedirect("/pages/jobs/eicr/burnley") === "/pages/jobs/eicr", "eicr Burnley redirects");
ok(nonGmMatrixRedirect("/pages/areas/burnley") === "/pages/areas", "Burnley area hub redirects");

const eicr = renderTownPage({ kind: "keyword", keyword: "eicr", town: "stockport", catalogue });
ok(eicr && eicr.path === "/pages/keywords/eicr/stockport" && !eicr.html.includes("dual-ring row"), "eicr stays on the GM renderer");

console.log(fail === 0 ? `OK building dual edge ${pass}` : `FAIL building dual edge pass=${pass} fail=${fail}`);
process.exit(fail === 0 ? 0 : 1);
