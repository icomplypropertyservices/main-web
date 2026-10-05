/**
 * Edge renderer for nationwide P0 keyword × TOP 5000 towns.
 */
import { nonGmMatrixRedirect, renderTownPage } from "../../netlify/edge-functions/town-matrix.js";
import { renderNationwideP0Town } from "../../netlify/lib/nationwide-p0.js";

let fail = 0;
const ok = (cond, msg) => {
  if (cond) {
    console.log(`[PASS] ${msg}`);
    return;
  }
  fail += 1;
  console.log(`[FAIL] ${msg}`);
};

ok(nonGmMatrixRedirect("/pages/keywords/aov-installer/leeds") === null, "Leeds stays");
ok(nonGmMatrixRedirect("/pages/keywords/barrier-installer/cardiff") === null, "Cardiff stays");
ok(nonGmMatrixRedirect("/pages/keywords/aov-installer/belfast") === "/pages/keywords/aov-installer", "Belfast redirects");
ok(nonGmMatrixRedirect("/pages/keywords/aov-installer/chorlton") === null, "Chorlton stays");
ok(nonGmMatrixRedirect("/pages/keywords/rewire/birmingham") === "/pages/keywords/rewire", "rewire Birmingham redirects");
ok(nonGmMatrixRedirect("/pages/electrical/london") === "/pages/services/electrical", "electrical London redirects");
ok(nonGmMatrixRedirect("/pages/aov/leeds") === null, "AOV Leeds service page stays");
ok(nonGmMatrixRedirect("/pages/barriers/liverpool") === null, "barrier Liverpool service page stays");

const leeds = renderNationwideP0Town("fire-alarm-installer", "leeds");
ok(leeds && leeds.robots === "index, follow", "Leeds is indexable");
ok(leeds && leeds.html.includes("<h1>Fire Alarm Installer in Leeds</h1>"), "Leeds H1");
ok(leeds && leeds.html.includes('rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/fire-alarm-installer/leeds"'), "canonical");
ok(leeds && leeds.html.includes('property="og:title"'), "og:title");
ok(leeds && leeds.html.includes('property="og:description"'), "og:description");
ok(leeds && leeds.html.includes('property="og:url"'), "og:url");
ok(leeds && leeds.html.includes('property="og:image" content="https://icomplypropertyservices.co.uk/'), "og:image absolute");
const desc = leeds?.html.match(/<meta name="description" content="([^"]*)"/)?.[1] || "";
ok(desc.length >= 140 && desc.length <= 160, `meta length ${desc.length}`);
ok(leeds?.title.includes("Leeds") && leeds.title.includes("iComply"), "title has town and brand");
const article = leeds?.html.match(/<article id="local-copy">([\s\S]*?)<\/article>/)?.[1] || "";
const words = article.replace(/<[^>]+>/g, " ").split(/\s+/).filter(Boolean).length;
ok(words >= 800, `body words ${words}`);
ok((leeds?.html.match(/<img /g) || []).length >= 3, "3 images");
ok(leeds?.html.includes("<h2>Fire Alarm Installer FAQ</h2>"), "FAQ");
ok(leeds && !leeds.html.includes("approved subcontractor"), "no approved-subcontractor wording");
ok(leeds && !/£\s*\d/.test(leeds.html), "no price");
ok(leeds?.html.includes("17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"), "NAP");
const hrefs = new Set([...(leeds?.html.match(/href="([^"]+)"/g) || [])]);
ok(hrefs.size >= 80, `links ${hrefs.size}`);

const chorlton = renderNationwideP0Town("barrier-installer", "chorlton");
ok(chorlton && chorlton.html.includes("Chorlton"), "Chorlton barrier page still renders");

const catalogue = {
  keywords: {
    eicr: {
      name: "EICR",
      service: "electrical",
      intro: "An EICR is the inspection record for a fixed installation.",
      focus: ["Visual inspection"],
      gas: false,
    },
  },
  places: {
    stockport: { name: "Stockport", region: "North West", country: "England", population: 294000, housing: "", industry: "", neighbours: ["Manchester"] },
  },
  services: { labels: { electrical: "Electrical" }, keywords: {}, excluded: [], family: [] },
  jobs: {},
  manufacturers: {},
};
const eicr = renderTownPage({ kind: "keyword", keyword: "eicr", town: "stockport", catalogue });
ok(eicr && eicr.html.includes("EICR") && eicr.html.includes("Stockport"), "other matrix pages still render via the shared template");

console.log(fail === 0 ? "PASS" : `FAIL ${fail}`);
process.exit(fail === 0 ? 0 : 1);
