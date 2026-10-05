/**
 * Edge renderer: P0 fire-alarm-installer towns stay nationwide and indexable.
 */
import { nonGmMatrixRedirect, renderTownPage } from "../../netlify/edge-functions/town-matrix.js";
import family from "../data/fire-alarm-installer-family.json" with { type: "json" };
import mainland from "../data/uk-mainland-towns-10k.json" with { type: "json" };

let fail = 0;
const ok = (cond, msg) => {
  if (cond) {
    console.log(`[PASS] ${msg}`);
    return;
  }
  fail += 1;
  console.log(`[FAIL] ${msg}`);
};

const towns = (mainland.towns || []).filter((town) => Number(town.population) > 10000);
ok(family.p0.length === 16, "16 P0 slugs");
ok(family.hubs.length === 44, "44 hubs");
ok(towns.length >= 900, `mainland towns ${towns.length}`);

ok(nonGmMatrixRedirect("/pages/keywords/fire-alarm-installer/leeds") === null, "Leeds stays");
ok(nonGmMatrixRedirect("/pages/keywords/fire-alarm-installer/belfast") === "/pages/keywords/fire-alarm-installer", "Belfast redirects");
ok(nonGmMatrixRedirect("/pages/keywords/rewire/liverpool") === null, "rewire Liverpool stays inside the dual ring");
ok(nonGmMatrixRedirect("/pages/keywords/fire-alarm-company/glasgow") === "/pages/keywords/fire-alarm-company", "non-P0 Glasgow redirects");
ok(nonGmMatrixRedirect("/pages/electrical/london") === "/pages/services/electrical", "electrical London redirects");

const catalogue = {
  keywords: {
    "fire-alarm-installer": {
      name: "Fire Alarm Installer",
      service: "fire-alarms",
      intro: "A fire alarm installer designs, fits and commissions to BS 5839.",
      focus: ["Design agreed first", "Commissioning included", "Price on application"],
      gas: false,
    },
    eicr: {
      name: "EICR",
      service: "electrical",
      intro: "An EICR is the inspection record for a fixed installation.",
      focus: ["Visual inspection"],
      gas: false,
    },
    "fire-alarm-company": {
      name: "Fire Alarm Company",
      service: "fire-alarms",
      intro: "A fire alarm company designs, installs and commissions to BS 5839.",
      focus: ["Design agreed first"],
      gas: false,
    },
  },
  places: {
    stockport: {
      name: "Stockport",
      region: "North West",
      country: "England",
      population: 294000,
      housing: "32% semi-detached houses",
      industry: "18% retail",
      neighbours: ["Manchester"],
    },
  },
  services: {
    labels: {
      "fire-alarms": "Fire Alarms",
      electrical: "Electrical",
      "emergency-lighting": "Emergency lighting",
      "fire-doors": "Fire doors",
      "fire-risk-assessments": "Fire risk assessments",
    },
    keywords: { "fire-alarms": ["fire-alarm-installer"], electrical: ["eicr"] },
    excluded: ["barriers", "aov-air-handling"],
    family: ["fire-alarms"],
  },
  jobs: {},
  manufacturers: {},
};

const london = renderTownPage({
  kind: "keyword",
  keyword: "fire-alarm-installer",
  town: "london",
  catalogue,
});
ok(london && london.robots === "index, follow", "London installer page is indexable");
ok(london && london.html.includes("London"), "London is named");
ok(london && london.html.includes("17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"), "NAP on the town page");
ok(london && london.html.includes("/pages/services/fire-alarms"), "service hub linked");
ok(london && !london.html.includes("approved subcontractor"), "no approved-subcontractor wording");
ok(london && !/£\s*\d/.test(london.html), "no price");
ok(london && /price on application|\bPOA\b/i.test(london.html), "POA");
function ogOk(page, label) {
  if (!page) {
    ok(false, `${label} rendered`);
    return;
  }
  const tags = {
    "og:title": page.title,
    "og:description": page.description,
    "og:url": page.canonical,
    "og:type": "website",
  };
  for (const [prop, value] of Object.entries(tags)) {
    ok(page.html.includes(`property="${prop}" content="${value}"`), `${label} ${prop}`);
  }
  const image = page.html.match(/property="og:image" content="([^"]+)"/);
  ok(Boolean(image && image[1].startsWith("https://")), `${label} og:image absolute https ${image ? image[1] : "missing"}`);
  ok(page.html.includes("<title>") && page.html.includes('name="description"') && page.html.includes('rel="canonical"'), `${label} keeps title, description and canonical`);
}
ogOk(london, "London installer");
for (const town of ["glasgow", "cardiff", "birmingham"]) {
  ogOk(renderTownPage({ kind: "keyword", keyword: "fire-alarm-installer", town, catalogue }), `${town} installer`);
}
const hrefs = new Set([...(london.html.match(/href="([^"]+)"/g) || [])]);
ok(hrefs.size >= 80, `unique hrefs ${hrefs.size}`);

const company = renderTownPage({
  kind: "keyword",
  keyword: "fire-alarm-company",
  town: "stockport",
  catalogue,
});
ok(company && company.robots === "index, follow", "non-P0 Stockport page stays on the GM matrix");
ok(company && !company.html.includes("approved subcontractor"), "non-P0 family page has no approved-subcontractor wording");

const stockport = renderTownPage({
  kind: "keyword",
  keyword: "eicr",
  town: "stockport",
  catalogue,
});
ok(stockport && stockport.robots === "index, follow", "other matrix pages stay on the GM core");
ok(stockport && !stockport.html.includes("17 Woodlands Park Road"), "other matrix pages do not use the fire installer NAP");
ok(stockport && !stockport.html.includes("approved subcontractors"), "GM copy no longer uses the subcontractor sentence");

const aberdeen = renderTownPage({
  kind: "keyword",
  keyword: "eicr",
  town: "aberdeen",
  catalogue,
});
ok(aberdeen && aberdeen.robots === "noindex, follow", "non-family Aberdeen stays noindex");

console.log(fail === 0 ? `PASS towns=${towns.length}` : `FAIL ${fail}`);
process.exit(fail === 0 ? 0 : 1);
